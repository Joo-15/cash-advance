<script setup>
import { computed, ref, watch } from "vue";
import {
    NButton,
    NEmpty,
    NIcon,
    NModal,
    NText,
    NUpload,
    NUploadDragger,
    NTag,
    NCard,
    NGrid,
    NGridItem,
    useMessage,
} from "naive-ui";
import {
    CloudUploadOutline,
    DocumentTextOutline,
    ImageOutline,
    EyeOutline,
    TrashOutline,
    DocumentOutline,
    CheckmarkOutline,
} from "@vicons/ionicons5";
import { formatDate, formatRupiah } from "@/utils/helpers";

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
    maxSize: {
        type: Number,
        default: 5,
    },
    maxFiles: {
        type: Number,
        default: 1,
    },
    accept: {
        type: Array,
        default: () => ["pdf", "jpg", "jpeg", "png"],
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: "Upload file",
    },
    borrowerInfo: {
        type: Object,
        default: () => ({
            name: "Fajar Nugroho",
            department: "R&D",
            disbursementDate: "18 Mar 2025",
            amount: 30000000,
            currentStage: 1,
            totalStages: 2,
        }),
    },
    loading: Boolean,
    showModal: Boolean,
    dataSelected: Array,
    closeModal: Function,
    submit: Function,
});

const selectedItem = computed(() => props.dataSelected?.[0] || {});
const displayData = computed(() => {
    const item = selectedItem.value;
    const lastApproval = item?.approvals?.slice(-1)[0] || null;
    return {
        id: item?.id,
        purpose: item?.purpose,
        name: item?.user?.name,
        department: item?.user?.department?.name,
        amount: formatRupiah(item?.amount),
        disbursedAmount: formatRupiah(item?.disbursement?.amount) || "-",
        disbursedDate: item?.disbursement?.disbursed_at
            ? item?.disbursement?.disbursed_at.split(" ")[0]
            : "-",
        approveDate: lastApproval?.approved_at
            ? lastApproval.approved_at.split(" ")[0]
            : null,
    };
});

const emit = defineEmits(["update:files", "submit", "cancel"]);

const message = useMessage();
const localFiles = ref([]);
const showPreview = ref(false);
const previewFileData = ref(null);
const submitting = ref(false);
const uploadRef = ref(null);
const lastProcessedFile = ref(null); // Untuk menyimpan file terakhir yang diproses
let debounceTimer = null;

const acceptTypes = props.accept.map((ext) => `.${ext}`).join(",");

// Watch untuk props.files
watch(
    () => props.files,
    (newFiles) => {
        console.log("Props files changed:", newFiles);
        if (newFiles && newFiles.length > 0) {
            localFiles.value = [...newFiles];
        } else {
            if (localFiles.value.length === 0) {
                localFiles.value = [];
            }
        }
    },
    { deep: true },
);

// Watch localFiles
watch(
    localFiles,
    (newFiles) => {
        console.log("Local files changed:", newFiles);
        emit("update:files", newFiles);
    },
    { deep: true },
);

// Fungsi untuk mengecek apakah file sudah ada
const isFileExists = (newFile) => {
    return localFiles.value.some(
        (existingFile) =>
            existingFile.name === newFile.name &&
            existingFile.size === newFile.size,
    );
};

// Fungsi untuk menangani upload file dengan debounce
const handleFileUpload = (fileObj) => {
    // Clear timer sebelumnya
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    // Debounce untuk mencegah double upload
    debounceTimer = setTimeout(() => {
        // Generate unique ID untuk file
        const fileKey = `${fileObj.name}_${fileObj.size}_${fileObj.lastModified || Date.now()}`;

        // Cek apakah file ini sama dengan yang terakhir diproses (dalam 500ms)
        if (lastProcessedFile.value === fileKey) {
            console.log("Duplicate file detected, skipping:", fileObj.name);
            return;
        }

        // Tandai file sedang diproses
        lastProcessedFile.value = fileKey;

        // Validasi ukuran
        const fileSize = fileObj.size;
        if (fileSize > props.maxSize * 1024 * 1024) {
            message.error(`Ukuran file melebihi ${props.maxSize}MB`);
            lastProcessedFile.value = null;
            return;
        }

        // Validasi tipe
        const extension = fileObj.name.split(".").pop()?.toLowerCase();
        if (!props.accept.includes(extension)) {
            message.error(
                `Tipe file tidak didukung. Format: ${props.accept.join(", ")}`,
            );
            lastProcessedFile.value = null;
            return;
        }

        // Validasi jumlah file
        if (!props.multiple && localFiles.value.length >= props.maxFiles) {
            message.warning(`Maksimal ${props.maxFiles} file`);
            lastProcessedFile.value = null;
            return;
        }

        // Cek duplikasi file
        const newFileCheck = {
            name: fileObj.name,
            size: fileSize,
        };

        if (isFileExists(newFileCheck)) {
            message.warning(`File "${fileObj.name}" sudah ada`);
            lastProcessedFile.value = null;
            return;
        }

        // Buat object file baru
        const newFile = {
            id: Date.now() + Math.random(),
            name: fileObj.name,
            size: fileSize,
            type: extension,
            file: fileObj,
            url: URL.createObjectURL(fileObj),
            status: "success",
            uploadTime: Date.now(),
        };

        console.log("Creating new file:", newFile);

        // Tambahkan ke localFiles
        if (props.multiple) {
            localFiles.value.push(newFile);
        } else {
            // Hapus file lama
            if (localFiles.value.length > 0 && localFiles.value[0].url) {
                URL.revokeObjectURL(localFiles.value[0].url);
            }
            localFiles.value = [newFile];
        }

        console.log("Updated localFiles:", localFiles.value);
        message.success(`File ${newFile.name} berhasil diupload`);

        // Reset lastProcessedFile setelah 1 detik
        setTimeout(() => {
            lastProcessedFile.value = null;
        }, 1000);
    }, 100); // Debounce 100ms
};

// Handler untuk @change dengan pengecekan file list
const handleChange = ({ file, fileList }) => {
    console.log("Change event received:", { file, fileList });

    // Ambil file object yang benar
    let fileObj = file.file || file;

    // Cek apakah ini file yang valid
    if (!fileObj || !fileObj.name) {
        console.error("Invalid file object:", file);
        return;
    }

    // Cek apakah file sudah ada di fileList sebelumnya
    // Naive UI sering memanggil change dengan file yang sama multiple times
    const existingInList = fileList.filter(
        (f) => f.file?.name === fileObj.name || f.name === fileObj.name,
    );

    // Jika file sudah ada di list dan ini adalah panggilan kedua, skip
    if (existingInList.length > 1) {
        console.log("File already in list, skipping duplicate");
        return;
    }

    // Proses file
    handleFileUpload(fileObj);
};

const removeFile = (index) => {
    console.log("Removing file at index:", index);
    if (localFiles.value[index]?.url) {
        URL.revokeObjectURL(localFiles.value[index].url);
    }
    localFiles.value.splice(index, 1);

    if (localFiles.value.length === 0) {
        emit("update:files", []);
    }
};

const removeAllFiles = () => {
    localFiles.value.forEach((file) => {
        if (file.url) {
            URL.revokeObjectURL(file.url);
        }
    });
    localFiles.value = [];
    emit("update:files", []);
    message.success("Semua file berhasil dihapus");
};

const previewFile = (file) => {
    previewFileData.value = {
        url: file.url,
        type: file.type === "pdf" ? "pdf" : "image",
    };
    showPreview.value = true;
};

const canPreview = (file) => {
    return file.type === "pdf" || ["jpg", "jpeg", "png"].includes(file.type);
};

const isPdf = (fileName) => {
    return fileName?.toLowerCase().endsWith(".pdf");
};

const truncateFileName = (name, maxLength = 30) => {
    if (!name) return "";
    if (name.length <= maxLength) return name;
    const ext = name.split(".").pop();
    const baseName = name.slice(0, maxLength - ext.length - 3);
    return `${baseName}...${ext}`;
};

const formatFileSize = (bytes) => {
    if (!bytes) return "";
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const handleCancel = () => {
    console.log("Cancel clicked");
    localFiles.value.forEach((file) => {
        if (file.url) {
            URL.revokeObjectURL(file.url);
        }
    });
    localFiles.value = [];
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    lastProcessedFile.value = null;
    if (props.closeModal) {
        props.closeModal();
    }
    emit("cancel");
    message.info("Data dibatalkan");
};

const handleSubmit = async () => {
    console.log("Submit clicked, localFiles:", localFiles.value);
    console.log("Local files length:", localFiles.value.length);

    if (!localFiles.value || localFiles.value.length === 0) {
        console.warn("No files found!");
        message.warning("Harap upload file terlebih dahulu");
        return;
    }

    submitting.value = true;

    try {
        const formData = new FormData();

        localFiles.value.forEach((file, index) => {
            if (file.file) {
                console.log(`Appending file ${index}:`, file.file);
                formData.append(`files[${index}]`, file.file);
            }
        });

        formData.append("borrowerInfo", JSON.stringify(props.borrowerInfo));
        formData.append("dataSelected", JSON.stringify(selectedItem.value));

        if (props.submit) {
            await props.submit(formData);
        } else {
            emit("submit", {
                files: localFiles.value,
                borrowerInfo: props.borrowerInfo,
                dataSelected: selectedItem.value,
            });
        }

        message.success("Bukti berhasil disimpan");
    } catch (error) {
        console.error("Submit error:", error);
        message.error(error.message || "Gagal menyimpan bukti");
    } finally {
        submitting.value = false;
    }
};

const reset = () => {
    localFiles.value.forEach((file) => {
        if (file.url) {
            URL.revokeObjectURL(file.url);
        }
    });
    localFiles.value = [];
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    lastProcessedFile.value = null;
};

defineExpose({ reset });
</script>

<template>
    <n-card class="detail-card">
        <div class="upload-component-container">
            <!-- Informasi Peminjam & Pencairan -->
            <NCard :bordered="false" class="info-card" size="small">
                <NGrid :cols="2" :x-gap="16" :y-gap="12">
                    <NGridItem>
                        <div class="info-item">
                            <div class="info-label">Peminjam</div>
                            <div class="info-value">
                                {{ displayData.name ?? "-" }}
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem>
                        <div class="info-item">
                            <div class="info-label">Departemen</div>
                            <div class="info-value">
                                <NTag
                                    :bordered="false"
                                    type="info"
                                    size="small"
                                >
                                    {{ displayData.department ?? "-" }}
                                </NTag>
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem>
                        <div class="info-item">
                            <div class="info-label">Tanggal Dicairkan</div>
                            <div class="info-value">
                                {{
                                    formatDate(displayData.disbursedDate ?? "-")
                                }}
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem>
                        <div class="info-item">
                            <div class="info-label">Jumlah Dicairkan</div>
                            <div class="info-value amount">
                                {{ displayData.disbursedAmount }}
                            </div>
                        </div>
                    </NGridItem>
                </NGrid>
            </NCard>

            <!-- Upload Header -->
            <div class="upload-header">
                <NIcon size="18" color="#18a058">
                    <DocumentOutline />
                </NIcon>
                <span class="upload-title">DOKUMEN BUKTI</span>
                <!-- <div class="header-actions" v-if="localFiles.length > 0">
                    <NButton
                        size="small"
                        text
                        type="error"
                        @click="removeAllFiles"
                    >
                        <template #icon>
                            <NIcon><TrashOutline /></NIcon>
                        </template>
                        Hapus Semua
                    </NButton>
                </div> -->
            </div>

            <!-- Upload Component -->
            <div class="upload-component">
                <NUpload
                    ref="uploadRef"
                    :multiple="multiple"
                    :accept="acceptTypes"
                    :max="maxFiles"
                    :default-upload="false"
                    @change="handleChange"
                    class="upload-area"
                >
                    <NUploadDragger>
                        <div class="upload-dragger-content">
                            <NIcon size="32" color="#18a058">
                                <CloudUploadOutline />
                            </NIcon>
                            <NText depth="3" size="small">
                                Klik atau tarik file ke sini
                            </NText>
                            <NText depth="3" size="tiny" class="file-hint">
                                {{ placeholder }} · {{ maxSize }}MB maks ·
                                {{ accept.join(", ") }}
                            </NText>
                        </div>
                    </NUploadDragger>
                </NUpload>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <NButton size="large" @click="handleCancel" class="cancel-btn">
                    Batal
                </NButton>
                <NButton
                    type="primary"
                    size="large"
                    :loading="submitting"
                    @click="handleSubmit"
                    class="submit-btn"
                >
                    <template #icon>
                        <NIcon><CheckmarkOutline /></NIcon>
                    </template>
                    Simpan Bukti
                </NButton>
            </div>
        </div>
    </n-card>
</template>

<style scoped>
.detail-card {
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.info-card {
    margin-bottom: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    background: #fafafc;
}

.info-item {
    padding: 4px 0;
}

.info-label {
    font-size: 12px;
    color: #8a8f99;
    margin-bottom: 4px;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 15px;
    font-weight: 500;
    color: #1f2d3d;
}

.info-value.amount {
    color: #18a058;
    font-weight: 600;
    font-size: 16px;
}

.upload-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
}

.upload-title {
    font-weight: 600;
    font-size: 16px;
    color: #1f2d3d;
    flex: 1;
}

.header-actions {
    display: flex;
    gap: 8px;
}

.upload-component {
    width: 100%;
    margin-bottom: 24px;
}

.upload-area {
    width: 100%;
}

.upload-dragger-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 24px;
}

.file-hint {
    color: #8a8f99;
}

.file-list {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.file-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e5e5e5;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
}

.file-icon {
    flex-shrink: 0;
    color: #18a058;
}

.file-name {
    font-size: 13px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-size {
    font-size: 11px;
    color: #8a8f99;
    flex-shrink: 0;
}

.file-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.preview-container {
    min-height: 400px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.preview-image {
    max-width: 100%;
    max-height: 500px;
    object-fit: contain;
}

.preview-pdf {
    width: 100%;
    height: 500px;
    border: none;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    padding-top: 8px;
    border-top: 1px solid #f0f0f0;
}

.cancel-btn,
.submit-btn {
    min-width: 120px;
    border-radius: 8px;
}

@media (max-width: 640px) {
    .action-buttons {
        flex-direction: column;
    }

    .cancel-btn,
    .submit-btn {
        width: 100%;
    }
}
</style>
