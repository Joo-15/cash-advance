<script setup>
import { computed, onMounted, ref, watch } from "vue";
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
    NForm,
    NSpace,
} from "naive-ui";
import {
    CloudUploadOutline,
    DocumentTextOutline,
    ImageOutline,
    EyeOutline,
    TrashOutline,
    DocumentOutline,
    CheckmarkOutline,
    SaveOutline,
    CloseOutline,
} from "@vicons/ionicons5";
import { formatDate, formatRupiah } from "@/utils/helpers";

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },

    loading: Boolean,
    showModal: Boolean,
    dataSelected: Array,
    closeModal: Function,
    submit: Function,
});

const message = useMessage();

const selectedItem = computed(() => props.dataSelected?.[0] || {});
const displayData = computed(() => {
    const item = selectedItem.value;
    const lastApproval = item?.approvals?.slice(-1)[0] || null;
    return {
        id: item?.id,
        purpose: item?.purpose,
        name: item?.user?.name,
        department: item?.user?.department?.name,
        attachment: item?.attachment || null,
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

console.log("selected row", props.dataSelected);

const fileList = ref([]);
const pdfUrl = ref(null);

// Get PDF URL
const getPdfUrl = (attachment) => {
    if (!attachment) return null;
    // Sesuaikan dengan URL storage Anda
    return `/storage/${attachment}`;
    // Atau jika menggunakan asset
    // return import.meta.env.VITE_APP_URL + '/storage/' + attachment
};

// Initialize existing PDF
const initializePdf = () => {
    if (displayData.value.attachment) {
        pdfUrl.value = getPdfUrl(displayData.value.attachment);

        // // Add to file list for preview
        // fileList.value = [
        //     {
        //         id: props.cashAdvance.id,
        //         name:
        //             props.cashAdvance.original_name ||
        //             props.cashAdvance.attachment.split("/").pop(),
        //         status: "finished",
        //         url: pdfUrl.value,
        //     },
        // ];
    }
};

// ✅ VALIDASI SEBELUM MASUK
const beforeUpload = ({ file }) => {
    const maxSizeMB = 2;

    // tipe file
    if (file.type !== "application/pdf") {
        message.error("File harus PDF");
        return false;
    }

    // ukuran file
    if (file.file.size / 1024 / 1024 > maxSizeMB) {
        message.error("Maksimal ukuran 2MB");
        return false;
    }

    // replace file lama
    fileList.value = [file];

    return false; // karena manual upload
};

// ✅ HANDLE CHANGE (backup kontrol)
const handleChange = ({ fileList: newFileList }) => {
    fileList.value = newFileList.slice(-1);
};

// ✅ SUBMIT
const submitForm = () => {
    if (!fileList.value.length) {
        message.error("File wajib diupload");
        return;
    }

    const formData = new FormData();

    // kirim relasi id
    formData.append("id", displayData.value.id);

    // kirim file
    formData.append("files[]", fileList.value[0].file);

    props.submit({
        values: formData,
        method: "put",
        url: "penggunaan-dana.update",
        id: displayData.value.id,

        onSuccess: () => {},
    });
};

console.log("pdf", pdfUrl.value);

onMounted(() => {
    initializePdf();
});
// defineExpose({ reset });
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
            </div>

            <!-- Upload Component -->
            <NForm>
                <div class="upload-component">
                    <NUpload
                        v-model:file-list="fileList"
                        :max="1"
                        accept=".pdf"
                        :default-upload="false"
                        @before-upload="beforeUpload"
                        @change="handleChange"
                    >
                        <NUploadDragger>
                            <div class="upload-dragger-content">
                                <NIcon size="32" color="#18a058">
                                    <CloudUploadOutline />
                                </NIcon>

                                <NText depth="3" size="small">
                                    Klik atau tarik file PDF ke sini
                                </NText>

                                <NText depth="3" size="tiny">
                                    Maksimal 2MB · PDF saja
                                </NText>
                            </div>
                        </NUploadDragger>
                    </NUpload>
                </div>
                <!-- <div v-if="pdfUrl" class="pdf-container">
                    <iframe
                        :src="pdfUrl"
                        class="pdf-iframe"
                        frameborder="0"
                        width="100%"
                        height="600px"
                    >
                    </iframe>
                </div> -->

                <!-- Action Buttons -->
                <div class="flex justify-end border-t pt-4 px-2 mt-4">
                    <n-space>
                        <n-button @click="closeModal">
                            <template #icon>
                                <n-icon>
                                    <CloseOutline />
                                </n-icon>
                            </template>
                            Batal
                        </n-button>

                        <n-button
                            type="primary"
                            :loading="loading"
                            :disabled="loading"
                            @click="submitForm"
                        >
                            <template #icon>
                                <n-icon>
                                    <SaveOutline />
                                </n-icon>
                            </template>
                            Simpan Bukti
                        </n-button>
                    </n-space>
                </div>
            </NForm>
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
</style>
