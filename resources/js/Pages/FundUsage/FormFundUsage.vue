<script setup>
import { ref, watch } from "vue";
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
    NProgress,
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

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
    maxSize: {
        type: Number,
        default: 5, // MB
    },
    maxFiles: {
        type: Number,
        default: 1,
    },
    accept: {
        type: Array,
        default: () => ["pdf"],
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: "Upload file",
    },
    // Informasi peminjam (bisa di-pass dari parent)
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
});

const emit = defineEmits(["update:files", "submit", "cancel"]);

const message = useMessage();
const localFiles = ref([...props.files]);
const showPreview = ref(false);
const previewFileData = ref(null);
const submitting = ref(false);

const acceptTypes = props.accept.map((ext) => `.${ext}`).join(",");

// Format Rupiah
const formatRupiah = (value) => {
    if (!value) return "-";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

watch(
    localFiles,
    (newFiles) => {
        emit("update:files", newFiles);
    },
    { deep: true },
);

watch(
    () => props.files,
    (newFiles) => {
        if (JSON.stringify(newFiles) !== JSON.stringify(localFiles.value)) {
            localFiles.value = [...newFiles];
        }
    },
    { deep: true },
);

const customRequest = ({ file, onFinish, onError }) => {
    // Validasi size
    if (file.file?.size > props.maxSize * 1024 * 1024) {
        message.error(`Ukuran file melebihi ${props.maxSize}MB`);
        onError();
        return;
    }

    // Validasi tipe
    const extension = file.name?.split(".").pop()?.toLowerCase();
    if (!props.accept.includes(extension)) {
        message.error(
            `Tipe file tidak didukung. Format: ${props.accept.join(", ")}`,
        );
        onError();
        return;
    }

    // Validasi jumlah file
    if (!props.multiple && localFiles.value.length >= props.maxFiles) {
        message.warning(`Maksimal ${props.maxFiles} file`);
        onError();
        return;
    }

    const newFile = {
        id: Date.now() + Math.random(),
        name: file.name,
        size: file.file?.size || 0,
        type: extension,
        file: file.file,
        url: file.file ? URL.createObjectURL(file.file) : null,
    };

    if (props.multiple) {
        localFiles.value.push(newFile);
    } else {
        localFiles.value = [newFile];
    }

    onFinish();
};

const handleChange = ({ file, fileList }) => {
    // handled by customRequest
};

const removeFile = (index) => {
    // Revoke object URL
    if (localFiles.value[index].url) {
        URL.revokeObjectURL(localFiles.value[index].url);
    }
    localFiles.value.splice(index, 1);
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

// Handle cancel
const handleCancel = () => {
    // Reset files
    localFiles.value.forEach((file) => {
        if (file.url) {
            URL.revokeObjectURL(file.url);
        }
    });
    localFiles.value = [];
    emit("cancel");
    message.info("Data dibatalkan");
};

// Handle submit
const handleSubmit = async () => {
    // Validasi file wajib
    if (localFiles.value.length === 0) {
        message.warning("Harap upload file terlebih dahulu");
        return;
    }

    submitting.value = true;

    try {
        // Simulasi API call atau emit event
        await new Promise((resolve) => setTimeout(resolve, 1000));

        const formData = {
            files: localFiles.value,
            borrowerInfo: props.borrowerInfo,
        };

        emit("submit", formData);
        message.success("Bukti berhasil disimpan");

        // Optional: reset setelah submit
        // localFiles.value = [];
    } catch (error) {
        console.error("Submit error:", error);
        message.error("Gagal menyimpan bukti");
    } finally {
        submitting.value = false;
    }
};

// Reset method untuk parent
const reset = () => {
    localFiles.value.forEach((file) => {
        if (file.url) {
            URL.revokeObjectURL(file.url);
        }
    });
    localFiles.value = [];
};

defineExpose({ reset });
</script>

<template>
    <div class="upload-component-container">
        <!-- Informasi Peminjam & Pencairan -->
        <NCard :bordered="false" class="info-card" size="small">
            <NGrid :cols="2" :x-gap="16" :y-gap="12">
                <NGridItem>
                    <div class="info-item">
                        <div class="info-label">PEMINJAM</div>
                        <div class="info-value">
                            {{ borrowerInfo?.name || "-" }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="info-item">
                        <div class="info-label">DEPARTEMEN</div>
                        <div class="info-value">
                            <NTag :bordered="false" type="info" size="small">
                                {{ borrowerInfo?.department || "-" }}
                            </NTag>
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="info-item">
                        <div class="info-label">TANGGAL CAIR</div>
                        <div class="info-value">
                            {{ borrowerInfo?.disbursementDate || "-" }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="info-item">
                        <div class="info-label">JUMLAH DICAIRKAN</div>
                        <div class="info-value amount">
                            {{ formatRupiah(borrowerInfo?.amount) }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem :span="2" v-if="borrowerInfo?.currentStage">
                    <div class="info-item">
                        <div class="info-label">TAHAP PENCAIRAN</div>
                        <div class="info-value stage">
                            <NProgress
                                type="line"
                                :percentage="
                                    (borrowerInfo.currentStage /
                                        borrowerInfo.totalStages) *
                                    100
                                "
                                :show-indicator="false"
                                style="width: 200px"
                            />
                            <span class="stage-text">
                                Tahap {{ borrowerInfo.currentStage }} /
                                {{ borrowerInfo.totalStages }}
                            </span>
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
        </div>

        <!-- Upload Component -->
        <div class="upload-component">
            <!-- Upload area (drag & drop) -->
            <NUpload
                :multiple="multiple"
                :accept="acceptTypes"
                :max="maxFiles"
                :default-upload="false"
                @change="handleChange"
                :custom-request="customRequest"
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
                        <n-text depth="3" size="tiny" class="file-hint">
                            {{ placeholder }} · {{ maxSize }}MB maks
                        </n-text>
                    </div>
                </NUploadDragger>
            </NUpload>

            <!-- File list -->
            <div class="file-list" v-if="localFiles.length > 0">
                <div
                    v-for="(file, index) in localFiles"
                    :key="file.id || index"
                    class="file-item"
                >
                    <div class="file-info">
                        <n-icon size="16" class="file-icon">
                            <DocumentTextOutline v-if="isPdf(file.name)" />
                            <ImageOutline v-else />
                        </n-icon>
                        <span class="file-name" :title="file.name">
                            {{ truncateFileName(file.name) }}
                        </span>
                        <span class="file-size">{{
                            formatFileSize(file.size)
                        }}</span>
                    </div>
                    <div class="file-actions">
                        <NButton
                            size="tiny"
                            text
                            @click="previewFile(file)"
                            v-if="canPreview(file)"
                        >
                            <template #icon>
                                <n-icon><EyeOutline /></n-icon>
                            </template>
                        </NButton>
                        <n-button
                            size="tiny"
                            text
                            type="error"
                            @click="removeFile(index)"
                        >
                            <template #icon>
                                <n-icon><TrashOutline /></n-icon>
                            </template>
                        </n-button>
                    </div>
                </div>
            </div>

            <!-- Preview Modal -->
            <NModal
                v-model:show="showPreview"
                preset="card"
                title="Preview File"
                style="width: 80%; max-width: 800px"
            >
                <div class="preview-container">
                    <img
                        v-if="
                            previewFileData && previewFileData.type === 'image'
                        "
                        :src="previewFileData.url"
                        class="preview-image"
                    />
                    <iframe
                        v-else-if="
                            previewFileData && previewFileData.type === 'pdf'
                        "
                        :src="previewFileData.url"
                        class="preview-pdf"
                    />
                    <n-empty
                        v-else
                        description="Tidak dapat preview file ini"
                    />
                </div>
            </NModal>
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
</template>

<style scoped>
.upload-component-container {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
}

/* Info Card */
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

.info-value.stage {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.stage-text {
    font-size: 13px;
    color: #5e6b7c;
}

/* Upload Header */
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
}

/* Upload Component */
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

/* Action Buttons */
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

/* Responsive */
@media (max-width: 640px) {
    .action-buttons {
        flex-direction: column;
    }

    .cancel-btn,
    .submit-btn {
        width: 100%;
    }

    .info-value.stage {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
