<script setup>
import { ref, reactive, computed, watch } from "vue";
import {
    useMessage,
    NCard,
    NForm,
    NFormItem,
    NInput,
    NButton,
    NTag,
    NProgress,
    NDatePicker,
    NInputNumber,
    NDivider,
} from "naive-ui";
import {
    formatDate,
    formatNumber,
    formatRupiah,
    parseNumber,
} from "@/utils/helpers";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/yup";
import { disbursement } from "@/Validations/validationSchemas";

// Props & Emits
const props = defineProps({
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
        amount: formatRupiah(item.amount),
        approveDate: lastApproval?.approved_at
            ? lastApproval.approved_at.split(" ")[0]
            : null,
        approveTime: lastApproval?.approved_at
            ? lastApproval.approved_at.split(" ")[1]
            : null,
    };
});

const emit = defineEmits(["update:showModal", "updated"]);

/*
| Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(disbursement),
    initialValues: {
        id: null,
        cash_advance_id: displayData.value.id,
        amount: null,
        disbursed_at: new Date().getTime(),
    },
});

const submitForm = handleSubmit(async (values) => {
    await props.submit({
        values,
        method: "post",
        url: "pencairan-dana.store",
        onSuccess: () => {
            props.closeModal();
            emit("updated");
            resetForm();
        },
    });
});

/*
| Form Fields
*/
const [amount] = defineField("amount");
const [disbursed_at] = defineField("disbursed_at");
</script>

<template>
    <div>
        <!-- Informasi Peminjam -->
        <div class="space-y-2 mb-6">
            <!-- PEMINJAM -->
            <div
                class="bg-white rounded-lg p-3 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0"
                    >
                        <svg
                            class="w-4 h-4 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            ></path>
                        </svg>
                    </div>
                    <div class="flex-1 flex items-center justify-between">
                        <div class="text-xs text-gray-400">Peminjam</div>
                        <div class="text-gray-800 text-sm font-medium">
                            {{ displayData.name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- DEPARTEMEN -->
            <div
                class="bg-white rounded-lg p-3 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0"
                    >
                        <svg
                            class="w-4 h-4 text-purple-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                            ></path>
                        </svg>
                    </div>
                    <div class="flex-1 flex items-center justify-between">
                        <div class="text-xs text-gray-400">Departemen</div>
                        <div class="text-gray-800 text-sm font-medium">
                            {{ displayData.department }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- TGL DISETUJUI -->
            <div
                class="bg-white rounded-lg p-3 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0"
                    >
                        <svg
                            class="w-4 h-4 text-green-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            ></path>
                        </svg>
                    </div>
                    <div class="flex-1 flex items-center justify-between">
                        <div class="text-xs text-gray-400">
                            Tanggal Disetujui
                        </div>
                        <div class="text-gray-800 text-sm font-medium">
                            {{ formatDate(displayData.approveDate) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- TUJUAN PINJAMAN -->
            <div
                class="bg-white rounded-lg p-3 hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0"
                    >
                        <svg
                            class="w-4 h-4 text-orange-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            ></path>
                        </svg>
                    </div>
                    <div class="flex-1 flex items-center justify-between">
                        <div class="text-xs text-gray-400">Tujuan Pinjaman</div>
                        <div
                            class="text-gray-800 text-sm font-medium text-right"
                        >
                            {{ displayData.purpose }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pinjaman Disetujui -->
        <div class="total-amount-section">
            <div class="total-label">Total Pinjaman Disetujui</div>
            <div class="total-amount">{{ displayData.amount }}</div>
        </div>

        <!-- Form Pencairan -->
        <div class="form-section">
            <n-divider title-placement="center" class="text-indigo-600">
                <b>JUMLAH PENCAIRAN</b>
            </n-divider>
            <n-form @submit.prevent="submitForm">
                <!-- Grid 2 kolom -->
                <div
                    style="
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 16px;
                    "
                >
                    <!-- Jumlah Pencairan -->
                    <n-form-item
                        size="large"
                        label="Jumlah dicairkan"
                        :validation-status="errors.amount ? 'error' : null"
                        :feedback="errors.amount"
                        required
                    >
                        <n-input-number
                            v-model:value="amount"
                            placeholder="Masukkan jumlah"
                            :show-button="false"
                            :format="formatNumber"
                            :parse="parseNumber"
                            :min="1"
                            class="w-full jumlah-input"
                        >
                            <template #prefix>
                                <span class="text-gray-500">Rp</span>
                            </template>
                        </n-input-number>
                    </n-form-item>

                    <!-- Tanggal Pencairan -->
                    <n-form-item
                        size="large"
                        label="Tanggal"
                        :validation-status="
                            errors.disbursed_at ? 'error' : null
                        "
                        :feedback="errors.disbursed_at"
                        required
                    >
                        <n-date-picker
                            v-model:value="disbursed_at"
                            value-format="timestamp"
                            type="date"
                            clearable
                            class="w-full"
                            placeholder="Pilih tanggal"
                        />
                    </n-form-item>
                </div>

                <!-- Buttons -->
                <div class="form-actions">
                    <n-button
                        type="primary"
                        size="large"
                        attr-type="submit"
                        :loading="loading"
                    >
                        Simpan
                    </n-button>
                </div>
            </n-form>
        </div>
    </div>
</template>

<style scoped>
:deep(.jumlah-input .n-input__input) {
    text-align: right;
}

.disbursement-card {
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.header {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}

.title {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    color: #1f2f3d;
}

.info-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
}

.info-row {
    display: flex;
    margin-bottom: 12px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-label {
    width: 140px;
    font-size: 12px;
    font-weight: 500;
    color: #8a8f99;
    letter-spacing: 0.5px;
}

.info-value {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: #1f2f3d;
}

.total-amount-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 10px;
    margin-bottom: 24px;
    text-align: center;
    position: relative;
}

.total-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.total-amount {
    font-size: 28px;
    font-weight: 700;
    color: white;
    margin-bottom: 12px;
}

.status-tag {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
}

.progress-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 24px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.progress-title {
    font-size: 14px;
    font-weight: 500;
    color: #1f2f3d;
}

.progress-percentage {
    font-size: 14px;
    font-weight: 600;
    color: #10b981;
}

.progress-detail {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
    font-size: 13px;
    color: #8a8f99;
}

.form-section {
    margin-top: 16px;
}

.currency-prefix {
    color: #8a8f99;
    font-weight: 500;
}

.max-amount-hint {
    font-size: 12px;
    color: #8a8f99;
    margin-top: 4px;
}

.form-actions {
    display: flex;
    gap: 12px;
}

.form-actions button {
    flex: 1;
}
</style>
