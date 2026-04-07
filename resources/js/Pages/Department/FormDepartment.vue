<script setup>
import { computed, watch } from "vue";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/yup";

// Naive UI Components
import { NButton, NForm, NFormItem, NInput, NSelect, NSpace } from "naive-ui";

// Utilities & Validations
import { departmentSchema } from "@/Validations/validationSchemas";

/*
| Props & Emits
*/
const props = defineProps({
    loading: Boolean,
    departmentsOptions: Array,
    rolesOptions: Array,
    dataEdit: Object,
    closeModal: Function,
    submit: Function,
});

const emit = defineEmits(["updated"]);

/*
Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(departmentSchema),
    initialValues: {
        id: null,
        name: "",
    },
});

/*
| Form Fields
*/

const [name] = defineField("name");

const isEditMode = computed(() => !!props.dataEdit?.id);

const submitForm = handleSubmit(async (values) => {
    await props.submit({
        values,
        method: isEditMode.value ? "put" : "post",
        url: isEditMode.value ? "departments.update" : "departments.store",
        id: isEditMode.value ? values.id : null,
        onSuccess: () => {
            props.closeModal();
            emit("updated");
            resetForm();
        },
    });
});

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
            <!-- Keperluan Field -->
            <n-form-item
                label="Nama Departemen"
                :validation-status="errors.name ? 'error' : null"
                :feedback="errors.name"
                required
            >
                <n-input
                    v-model:value="name"
                    placeholder="Masukkan nama departemen"
                />
            </n-form-item>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end border-t pt-4 px-2 mt-4">
            <n-space>
                <n-button @click="closeModal?.()"> Batal </n-button>

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
