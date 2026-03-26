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

console.log("display data", displayData);

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
    console.log(values);
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

// watch(
//     () => props.dataSelected,
//     (val) => {
//         console.log("test", val);
//         if (val?.id) {
//             setValues({
//                 ...val,
//                 request_date: val.request_date
//                     ? new Date(val.request_date).getTime()
//                     : null,
//                 amount: val.amount ? Number(val.amount) : null,
//             });
//         } else {
//             resetForm();
//         }
//     },
//     { immediate: true, deep: true },
// );
</script>

<template>
    <div>
        <n-card class="disbursement-card">
            <!-- Informasi Peminjam -->
            <div class="info-section">
                <div class="info-row">
                    <div class="info-label">PEMINJAM</div>
                    <div class="info-value">{{ displayData.name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">DEPARTEMEN</div>
                    <div class="info-value">{{ displayData.department }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">TGL DISETUJUI</div>
                    <div class="info-value">
                        {{ formatDate(displayData.approveDate) }}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">TUJUAN PINJAMAN</div>
                    <div class="info-value">{{ displayData.purpose }}</div>
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
                            label="Jumlah"
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
                    <div class="form-actions mt-4">
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
        </n-card>
    </div>
</template>

<style scoped>
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
