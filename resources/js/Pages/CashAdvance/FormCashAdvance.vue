<script setup>
import { computed, ref, watch } from "vue";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/yup";

// Naive UI Components
import {
    NButton,
    NDatePicker,
    NForm,
    NFormItem,
    NIcon,
    NInput,
    NInputNumber,
    NModal,
    NSpace,
} from "naive-ui";

// Utilities & Validations
import { cashAdvanceSchema } from "@/Validations/validationSchemas";
import { formatNumber, parseNumber } from "@/utils/helpers";
import { CloseOutline, SaveOutline } from "@vicons/ionicons5";

/*
| Props & Emits
*/
const props = defineProps({
    loading: Boolean,
    showModal: Boolean,
    dataEdit: Object,
    closeModal: Function,
    submit: Function,
});

const emit = defineEmits(["update:showModal", "updated"]);

/*
| Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(cashAdvanceSchema),
    initialValues: {
        id: null,
        request_date: new Date().getTime(),
        purpose: "",
        amount: null,
    },
});

/*
| Form Fields
*/
const [request_date] = defineField("request_date");
const [purpose] = defineField("purpose");
const [amount] = defineField("amount");

const isEditMode = computed(() => !!props.dataEdit?.id);

const submitForm = handleSubmit(async (values) => {
    await props.submit({
        values,
        method: isEditMode.value ? "put" : "post",
        url: isEditMode.value
            ? "pengajuan-pinjaman.update"
            : "pengajuan-pinjaman.store",
        id: isEditMode.value ? values.id : null,
        onSuccess: () => {
            props.closeModal();
            emit("updated");
            resetForm();
        },
    });
});

defineExpose({
    resetForm,
});

/*
| Watchers
*/
watch(
    () => props.dataEdit,
    (val) => {
        if (val?.id) {
            setValues({
                ...val,
                request_date: val.request_date
                    ? new Date(val.request_date).getTime()
                    : null,
                amount: val.amount ? Number(val.amount) : null,
            });
        } else {
            resetForm();
        }
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <n-form @submit.prevent="submitForm">
        <div>
            <!-- Tanggal Field -->
            <n-form-item
                label="Tanggal"
                :validation-status="errors.request_date ? 'error' : null"
                :feedback="errors.request_date"
                required
            >
                <n-date-picker
                    v-model:value="request_date"
                    value-format="timestamp"
                    type="date"
                    clearable
                    class="w-full"
                    placeholder="Pilih tanggal"
                />
            </n-form-item>

            <!-- Keperluan Field -->
            <n-form-item
                label="Tujuan"
                :validation-status="errors.purpose ? 'error' : null"
                :feedback="errors.purpose"
                required
            >
                <n-input
                    v-model:value="purpose"
                    placeholder="Masukkan tujuan"
                    type="textarea"
                    :rows="3"
                />
            </n-form-item>

            <!-- Jumlah Field -->
            <n-form-item
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
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end border-t pt-4 px-2 mt-4">
            <n-space>
                <n-button @click="closeModal">
                    <template #icon>
                        <n-icon>
                            <close-outline />
                        </n-icon>
                    </template>
                    Batal
                </n-button>

                <n-button
                    type="primary"
                    attr-type="submit"
                    :loading="loading"
                    :disabled="loading"
                >
                    <template #icon>
                        <n-icon>
                            <SaveOutline />
                        </n-icon>
                    </template>
                    {{ isEditMode ? "Update" : "Simpan" }}
                </n-button>
            </n-space>
        </div>
    </n-form>
</template>

<style scoped>
/* Input Number Styling */
:deep(.jumlah-input .n-input__input) {
    text-align: right;
}
</style>
