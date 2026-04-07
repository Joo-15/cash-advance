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
    NInput,
    NInputNumber,
    NSelect,
    NSpace,
} from "naive-ui";

// Utilities & Validations
import { approvalStepSchema } from "@/Validations/validationSchemas";

/*
| Props & Emits
*/
const props = defineProps({
    loading: Boolean,
    showModal: Boolean,
    dataEdit: Object,
    rolesOptions: Array,
    closeModal: Function,
    submit: Function,
});

const emit = defineEmits(["update:showModal", "updated"]);

/*
| Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(approvalStepSchema),
    initialValues: {
        id: null,
        step_order: null,
    },
});

/*
| Form Fields
*/

const [step_order] = defineField("step_order");

const isEditMode = computed(() => !!props.dataEdit?.id);

const submitForm = handleSubmit(async (values) => {
    await props.submit({
        values,
        method: isEditMode.value ? "put" : "post",
        url: isEditMode.value
            ? "approval-steps.update"
            : "approval-steps.store",
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
            <n-form-item
                label="Level"
                :validation-status="errors.step_order ? 'error' : null"
                :feedback="errors.step_order"
                required
            >
                <n-input-number
                    v-model:value="step_order"
                    min="1"
                    placeholder="Pilih Urutan Persetujuan"
                    clearable
                />
            </n-form-item>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end border-t pt-4 px-2 mt-4">
            <n-space>
                <n-button @click="closeModal"> Batal </n-button>

                <n-button
                    type="primary"
                    attr-type="submit"
                    :loading="loading"
                    :disabled="loading"
                >
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

:deep(.n-input-number .n-input__prefix) {
    color: #6b7280;
    font-weight: 500;
    padding-right: 8px;
}

/* Modal Styling */
:deep(.n-modal) {
    transition: all 0.3s ease;
}

/* Responsive Adjustments */
@media (max-width: 640px) {
    :deep(.n-modal) {
        margin: 16px;
    }
}
</style>
