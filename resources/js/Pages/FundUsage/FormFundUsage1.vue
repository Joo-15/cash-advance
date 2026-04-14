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
const dialog = useDialog();

// State
const fileList = ref([]);
const pdfUrl = ref(null);
const submitting = ref(false);
const expenseItems = ref([]);

// Form Data untuk Laporan
const reportForm = ref({
    total_spent: null,
    report_notes: "",
    difference: 0,
});

// Validasi rules
const rules = {
    total_spent: [
        {
            required: true,
            message: "Total pengeluaran wajib diisi",
            trigger: "blur",
        },
        {
            validator: (rule, value) => value > 0,
            message: "Total pengeluaran harus lebih dari 0",
            trigger: "blur",
        },
        {
            validator: (rule, value) => {
                const disbursedAmount =
                    selectedItem.value?.disbursement?.amount || 0;
                if (value > disbursedAmount) {
                    return false;
                }
                return true;
            },
            message:
                "Total pengeluaran tidak boleh melebihi dana yang dicairkan",
            trigger: "blur",
        },
    ],
};

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
    };
});

// Difference calculation
const difference = computed(() => {
    const amount = displayData.value.rawDisbursedAmount;
    const totalSpent = reportForm.value.total_spent;
    if (!totalSpent) return null;
    return amount - totalSpent;
});

const differenceText = computed(() => {
    if (difference.value === null) return "";
    if (difference.value > 0) {
        return `💰 Kelebihan dana: ${formatRupiah(difference.value)} (harus dikembalikan)`;
    } else if (difference.value < 0) {
        return `⚠️ Kekurangan dana: ${formatRupiah(Math.abs(difference.value))} (akan ditambahkan)`;
    }
    return "✅ Dana pas (tidak kurang/lebih)";
});

const differenceClass = computed(() => {
    if (difference.value === null) return "";
    if (difference.value > 0) return "success";
    if (difference.value < 0) return "error";
    return "info";
});

const expenseSubtotal = computed(() => {
    return expenseItems.value.reduce(
        (sum, item) => sum + (Number(item.amount) || 0),
        0,
    );
});

const canSubmit = computed(() => {
    return reportForm.value.total_spent > 0 && fileList.value.length > 0;
});

// Methods
const getPdfUrl = (attachment) => {
    if (!attachment) return null;
    return `/storage/${attachment}`;
};

const initializeData = () => {
    // Initialize form with existing data
    if (displayData.value.totalSpent) {
        reportForm.value.total_spent = displayData.value.totalSpent;
        reportForm.value.report_notes = displayData.value.reportNotes || "";
    } else {
        reportForm.value.total_spent = null;
        reportForm.value.report_notes = "";
    }

    // Initialize file list
    if (displayData.value.attachment) {
        pdfUrl.value = getPdfUrl(displayData.value.attachment);
    }
};

const addExpenseItem = () => {
    expenseItems.value.push({
        id: Date.now(),
        description: "",
        amount: null,
        date: new Date().toISOString().split("T")[0],
    });
};

const removeExpenseItem = (index) => {
    expenseItems.value.splice(index, 1);
};

const updateTotalFromItems = () => {
    if (expenseItems.value.length > 0) {
        reportForm.value.total_spent = expenseSubtotal.value;
    }
};

// Upload handlers
const beforeUpload = ({ file }) => {
    const maxSizeMB = 2;

    if (file.type !== "application/pdf") {
        message.error("File harus PDF");
        return false;
    }

    if (file.file.size / 1024 / 1024 > maxSizeMB) {
        message.error("Maksimal ukuran 2MB");
        return false;
    }

    fileList.value = [file];
    return false;
};

const handleChange = ({ fileList: newFileList }) => {
    fileList.value = newFileList.slice(-1);
};

const removeFile = () => {
    dialog.warning({
        title: "Konfirmasi",
        content: "Apakah Anda yakin ingin menghapus file ini?",
        positiveText: "Ya, Hapus",
        negativeText: "Batal",
        onPositiveClick: () => {
            fileList.value = [];
            pdfUrl.value = null;
            message.success("File berhasil dihapus");
        },
    });
};

// Submit laporan
const submitForm = () => {
    // Validasi
    if (!reportForm.value.total_spent) {
        message.error("Total pengeluaran wajib diisi");
        return;
    }

    if (reportForm.value.total_spent <= 0) {
        message.error("Total pengeluaran harus lebih dari 0");
        return;
    }

    if (reportForm.value.total_spent > displayData.value.rawDisbursedAmount) {
        message.error(
            "Total pengeluaran tidak boleh melebihi dana yang dicairkan",
        );
        return;
    }

    if (fileList.value.length === 0) {
        message.error("File bukti laporan wajib diupload");
        return;
    }

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
            formData.append("total_spent", reportForm.value.total_spent);
            formData.append(
                "report_notes",
                reportForm.value.report_notes || "",
            );
            formData.append(
                "expense_items",
                JSON.stringify(expenseItems.value),
            );

            if (fileList.value[0]?.file) {
                formData.append("files[]", fileList.value[0].file);
            }

            props.submit({
                values: formData,
                method: "post",
                url: "penggunaan-dana.submit-report",
                id: displayData.value.id,
                onSuccess: () => {
                    submitting.value = false;
                    message.success("Laporan berhasil disubmit");
                    props.closeModal();
                },
                onError: () => {
                    submitting.value = false;
                },
            });
        },
    });
};

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
            type="warning"
            title="Laporan Sudah Disubmit"
            class="mb-4"
        >
            Laporan ini sudah disubmit dan tidak dapat diubah lagi.
            {{
                displayData.reportStatus === "approved"
                    ? "Laporan telah disetujui oleh finance."
                    : "Menunggu review dari finance."
            }}
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
                <NGridItem>
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

                <NForm
                    ref="formRef"
                    :model="reportForm"
                    :rules="rules"
                    label-placement="top"
                >
                    <!-- Total Pengeluaran -->
                    <NFormItem
                        label="Total Pengeluaran"
                        name="total_spent"
                        required
                    >
                        <NInputNumber
                            v-model:value="reportForm.total_spent"
                            placeholder="Masukkan total pengeluaran"
                            :min="0"
                            :max="displayData.rawDisbursedAmount"
                            :step="1000"
                            style="width: 100%"
                            :disabled="isReportLocked"
                        >
                            <template #prefix>Rp</template>
                        </NInputNumber>

                        <!-- Difference Alert -->
                        <div
                            v-if="difference !== null"
                            class="difference-alert mt-2"
                        >
                            <NAlert
                                :type="differenceClass"
                                :show-icon="false"
                                class="difference-text"
                            >
                                {{ differenceText }}
                            </NAlert>
                        </div>
                    </NFormItem>

                    <!-- Rincian Pengeluaran -->
                    <!-- <div class="expense-items-section">
                        <div class="section-header">
                            <div class="section-title">
                                <NIcon size="16">
                                    <CalculatorOutline />
                                </NIcon>
                                <span>Rincian Pengeluaran</span>
                            </div>
                            <NButton
                                size="small"
                                quaternary
                                @click="addExpenseItem"
                            >
                                <template #icon>
                                    <NIcon><AddOutline /></NIcon>
                                </template>
                                Tambah Item
                            </NButton>
                        </div>

                        <div
                            v-if="expenseItems.length === 0"
                            class="empty-expense"
                        >
                            <NText depth="3" class="text-center">
                                Belum ada rincian. Klik "Tambah Item" untuk
                                menambahkan.
                            </NText>
                        </div>

                        <div v-else class="expense-items-list">
                            <div
                                v-for="(item, index) in expenseItems"
                                :key="item.id"
                                class="expense-item"
                            >
                                <div class="expense-item-row">
                                    <div class="expense-description">
                                        <NInput
                                            v-model:value="item.description"
                                            placeholder="Deskripsi pengeluaran"
                                            size="small"
                                            :disabled="isReportLocked"
                                        />
                                    </div>
                                    <div class="expense-amount">
                                        <NInputNumber
                                            v-model:value="item.amount"
                                            placeholder="Jumlah"
                                            :min="0"
                                            :step="10000"
                                            size="small"
                                            style="width: 150px"
                                            :disabled="isReportLocked"
                                            @update:value="updateTotalFromItems"
                                        >
                                            <template #prefix>Rp</template>
                                        </NInputNumber>
                                    </div>
                                    <div class="expense-date">
                                        <NInput
                                            v-model:value="item.date"
                                            type="date"
                                            size="small"
                                            style="width: 130px"
                                            :disabled="isReportLocked"
                                        />
                                    </div>
                                    <div class="expense-action">
                                        <NButton
                                            size="tiny"
                                            quaternary
                                            type="error"
                                            @click="removeExpenseItem(index)"
                                            :disabled="isReportLocked"
                                        >
                                            <template #icon>
                                                <NIcon><TrashOutline /></NIcon>
                                            </template>
                                        </NButton>
                                    </div>
                                </div>
                            </div>

                            <div class="expense-subtotal">
                                <span>Subtotal:</span>
                                <strong>{{
                                    formatRupiah(expenseSubtotal)
                                }}</strong>
                            </div>
                        </div>
                    </div> -->

                    <!-- Catatan -->
                    <NFormItem label="Catatan Pengeluaran" name="report_notes">
                        <NInput
                            v-model:value="reportForm.report_notes"
                            type="textarea"
                            rows="4"
                            placeholder="Jelaskan rincian penggunaan dana secara detail..."
                            :maxlength="1000"
                            show-count
                            :disabled="isReportLocked"
                        />
                    </NFormItem>
                </NForm>
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
                                <NText depth="3" size="small"
                                    >Klik atau tarik file PDF ke sini</NText
                                >
                                <NText depth="3" size="tiny"
                                    >Maksimal 2MB · PDF saja</NText
                                >
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

                    <!-- PDF Preview -->
                    <!-- <div v-if="pdfUrl || fileList[0]" class="pdf-preview">
                        <iframe
                            :src="
                                pdfUrl || URL.createObjectURL(fileList[0]?.file)
                            "
                            class="pdf-iframe"
                            frameborder="0"
                        ></iframe>
                    </div> -->
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
                            <div class="info-label">Selisih</div>
                            <div class="info-value" :class="differenceClass">
                                {{
                                    formatRupiah(
                                        displayData.rawDisbursedAmount -
                                            (displayData.totalSpent || 0),
                                    )
                                }}
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem :span="2">
                        <div class="info-item">
                            <div class="info-label">Catatan</div>
                            <div class="info-value">
                                {{ displayData.reportNotes || "-" }}
                            </div>
                        </div>
                    </NGridItem>
                    <NGridItem :span="2" v-if="displayData.financeNotes">
                        <div class="info-item">
                            <div class="info-label">Catatan Finance</div>
                            <div class="info-value">
                                {{ displayData.financeNotes }}
                            </div>
                        </div>
                    </NGridItem>
                </NGrid>
            </NCard>

            <!-- PDF Preview untuk view mode -->
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
                <iframe
                    :src="getPdfUrl(displayData.attachment)"
                    class="pdf-iframe"
                    frameborder="0"
                ></iframe>
            </NCard>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <NButton @click="closeModal">
                <template #icon>
                    <NIcon><CloseOutline /></NIcon>
                </template>
                {{ isReportLocked ? "Tutup" : "Batal" }}
            </NButton>

            <NButton
                v-if="!isReportLocked"
                type="primary"
                :loading="submitting"
                :disabled="!canSubmit || submitting"
                @click="submitForm"
            >
                <template #icon>
                    <NIcon><SaveOutline /></NIcon>
                </template>
                Submit Laporan
            </NButton>
        </div>
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
