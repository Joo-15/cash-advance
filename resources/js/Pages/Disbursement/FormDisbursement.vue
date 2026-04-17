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
    NIcon,
    NGrid,
    NGridItem,
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
import { DocumentOutline } from "@vicons/ionicons5";

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
        requestDate: item?.request_date,
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
        <!-- Informasi Cash Advance -->
        <NCard
            :bordered="false"
            class="mb-5 rounded-xl bg-white shadow-md"
            size="small"
        >
            <template #header>
                <div class="flex items-center gap-2">
                    <n-icon size="18" color="#18a058">
                        <document-outline />
                    </n-icon>
                    <span class="font-semibold text-[15px] text-[#1f2d3d]"
                        >Informasi Pengajuan & Pencairan</span
                    >
                </div>
            </template>

            <n-grid :cols="2" :x-gap="16" :y-gap="12">
                <n-grid-item>
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Pemohon
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            {{ displayData.name ?? "-" }}
                        </div>
                    </div>
                </n-grid-item>
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
                            Tanggal Pengajuan
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            {{ formatDate(displayData.requestDate ?? "-") }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem>
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Jumlah Diajukan
                        </div>
                        <div class="text-base font-semibold text-[#18a058]">
                            {{ displayData.amount }}
                        </div>
                    </div>
                </NGridItem>
                <NGridItem :span="2">
                    <div class="py-1">
                        <div class="text-sm text-[#8a8f99] mb-1 tracking-wide">
                            Tujuan
                        </div>
                        <div class="text-sm font-medium text-[#1f2d3d]">
                            {{ displayData.purpose || "-" }}
                        </div>
                    </div>
                </NGridItem>
            </n-grid>
        </NCard>

        <!-- Total Pinjaman Disetujui -->
        <div class="total-amount-section">
            <div class="total-label">Total Pinjaman Disetujui</div>
            <div class="total-amount">{{ displayData.amount }}</div>
        </div>

        <!-- Form Pencairan -->
        <div class="form-section">
            <!-- <n-divider title-placement="center" class="text-indigo-600">
                <b>JUMLAH PENCAIRAN</b>
            </n-divider> -->
            <n-form @submit.prevent="submitForm">
                <!-- Grid 2 kolom -->
                <div class="grid grid-cols-1">
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
