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
import {
    approvalStepRoleSchema,
    approvalStepSchema,
} from "@/Validations/validationSchemas";

/*
| Props & Emits
*/
const props = defineProps({
    loading: Boolean,
    showModal: Boolean,
    dataEdit: Object,
    rolesOptions: Array,
    approvalStep: Array,
    closeModal: Function,
    submit: Function,
});

const emit = defineEmits(["update:showModal", "updated"]);

/*
| Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(approvalStepRoleSchema),
    initialValues: {
        id: null,
        role_id: null,
        approval_step_id: null,
    },
});

/*
| Form Fields
*/

const [role_id] = defineField("role_id");
const [approval_step_id] = defineField("approval_step_id");

const isEditMode = computed(() => !!props.dataEdit?.id);

const submitForm = handleSubmit(async (values) => {
    await props.submit({
        values,
        method: isEditMode.value ? "put" : "post",
        url: isEditMode.value
            ? "approval-step-roles.update"
            : "approval-step-roles.store",
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
                label="Role"
                :validation-status="errors.role_id ? 'error' : null"
                :feedback="errors.role_id"
                required
            >
                <n-select
                    v-model:value="role_id"
                    :options="rolesOptions"
                    placeholder="Pilih Role"
                    clearable
                />
            </n-form-item>
            <n-form-item
                label="Urutan"
                :validation-status="errors.approval_step_id ? 'error' : null"
                :feedback="errors.approval_step_id"
                required
            >
                <n-select
                    v-model:value="approval_step_id"
                    :options="approvalStep"
                    placeholder="Pilih Urutan"
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
