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
    NForm,
    NFormItem,
    NInput,
    NInputNumber,
    NSpace,
    NDivider,
    NAlert,
    NSpin,
    useMessage,
    useDialog,
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
    AddOutline,
    ReceiptOutline,
    CalculatorOutline,
    AlertCircleOutline,
    CloseCircleOutline,
    CheckmarkCircleOutline,
    PeopleOutline,
    InformationCircleOutline,
    DownloadOutline,
} from "@vicons/ionicons5";
import { formatDate, formatRupiah } from "@/utils/helpers";
import { toTypedSchema } from "@vee-validate/yup";
import { fundUsageSchema } from "@/Validations/validationSchemas";
import { useForm } from "vee-validate";

const props = defineProps({
    files: {
        type: Array,
        default: () => [],
    },
    roleName: String,
    loading: Boolean,
    showModal: Boolean,
    dataSelected: Array,
    closeModal: Function,
    submit: Function,
});

const emit = defineEmits([
    "update:attachment",
    "file-uploaded",
    "file-removed",
]);

const message = useMessage();
const dialog = useDialog();
const showPdfModal = ref(false);

// State
const fileList = ref([]);
const pdfUrl = ref(null);
const submitting = ref(false);
const uploadError = ref("");

// Computed
const selectedItem = computed(() => props.dataSelected?.[0] || {});
const displayData = computed(() => {
    const item = selectedItem.value;
    const lastApproval = item?.approvals?.slice(-1)[0] || null;
    const disbursement = item?.disbursement || {};
    // console.log()
    return {
        id: item?.id,
        purpose: item?.purpose,
        name: item?.user?.name,
        department: item?.user?.department?.name,
        attachment: item?.attachment || null,
        amount: formatRupiah(item?.amount),
        rawAmount: item?.amount || 0,
        disbursedAmount: formatRupiah(disbursement?.amount) || "-",
        rawDisbursedAmount: disbursement?.amount || 0,
        disbursedDate: disbursement?.disbursed_at
            ? disbursement.disbursed_at.split(" ")[0]
            : "-",
        approveDate: lastApproval?.approved_at
            ? lastApproval.approved_at.split(" ")[0]
            : null,
        reportStatus: disbursement?.report_status || "not_submitted",
        reportNotes: disbursement?.report_notes || "",
        financeNotes: disbursement?.finance_notes || "",
        totalSpent: disbursement?.total_spent || null,
    };
});

// console.log("test", displayData.value.reportStatus);
const getFileName = (attachment) => {
    if (!attachment) return "Dokumen.pdf";
    if (typeof attachment === "string") {
        return attachment.split("/").pop() || "Dokumen.pdf";
    }
    if (attachment.name) return attachment.name;
    if (attachment.original_name) return attachment.original_name;
    return "Dokumen Bukti.pdf";
};

// Get file size
const getFileSize = (attachment) => {
    if (!attachment) return "";
    if (attachment.size) {
        const bytes = attachment.size;
        const kilobytes = bytes / 1024;
        if (kilobytes < 1024) {
            return `${kilobytes.toFixed(2)} KB`;
        }
        return `${(kilobytes / 1024).toFixed(2)} MB`;
    }
    return "PDF Document";
};

const getAlertType = () => {
    const statusMap = {
        submitted: "warning",
        approved: "success",
        rejected: "error",
    };
    return statusMap[displayData.value.reportStatus] || "info";
};

const getAlertTitle = () => {
    const titleMap = {
        submitted: "Menunggu Review",
        approved: "Laporan Disetujui",
        rejected: "Laporan Ditolak",
    };
    return titleMap[displayData.value.reportStatus] || "Informasi Laporan";
};

const getAlertMessage = () => {
    if (displayData.value.reportStatus === "submitted") {
        return "Laporan sedang menunggu review dari finance.";
    } else if (displayData.value.reportStatus === "approved") {
        return "Laporan telah disetujui oleh finance.";
    } else if (displayData.value.reportStatus === "rejected") {
        return `Laporan ditolak. Alasan: ${displayData.value.financeNotes || "Tidak ada catatan"}`;
    }
    return "";
};

const getSisaDana = () => {
    const disbursed = displayData.value.disbursedAmount || 0;
    const spent = displayData.value.totalSpent || 0;
    return disbursed - spent;
};

const getSisaDanaClass = () => {
    const sisa = getSisaDana();
    if (sisa < 0) return "text-danger";
    if (sisa === 0) return "text-warning";
    return "text-success";
};

const getStatusType = () => {
    const statusMap = {
        not_submitted: "default",
        submitted: "warning",
        approved: "success",
        rejected: "error",
    };
    return statusMap[displayData.value.reportStatus] || "default";
};

const getStatusLabel = () => {
    const statusMap = {
        not_submitted: "Belum Dikirim",
        submitted: "Menunggu Review",
        approved: "Disetujui",
        rejected: "Ditolak",
    };
    return statusMap[displayData.value.reportStatus] || "-";
};

const getReviewStatusType = () => {
    const statusMap = {
        submitted: "warning",
        approved: "success",
        rejected: "error",
    };
    return statusMap[displayData.value.reportStatus] || "default";
};

const getReviewStatusLabel = () => {
    const statusMap = {
        submitted: "Menunggu Review",
        approved: "Disetujui",
        rejected: "Ditolak",
    };
    return statusMap[displayData.value.reportStatus] || "-";
};

const {
    handleSubmit,
    errors,
    defineField,
    setFieldValue,
    validateField,
    setFieldError,
} = useForm({
    validationSchema: toTypedSchema(fundUsageSchema),
    initialValues: {
        id: null,
        total_spent: null,
        report_notes: "",
        attachment: null,
    },
});

// Open PDF Modal
const openPdfModal = () => {
    showPdfModal.value = true;
};

// Methods
const getPdfUrl = (attachment) => {
    if (!attachment) return null;
    return `/storage/${attachment}`;
};

const initializeData = () => {
    // Initialize form with existing data
    // if (displayData.value.totalSpent) {
    //     reportForm.value.total_spent = displayData.value.totalSpent;
    //     reportForm.value.report_notes = displayData.value.reportNotes || "";
    // } else {
    //     reportForm.value.total_spent = null;
    //     reportForm.value.report_notes = "";
    // }

    // Initialize file list
    if (displayData.value.attachment) {
        pdfUrl.value = getPdfUrl(displayData.value.attachment);
    }
};

const beforeUpload = (data) => {
    const file = data.file?.file || data.file;

    // Validasi tipe file
    if (file.type !== "application/pdf") {
        uploadError.value = "File harus berupa PDF";
        setFieldError("attachment", "File harus berupa PDF");
        return false;
    }

    // Validasi ukuran file
    if (file.size > 2 * 1024 * 1024) {
        uploadError.value = "Ukuran file maksimal 2MB";
        setFieldError("attachment", "Ukuran file maksimal 2MB");
        return false;
    }

    uploadError.value = "";
    return true;
};

// Handle file change
const handleChange = async (data) => {
    const fileItem = data.file;
    const file = fileItem?.file;

    if (!file) {
        setFieldValue("attachment", null);
        pdfUrl.value = "";
        emit("update:attachment", null);
        return;
    }

    // Set nilai ke form
    setFieldValue("attachment", file);

    // Trigger validasi
    await validateField("attachment");

    // Buat URL preview
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
    }
    pdfUrl.value = URL.createObjectURL(file);

    // Emit event
    emit("update:attachment", file);
    emit("file-uploaded", file);

    // Clear error jika sukses
    if (!errors.value?.attachment) {
        uploadError.value = "";
    }
};

// Clear error
const clearError = () => {
    uploadError.value = "";
    if (errors.value?.attachment) {
        setFieldError("attachment", undefined);
    }
};

// Remove file
const removeFile = async () => {
    // Revoke URL
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
    }

    // Clear state
    fileList.value = [];
    pdfUrl.value = "";
    uploadError.value = "";

    // Update form
    setFieldValue("attachment", null);
    await validateField("attachment");

    // Emit event
    emit("update:attachment", null);
    emit("file-removed");
};

/*
Form Setup (VeeValidate)
*/

const [total_spent] = defineField("total_spent");
const [report_notes] = defineField("report_notes");
// const [attachment] = defineField("attachment");

// Submit laporan
const submitForm = handleSubmit(async (values) => {
    dialog.info({
        title: "Konfirmasi Submit Laporan",
        content:
            "Pastikan semua data sudah benar. Laporan tidak dapat diubah setelah disubmit.",
        positiveText: "Ya, Submit",
        negativeText: "Periksa Kembali",
        onPositiveClick: async () => {
            submitting.value = true;

            const formData = new FormData();
            formData.append("id", displayData.value.id);
            formData.append("total_spent", values.total_spent);
            formData.append("report_notes", values.report_notes);

            if (fileList.value[0]?.file) {
                formData.append("files[]", fileList.value[0].file);
            }

            await props.submit({
                values: formData,
                method: "put",
                url: "penggunaan-dana.update",
                id: displayData.value.id,
                onSuccess: () => {
                    // submitting.value = false;
                    // message.success("Laporan berhasil disubmit");
                    // props.closeModal();
                },
                onError: () => {
                    // submitting.value = false;
                },
            });
        },
    });
});

const isReportLocked = computed(() => {
    return ["submitted", "approved"].includes(displayData.value.reportStatus);
});

onMounted(() => {
    initializeData();
});
</script>

<template>
    <div class="report-container">
        <!-- Alert untuk status sudah submit -->
        <NAlert
            v-if="isReportLocked"
            :type="getAlertType()"
            :title="getAlertTitle()"
            class="mb-4"
        >
            {{ getAlertMessage() }}

            <!-- Tombol aksi untuk finance -->
            <template
                #action
                v-if="
                    isReportLocked && displayData.reportStatus === 'submitted'
                "
            >
                <NSpace>
                    <NButton
                        size="small"
                        type="success"
                        @click="openApproveModal"
                    >
                        <template #icon>
                            <NIcon><CheckmarkCircleOutline /></NIcon>
                        </template>
                        Setujui
                    </NButton>
                    <NButton size="small" type="error" @click="openRejectModal">
                        <template #icon>
                            <NIcon><CloseCircleOutline /></NIcon>
                        </template>
                        Tolak
                    </NButton>
                </NSpace>
            </template>
        </NAlert>

        <!-- Informasi Cash Advance -->
        <NCard :bordered="false" class="info-card" size="small">
            <template #header>
                <div class="card-header">
                    <NIcon size="18" color="#18a058">
                        <DocumentOutline />
                    </NIcon>
                    <span class="card-title"
                        >Informasi Pengajuan & Pencairan</span
                    >
                </div>
            </template>

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
                            <NTag :bordered="false" type="info" size="small">
                                {{ displayData.department ?? "-" }}
                            </NTag>
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="info-item">
                        <div class="info-label">Tanggal Dicairkan</div>
                        <div class="info-value">
                            {{ formatDate(displayData.disbursedDate ?? "-") }}
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
                <NGridItem :span="2">
                    <div class="info-item">
                        <div class="info-label">Keperluan</div>
                        <div class="info-value">
                            {{ displayData.purpose || "-" }}
                        </div>
                    </div>
                </NGridItem>
            </NGrid>
        </NCard>

        <!-- Form Laporan (hanya jika belum submit) -->
        <n-form @submit.prevent="submitForm">
            <div v-if="!isReportLocked">
                <!-- Form Utama -->
                <NCard :bordered="false" class="form-card" size="small">
                    <template #header>
                        <div class="card-header">
                            <NIcon size="18" color="#18a058">
                                <ReceiptOutline />
                            </NIcon>
                            <span class="card-title">Detail Pengeluaran</span>
                        </div>
                    </template>

                    <n-form-item
                        label="Total Pengeluaran"
                        :validation-status="errors.total_spent ? 'error' : null"
                        :feedback="errors.total_spent"
                        required
                    >
                        <n-input
                            v-model:value="total_spent"
                            placeholder="Masukkan total pengeluaran"
                            type="number"
                            :disabled="isReportLocked"
                        />
                    </n-form-item>

                    <!-- Catatan -->
                    <n-form-item
                        label="Catatan Pengeluaran"
                        :validation-status="
                            errors.report_notes ? 'error' : null
                        "
                        :feedback="errors.report_notes"
                        required
                    >
                        <NInput
                            v-model:value="report_notes"
                            type="textarea"
                            rows="4"
                            placeholder="Jelaskan rincian penggunaan dana secara detail..."
                            :maxlength="1000"
                            show-count
                            :disabled="isReportLocked"
                        />
                    </n-form-item>
                </NCard>

                <!-- Upload Dokumen Bukti -->
                <NCard :bordered="false" class="upload-card" size="small">
                    <template #header>
                        <div class="card-header">
                            <NIcon size="18" color="#18a058">
                                <CloudUploadOutline />
                            </NIcon>
                            <span class="card-title">Dokumen Bukti</span>
                            <span class="card-subtitle">(Wajib diisi)</span>
                        </div>
                    </template>

                    <!-- Tampilkan Error Alert -->
                    <NAlert
                        v-if="errors?.attachment || uploadError"
                        type="error"
                        closable
                        @close="clearError"
                        class="error-alert"
                    >
                        <template #icon>
                            <NIcon>
                                <AlertCircleOutline />
                            </NIcon>
                        </template>
                        {{ errors?.attachment || uploadError }}
                    </NAlert>

                    <!-- Upload Area -->
                    <div v-if="!pdfUrl && fileList.length === 0">
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

                    <!-- Preview File -->
                    <div v-else class="file-preview">
                        <div class="file-info">
                            <NIcon size="24" color="#18a058">
                                <DocumentTextOutline />
                            </NIcon>
                            <div class="file-details">
                                <div class="file-name">
                                    {{
                                        fileList[0]?.name ||
                                        (pdfUrl
                                            ? pdfUrl.split("/").pop()
                                            : "Bukti Laporan.pdf")
                                    }}
                                </div>
                                <div class="file-size">
                                    {{
                                        fileList[0]?.file?.size
                                            ? (
                                                  fileList[0].file.size / 1024
                                              ).toFixed(2) + " KB"
                                            : "PDF Document"
                                    }}
                                </div>
                            </div>
                            <div class="file-actions">
                                <NButton
                                    size="small"
                                    quaternary
                                    @click="removeFile"
                                    :disabled="isReportLocked"
                                >
                                    <template #icon>
                                        <NIcon><TrashOutline /></NIcon>
                                    </template>
                                </NButton>
                            </div>
                        </div>
                    </div>
                </NCard>
            </div>

            <!-- View Mode (setelah submit) -->
            <div v-else>
                <NCard :bordered="false" class="view-card" size="small">
                    <template #header>
                        <div class="card-header">
                            <NIcon size="18" color="#18a058">
                                <ReceiptOutline />
                            </NIcon>
                            <span class="card-title">Ringkasan Laporan</span>
                            <div class="status-badge">
                                <NTag
                                    :type="getStatusType()"
                                    size="small"
                                    :bordered="false"
                                >
                                    {{ getStatusLabel() }}
                                </NTag>
                            </div>
                        </div>
                    </template>

                    <NGrid :cols="2" :x-gap="16" :y-gap="12">
                        <NGridItem>
                            <div class="info-item">
                                <div class="info-label">Total Pengeluaran</div>
                                <div class="info-value amount">
                                    {{ formatRupiah(displayData.totalSpent) }}
                                </div>
                            </div>
                        </NGridItem>
                        <NGridItem>
                            <div class="info-item">
                                <div class="info-label">Sisa Dana</div>
                                <div
                                    class="info-value"
                                    :class="getSisaDanaClass()"
                                >
                                    {{ formatRupiah(getSisaDana()) }}
                                </div>
                            </div>
                        </NGridItem>
                        <NGridItem :span="2">
                            <div class="info-item">
                                <div class="info-label">
                                    Catatan Pengeluaran
                                </div>
                                <div class="info-value">
                                    {{ displayData.reportNotes || "-" }}
                                </div>
                            </div>
                        </NGridItem>
                    </NGrid>
                </NCard>

                <!-- Review Finance Section (untuk role finance) -->
                <NCard
                    v-if="displayData.reportStatus !== 'not_submitted'"
                    :bordered="false"
                    class="review-card"
                    size="small"
                >
                    <template #header>
                        <div class="card-header">
                            <NIcon size="18" color="#18a058">
                                <PeopleOutline />
                            </NIcon>
                            <span class="card-title">Review Finance</span>
                        </div>
                    </template>

                    <!-- Status Review -->
                    <div class="review-status">
                        <div class="status-item">
                            <div class="status-label">Status Review:</div>
                            <div class="status-value">
                                <NTag
                                    :type="getReviewStatusType()"
                                    size="medium"
                                >
                                    {{ getReviewStatusLabel() }}
                                </NTag>
                            </div>
                        </div>

                        <div class="status-item" v-if="displayData.reviewedBy">
                            <div class="status-label">Direview oleh:</div>
                            <div class="status-value">
                                {{ displayData.reviewedBy }}
                            </div>
                        </div>

                        <div class="status-item" v-if="displayData.reviewedAt">
                            <div class="status-label">Tanggal review:</div>
                            <div class="status-value">
                                {{ formatDateTime(displayData.reviewedAt) }}
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Finance -->
                    <div
                        class="finance-notes-section"
                        v-if="displayData.financeNotes"
                    >
                        <div class="section-title">Catatan Finance</div>
                        <div class="finance-notes-content">
                            {{ displayData.financeNotes }}
                        </div>
                    </div>

                    <!-- Form Review untuk finance -->
                    <div
                        v-if="
                            props.roleName === 'Finance' &&
                            displayData.reportStatus === 'submitted'
                        "
                        class="review-form"
                    >
                        <n-divider>Review Laporan</n-divider>

                        <n-form-item label="Catatan Review">
                            <n-input
                                v-model:value="reviewNotes"
                                type="textarea"
                                rows="3"
                                placeholder="Masukkan catatan untuk pemohon..."
                            />
                        </n-form-item>

                        <div class="review-actions">
                            <NButton
                                type="error"
                                @click="openRejectModal"
                                :loading="reviewLoading"
                            >
                                <template #icon>
                                    <NIcon><CloseCircleOutline /></NIcon>
                                </template>
                                Tolak Laporan
                            </NButton>

                            <NButton
                                type="success"
                                @click="openApproveModal"
                                :loading="reviewLoading"
                            >
                                <template #icon>
                                    <NIcon><CheckmarkCircleOutline /></NIcon>
                                </template>
                                Setujui Laporan
                            </NButton>
                        </div>
                    </div>
                </NCard>

                <!-- Tampilan status untuk pemohon -->
                <NCard
                    v-else-if="displayData.reportStatus !== 'not_submitted'"
                    :bordered="false"
                    class="info-card"
                    size="small"
                >
                    <template #header>
                        <div class="card-header">
                            <NIcon size="18" color="#18a058">
                                <InformationCircleOutline />
                            </NIcon>
                            <span class="card-title">Status Laporan</span>
                        </div>
                    </template>

                    <div class="status-info">
                        <div class="status-badge-large">
                            <NTag
                                :type="getStatusType()"
                                size="large"
                                :bordered="false"
                            >
                                {{ getStatusLabel() }}
                            </NTag>
                        </div>

                        <div
                            v-if="displayData.financeNotes"
                            class="finance-feedback"
                        >
                            <div class="feedback-title">
                                Catatan dari Finance:
                            </div>
                            <div class="feedback-content">
                                {{ displayData.financeNotes }}
                            </div>
                        </div>

                        <div
                            v-if="displayData.reportStatus === 'rejected'"
                            class="rejected-message"
                        >
                            <NAlert type="error" title="Laporan Ditolak">
                                Laporan Anda ditolak. Silakan perbaiki dan
                                submit ulang.
                            </NAlert>
                        </div>
                    </div>
                </NCard>

                <!-- Dokumen Bukti dengan Tombol View -->
                <NCard
                    v-if="displayData.attachment"
                    :bordered="false"
                    class="view-card"
                    size="small"
                >
                    <template #header>
                        <div class="card-header">
                            <NIcon size="18" color="#18a058">
                                <DocumentOutline />
                            </NIcon>
                            <span class="card-title">Dokumen Bukti</span>
                        </div>
                    </template>

                    <div class="document-info">
                        <div class="document-details">
                            <NIcon size="32" color="#18a058">
                                <DocumentTextOutline />
                            </NIcon>
                            <div class="document-meta">
                                <div class="document-name">
                                    {{ getFileName(displayData.attachment) }}
                                </div>
                                <div class="document-size">
                                    {{ getFileSize(displayData.attachment) }}
                                </div>
                            </div>
                        </div>

                        <NButton
                            type="primary"
                            size="medium"
                            @click="openPdfModal"
                        >
                            <template #icon>
                                <NIcon><EyeOutline /></NIcon>
                            </template>
                            Lihat Dokumen
                        </NButton>
                    </div>
                </NCard>

                <!-- Modal PDF Viewer -->
                <NModal
                    v-model:show="showPdfModal"
                    preset="card"
                    title="Dokumen Bukti"
                    style="width: 90%; max-width: 1200px"
                    :closable="true"
                >
                    <template #header-extra>
                        <NButton size="small" @click="downloadPdf">
                            <template #icon>
                                <NIcon><DownloadOutline /></NIcon>
                            </template>
                            Download
                        </NButton>
                    </template>

                    <iframe
                        :src="getPdfUrl(displayData.attachment)"
                        class="pdf-iframe-modal"
                        frameborder="0"
                    ></iframe>
                </NModal>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons" v-if="!isReportLocked">
                <NButton @click="closeModal">
                    <template #icon>
                        <NIcon><CloseOutline /></NIcon>
                    </template>
                    Batal
                </NButton>

                <NButton
                    type="primary"
                    attr-type="submit"
                    :loading="loading"
                    :disabled="loading"
                >
                    <template #icon>
                        <NIcon><SaveOutline /></NIcon>
                    </template>
                    Submit Laporan
                </NButton>
            </div>
        </n-form>

        <!-- Catatan Finance untuk Review -->
        <!-- <NCard
            v-if="displayData.reportStatus === 'submitted'"
            :bordered="false"
            class="finance-review-card"
            size="small"
        >
            <template #header>
                <div class="card-header">
                    <NIcon size="18" color="#18a058">
                        <PeopleOutline />
                    </NIcon>
                    <span class="card-title">Review Finance</span>
                    <NTag type="warning" size="small" :bordered="false">
                        Menunggu Review
                    </NTag>
                </div>
            </template>

            <n-form-item label="Catatan Finance">
                <n-input
                    v-model:value="displayData.financeNotes"
                    type="textarea"
                    rows="4"
                    placeholder="Masukkan catatan untuk pemohon (opsional)..."
                    :maxlength="500"
                    show-count
                />
            </n-form-item>

            <div class="review-actions">
                <NButton
                    type="error"
                    @click="rejectReport"
                    :loading="loading"
                    size="medium"
                >
                    <template #icon>
                        <NIcon><CloseCircleOutline /></NIcon>
                    </template>
                    Tolak Laporan
                </NButton>

                <NButton
                    type="success"
                    @click="approveReport"
                    :loading="loading"
                    size="medium"
                >
                    <template #icon>
                        <NIcon><CheckmarkCircleOutline /></NIcon>
                    </template>
                    Setujui Laporan
                </NButton>
            </div>
        </NCard> -->

        <!-- Alert untuk hasil review -->
        <NAlert
            v-if="displayData.reportStatus === 'approved'"
            type="success"
            class="review-alert"
            title="Laporan Disetujui"
        >
            <template #icon>
                <NIcon><CheckmarkCircleOutline /></NIcon>
            </template>
            Laporan telah disetujui oleh finance.
            <div v-if="displayData.financeNotes" class="finance-notes">
                <strong>Catatan Finance:</strong> {{ displayData.financeNotes }}
            </div>
        </NAlert>

        <NAlert
            v-if="displayData.reportStatus === 'rejected'"
            type="error"
            class="review-alert"
            title="Laporan Ditolak"
        >
            <template #icon>
                <NIcon><CloseCircleOutline /></NIcon>
            </template>
            Laporan ditolak. Silakan perbaiki dan submit ulang.
            <div v-if="displayData.financeNotes" class="finance-notes">
                <strong>Alasan Penolakan:</strong>
                {{ displayData.financeNotes }}
            </div>
        </NAlert>
    </div>
</template>

<style scoped>
.report-container {
    max-height: 80vh;
    overflow-y: auto;
    padding: 4px;
}

.info-card,
.form-card,
.upload-card,
.view-card {
    margin-bottom: 20px;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-title {
    font-weight: 600;
    font-size: 15px;
    color: #1f2d3d;
}

.card-subtitle {
    font-size: 12px;
    color: #e53935;
    margin-left: 4px;
}

.info-item {
    padding: 4px 0;
}

.info-label {
    font-size: 12px;
    color: #8a8f99;
    margin-bottom: 4px;
    letter-spacing: 0.3px;
}

.info-value {
    font-size: 14px;
    font-weight: 500;
    color: #1f2d3d;
}

.info-value.amount {
    color: #18a058;
    font-weight: 600;
    font-size: 16px;
}

.info-value.success {
    color: #18a058;
}

.info-value.error {
    color: #e53935;
}

.info-value.info {
    color: #2080f0;
}

.difference-alert {
    width: 100%;
}

.difference-text {
    font-size: 12px;
    padding: 4px 8px;
}

.expense-items-section {
    margin-bottom: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
    color: #4a5568;
}

.empty-expense {
    padding: 24px;
    text-align: center;
    background: #fafafc;
    border-radius: 8px;
    border: 1px dashed #d9d9d9;
}

.expense-items-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.expense-item {
    padding: 12px;
    background: #fafafc;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
}

.expense-item-row {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.expense-description {
    flex: 2;
    min-width: 200px;
}

.expense-amount {
    width: 160px;
}

.expense-date {
    width: 130px;
}

.expense-action {
    width: 32px;
}

.expense-subtotal {
    text-align: right;
    padding: 8px 12px;
    font-size: 13px;
    border-top: 1px solid #e5e5e5;
    margin-top: 8px;
}

.expense-subtotal strong {
    color: #18a058;
    margin-left: 8px;
    font-size: 14px;
}

.upload-dragger-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 32px;
}

.file-preview {
    width: 100%;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #fafafc;
    border-radius: 8px;
    margin-bottom: 16px;
}

.file-details {
    flex: 1;
}

.file-name {
    font-size: 13px;
    font-weight: 500;
    color: #1f2d3d;
}

.file-size {
    font-size: 11px;
    color: #8a8f99;
    margin-top: 2px;
}

.file-actions {
    display: flex;
    gap: 8px;
}

.pdf-preview {
    margin-top: 16px;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    overflow: hidden;
}

.pdf-iframe {
    width: 100%;
    height: 400px;
    border: none;
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
}

/* Scrollbar styling */
.report-container::-webkit-scrollbar {
    width: 6px;
}

.report-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.report-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.report-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
