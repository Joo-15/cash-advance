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
    NSpace,
    NDivider,
    NAlert,
    useMessage,
    useDialog,
    NInputNumber,
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
import {
    formatDate,
    formatRupiah,
    parseNumber,
    formatNumber,
} from "@/utils/helpers";
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

// State
const showPdfModal = ref(false);
const fileList = ref([]);
const pdfUrl = ref(null);
const submitting = ref(false);
const uploadError = ref("");
const reviewNotes = ref(""); // Untuk catatan review finance
const reviewLoading = ref(false);

// Computed
const selectedItem = computed(() => props.dataSelected?.[0] || {});
const displayData = computed(() => {
    const item = selectedItem.value;
    const lastApproval = item?.approvals?.slice(-1)[0] || null;
    const disbursement = item?.disbursement || {};

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
        reviewedBy: disbursement?.reviewed_by || null,
        reviewedAt: disbursement?.reviewed_at || null,
    };
});

// Helper Functions
const getFileName = (attachment) => {
    if (!attachment) return "Dokumen.pdf";
    if (typeof attachment === "string") {
        return attachment.split("/").pop() || "Dokumen.pdf";
    }
    if (attachment.name) return attachment.name;
    if (attachment.original_name) return attachment.original_name;
    return "Dokumen Bukti.pdf";
};

const getPdfUrl = (attachment) => {
    if (!attachment) return null;
    return `/storage/${attachment}`;
};

// Status & Alert Helpers
const isReportLocked = computed(() => {
    return ["submitted", "approved"].includes(displayData.value.reportStatus);
});

const getAlertType = () => {
    const statusMap = {
        not_submitted: "warning",
        submitted: "warning",
        approved: "success",
        rejected: "error",
    };
    return statusMap[displayData.value.reportStatus] || "info";
};

const getAlertTitle = () => {
    const titleMap = {
        not_submitted: "Belum Dikirim",
        submitted: "Menunggu Review",
        approved: "Laporan Disetujui",
        rejected: "Laporan Ditolak",
    };
    return titleMap[displayData.value.reportStatus] || "Informasi Laporan";
};

const getAlertMessage = () => {
    if (displayData.value.reportStatus === "not_submitted") {
        return "Laporan belum dikirim";
    } else if (displayData.value.reportStatus === "submitted") {
        return "Laporan sedang menunggu review dari finance.";
    } else if (displayData.value.reportStatus === "approved") {
        return "Laporan telah disetujui oleh finance.";
    } else if (displayData.value.reportStatus === "rejected") {
        return `${displayData.value.financeNotes || "Tidak ada catatan"}`;
    }
    return "";
};

const getSisaDana = () => {
    const disbursed = displayData.value.rawDisbursedAmount || 0;
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

// Form Setup
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
        attachment: undefined,
    },
});

const [total_spent] = defineField("total_spent");
const [report_notes] = defineField("report_notes");
const [attachment] = defineField("attachment");

// File Upload Methods
const beforeUpload = (data) => {
    const file = data.file?.file || data.file;

    if (file.type !== "application/pdf") {
        uploadError.value = "File harus berupa PDF";
        setFieldError("attachment", "File harus berupa PDF");
        return false;
    }

    if (file.size > 2 * 1024 * 1024) {
        uploadError.value = "Ukuran file maksimal 2MB";
        setFieldError("attachment", "Ukuran file maksimal 2MB");
        return false;
    }

    uploadError.value = "";
    return true;
};

const handleChange = async (data) => {
    const fileItem = data.file;
    const file = fileItem?.file;

    if (!file) {
        setFieldValue("attachment", null);
        pdfUrl.value = "";
        emit("update:attachment", null);
        return;
    }

    setFieldValue("attachment", file);
    await validateField("attachment");

    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
    }
    pdfUrl.value = URL.createObjectURL(file);

    emit("update:attachment", file);
    emit("file-uploaded", file);

    if (!errors.value?.attachment) {
        uploadError.value = "";
    }
};

const clearError = () => {
    uploadError.value = "";
    if (errors.value?.attachment) {
        setFieldError("attachment", undefined);
    }
};

const removeFile = async () => {
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
    }

    fileList.value = [];
    pdfUrl.value = "";
    uploadError.value = "";

    setFieldValue("attachment", null);
    await validateField("attachment");

    emit("update:attachment", null);
    emit("file-removed");
};

// Modal Methods
const openPdfModal = () => {
    showPdfModal.value = true;
};

// Submit Report
const submitForm = handleSubmit(async (values) => {
    // const hasFile = fileList.value.length > 0 || displayData.value.attachment;

    // if (!hasFile) {
    //     message.error("Dokumen bukti wajib diupload");
    //     setFieldError("attachment", "Dokumen bukti wajib diupload");
    //     return;
    // }

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
                    submitting.value = false;
                },
                onError: () => {
                    submitting.value = false;
                },
            });
        },
    });
});

// Fungsi untuk menyetujui laporan dengan catatan
const approveReport = async () => {
    if (
        !reviewNotes.value.trim() &&
        displayData.value.reportStatus === "submitted"
    ) {
        dialog.warning({
            title: "Catatan Kosong",
            content: "Apakah Anda ingin melanjutkan tanpa catatan?",
            positiveText: "Ya, Lanjutkan",
            negativeText: "Batal",
            onPositiveClick: async () => {
                await submitReview("approved");
            },
        });
        return;
    }

    await submitReview("approved");
};

// Fungsi untuk menolak laporan dengan catatan
const rejectReport = async () => {
    if (!reviewNotes.value.trim()) {
        message.warning(
            "Mohon isi catatan penolakan untuk memberikan alasan kepada pemohon",
        );
        return;
    }

    await submitReview("rejected");
};

// Fungsi submit review ke backend
const submitReview = async (status) => {
    reviewLoading.value = true;

    const formData = new FormData();
    formData.append("id", displayData.value.id);
    formData.append("report_status", status);
    formData.append("finance_notes", reviewNotes.value);

    try {
        await props.submit({
            values: formData,
            method: "put",
            url: "penggunaan-dana.review",
            id: displayData.value.id,
            onSuccess: () => {},
            onError: (error) => {},
        });
    } finally {
        reviewLoading.value = false;
    }
};

// const openRejectModal = () => {
//     dialog.info({
//         title: "Konfirmasi Penolakan",
//         content: "Apakah Anda yakin ingin menolak laporan ini?",
//         positiveText: "Ya, Tolak",
//         negativeText: "Batal",
//         onPositiveClick: async () => {
//             reviewLoading.value = true;
//             reviewLoading.value = false;
//         },
//     });
// };

// Initialize Data
const initializeData = () => {
    // Reset semua state
    fileList.value = [];
    uploadError.value = "";

    if (pdfUrl.value && pdfUrl.value.startsWith("blob:")) {
        URL.revokeObjectURL(pdfUrl.value);
    }
    pdfUrl.value = "";

    // ✅ Untuk existing file, kita tidak bisa set File object
    // Karena file sudah ada di server, kita set attachment ke true
    // TAPI schema mengharapkan File object, jadi kita perlu modifikasi

    if (displayData.value.attachment) {
        pdfUrl.value = getPdfUrl(displayData.value.attachment);

        // ✅ Untuk existing file, buat dummy File object atau set ke true
        // Cara 1: Set ke true (tapi schema akan error karena bukan File)
        // setFieldValue("attachment", true);

        // Cara 2: Buat dummy File object (RECOMMENDED)
        const dummyFile = new File(
            [],
            getFileName(displayData.value.attachment),
            {
                type: "application/pdf",
            },
        );
        setFieldValue("attachment", dummyFile);

        // Clear error
        setFieldError("attachment", undefined);
        uploadError.value = "";
    } else {
        setFieldValue("attachment", null);
    }

    // Isi form dengan data existing
    if (displayData.value.totalSpent) {
        const totalValue =
            typeof displayData.value.totalSpent === "number"
                ? displayData.value.totalSpent
                : parseFloat(displayData.value.totalSpent);
        setFieldValue("total_spent", isNaN(totalValue) ? null : totalValue);
        setFieldValue("report_notes", displayData.value.reportNotes || "");
    }
};

onMounted(() => {
    initializeData();
});
</script>

<template>
    <div class="max-h-[80vh] overflow-y-auto p-1 custom-scrollbar">
        <!-- Alert Status -->
        <NAlert
            v-if="isReportLocked || !isReportLocked"
            :type="getAlertType()"
            :title="getAlertTitle()"
            class="mb-4"
        >
            {{ getAlertMessage() }}
        </NAlert>

        <!-- Informasi Cash Advance -->
        <NCard
            :bordered="false"
            class="mb-5 rounded-xl bg-white shadow-sm"
            size="small"
        >
            <template #header>
                <div class="flex items-center gap-2">
                    <NIcon size="18" color="#18a058">
                        <DocumentOutline />
                    </NIcon>
                    <span class="font-semibold text-[15px] text-[#1f2d3d]"
                        >Informasi Pengajuan & Pencairan</span
                    >
                </div>
            </template>

            <NGrid :cols="2" :x-gap="16" :y-gap="12">
                <NGridItem>
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Peminjam
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            {{ displayData.name ?? "-" }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Departemen
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            <NTag :bordered="false" type="info" size="small">
                                {{ displayData.department ?? "-" }}
                            </NTag>
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Tanggal Dicairkan
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            {{ formatDate(displayData.disbursedDate ?? "-") }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Jumlah Dicairkan
                        </div>
                        <div class="text-base font-semibold text-[#18a058]">
                            {{ displayData.disbursedAmount }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem :span="2">
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Keperluan
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            {{ displayData.purpose || "-" }}
                        </div>
                    </div>
                </NGridItem>
            </NGrid>
        </NCard>

        <!-- Form Laporan (Belum Submit) -->
        <n-form
            @submit.prevent="submitForm"
            v-if="!isReportLocked && roleName === 'Employee'"
        >
            <NCard
                :bordered="false"
                class="mb-5 rounded-xl bg-white shadow-sm"
                size="small"
            >
                <template #header>
                    <div class="flex items-center gap-2">
                        <NIcon size="18" color="#18a058">
                            <ReceiptOutline />
                        </NIcon>
                        <span class="font-semibold text-[15px] text-[#1f2d3d]"
                            >Detail Pengeluaran</span
                        >
                    </div>
                </template>

                <n-form-item
                    label="Total Pengeluaran"
                    :validation-status="errors.total_spent ? 'error' : null"
                    :feedback="errors.total_spent"
                    required
                >
                    <NInputNumber
                        v-model:value="total_spent"
                        placeholder="Masukkan total pengeluaran"
                        :show-button="false"
                        :format="formatNumber"
                        :parse="parseNumber"
                        :min="1"
                        class="w-full text-right"
                    >
                        <template #prefix>
                            <span class="text-gray-500">Rp</span>
                        </template>
                    </NInputNumber>
                </n-form-item>

                <n-form-item
                    label="Catatan Pengeluaran"
                    :validation-status="errors.report_notes ? 'error' : null"
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
                    />
                </n-form-item>
            </NCard>

            <!-- Upload Dokumen Bukti -->
            <NCard
                :bordered="false"
                class="mb-5 rounded-xl bg-white shadow-sm"
                size="small"
            >
                <template #header>
                    <div class="flex items-center gap-2">
                        <NIcon size="18" color="#18a058">
                            <CloudUploadOutline />
                        </NIcon>
                        <span class="font-semibold text-[15px] text-[#1f2d3d]"
                            >Dokumen Laporan</span
                        >
                        <span class="text-xs text-[#e53935] ml-1"
                            >(Wajib diisi)</span
                        >
                    </div>
                </template>

                <NAlert
                    v-if="errors?.attachment || uploadError"
                    type="error"
                    closable
                    @close="clearError"
                    class="mb-4"
                >
                    <template #icon>
                        <NIcon><AlertCircleOutline /></NIcon>
                    </template>
                    {{ errors?.attachment || uploadError }}
                </NAlert>

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
                            <div class="flex flex-col items-center gap-2 py-8">
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

                <div v-else class="w-full">
                    <div
                        class="flex items-center gap-3 p-3 bg-[#fafafc] rounded-lg mb-4"
                    >
                        <NIcon size="24" color="#18a058">
                            <DocumentTextOutline />
                        </NIcon>
                        <div class="flex-1">
                            <div class="text-[13px] font-medium text-[#1f2d3d]">
                                {{ fileList[0]?.name || "Bukti Laporan.pdf" }}
                            </div>
                            <div class="text-[11px] text-[#8a8f99] mt-0.5">
                                {{
                                    fileList[0]?.file?.size
                                        ? (
                                              fileList[0].file.size / 1024
                                          ).toFixed(2) + " KB"
                                        : "PDF Document"
                                }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <NButton
                                size="small"
                                quaternary
                                @click="removeFile"
                            >
                                <template #icon>
                                    <NIcon><TrashOutline /></NIcon>
                                </template>
                            </NButton>
                        </div>
                    </div>
                </div>
            </NCard>

            <!-- Action Buttons -->
            <div
                class="flex justify-end gap-3 mt-5 pt-4 border-t border-gray-100"
            >
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

        <!-- View Mode (Setelah Submit) -->
        <div v-else>
            <!-- Ringkasan Laporan -->
            <NCard
                :bordered="false"
                class="mb-5 rounded-xl bg-white shadow-sm"
                size="small"
            >
                <template #header>
                    <div class="flex items-center gap-2">
                        <NIcon size="18" color="#18a058">
                            <ReceiptOutline />
                        </NIcon>
                        <span class="font-semibold text-[15px] text-[#1f2d3d]"
                            >Ringkasan Laporan</span
                        >
                    </div>
                </template>

                <NGrid :cols="2" :x-gap="16" :y-gap="12">
                    <NGridItem>
                        <div class="py-1">
                            <div
                                class="text-sm text-[#8a8f99] mb-1 tracking-wide"
                            >
                                Total Pengeluaran
                            </div>
                            <div class="text-base font-semibold text-[#18a058]">
                                {{ formatRupiah(displayData.totalSpent) }}
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem>
                        <div class="py-1">
                            <div
                                class="text-sm text-[#8a8f99] mb-1 tracking-wide"
                            >
                                Sisa Dana
                            </div>
                            <div
                                class="text-base font-semibold"
                                :class="getSisaDanaClass()"
                            >
                                {{ formatRupiah(getSisaDana()) }}
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem :span="2">
                        <div class="p-4 bg-slate-50 rounded-lg">
                            <div
                                class="text-sm text-[#8a8f99] mb-1 tracking-wide"
                            >
                                Catatan Pengeluaran
                            </div>
                            <div class="text-sm font-medium text-[#1f2d3d]">
                                {{ displayData.reportNotes || "-" }}
                            </div>
                        </div>
                    </NGridItem>
                </NGrid>
            </NCard>

            <!-- Review Finance Section -->
            <NCard
                v-if="displayData.reportStatus !== 'not_submitted'"
                :bordered="false"
                class="mb-5 rounded-xl bg-white shadow-sm"
                size="small"
            >
                <template #header>
                    <div class="flex items-center gap-2">
                        <NIcon size="18" color="#18a058">
                            <PeopleOutline />
                        </NIcon>
                        <span class="font-semibold text-[15px] text-[#1f2d3d]"
                            >Review Finance</span
                        >
                    </div>
                </template>

                <div
                    class="mt-4 p-3 bg-[#fafafc] rounded-lg"
                    v-if="displayData.financeNotes"
                >
                    <div class="text-[13px] font-semibold text-[#4a5568] mb-2">
                        Catatan Finance
                    </div>
                    <div class="text-[13px] text-[#1f2d3d] leading-relaxed">
                        {{ displayData.financeNotes }}
                    </div>
                </div>

                <!-- Form Review untuk Finance -->
                <div
                    v-if="
                        roleName === 'Finance' &&
                        displayData.reportStatus === 'submitted'
                    "
                    class="mt-5"
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

                    <div class="flex justify-end gap-3 mt-4">
                        <NButton
                            type="error"
                            @click="rejectReport"
                            :loading="reviewLoading"
                            :disabled="reviewLoading"
                        >
                            <template #icon>
                                <NIcon><CloseCircleOutline /></NIcon>
                            </template>
                            Tolak Laporan
                        </NButton>

                        <NButton
                            type="success"
                            @click="approveReport"
                            :loading="reviewLoading"
                            :disabled="reviewLoading"
                        >
                            <template #icon>
                                <NIcon><CheckmarkCircleOutline /></NIcon>
                            </template>
                            Setujui Laporan
                        </NButton>
                    </div>
                </div>
            </NCard>

            <!-- Dokumen Bukti -->
            <NCard
                v-if="displayData.attachment"
                :bordered="false"
                class="mb-5 rounded-xl bg-white shadow-sm overflow-hidden"
                size="small"
            >
                <template #header>
                    <div class="flex items-center gap-2">
                        <NIcon size="20" color="#18a058">
                            <DocumentOutline />
                        </NIcon>
                        <span class="font-semibold text-gray-700">
                            Dokumen Laporan
                        </span>
                    </div>
                </template>

                <div class="flex items-center justify-between gap-4 p-2">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="flex-shrink-0">
                            <NIcon size="40" color="#18a058">
                                <DocumentTextOutline />
                            </NIcon>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div
                                class="text-sm font-medium text-gray-800 truncate"
                            >
                                {{ getFileName(displayData.attachment) }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                File Attachment
                            </div>
                        </div>
                    </div>

                    <NButton
                        type="primary"
                        size="medium"
                        ghost
                        @click="openPdfModal"
                        class="flex-shrink-0"
                    >
                        <template #icon>
                            <NIcon><EyeOutline /></NIcon>
                        </template>
                        Lihat Dokumen
                    </NButton>
                </div>
            </NCard>
        </div>

        <!-- Modal PDF Viewer -->
        <NModal
            v-model:show="showPdfModal"
            preset="card"
            title="Dokumen Bukti"
            class="!w-[90%] !max-w-[1200px]"
            :closable="true"
        >
            <div class="w-full h-[70vh]">
                <iframe
                    :src="getPdfUrl(displayData.attachment)"
                    class="w-full h-full border-0"
                    frameborder="0"
                ></iframe>
            </div>
        </NModal>
    </div>
</template>

<style scoped>
/* Custom text color classes */
.text-danger {
    color: #e53935;
}

.text-warning {
    color: #ff9800;
}

.text-success {
    color: #18a058;
}

/* Custom scrollbar styling */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
