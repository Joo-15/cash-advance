<script setup>
import { computed, watch } from "vue";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/yup";

// Naive UI Components
import { NButton, NForm, NFormItem, NInput, NSelect, NSpace } from "naive-ui";

// Utilities & Validations
import { userSchema } from "@/Validations/validationSchemas";

/*
| Props & Emits
*/
const props = defineProps({
    loading: Boolean,
    departmentsOptions: Array,
    dataEdit: Object,
    closeModal: Function,
    submit: Function,
});

const emit = defineEmits(["updated"]);

/*
Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(userSchema),
    initialValues: {
        id: null,
        name: "",
        email: "",
        password: null,
        department_id: null,
    },
});

/*
| Form Fields
*/
const [name] = defineField("name");
const [email] = defineField("email");
const [password] = defineField("password");
const [department_id] = defineField("department_id");

const isEditMode = computed(() => !!props.dataEdit?.id);

const submitForm = handleSubmit(async (values) => {
    await props.submit({
        values,
        method: isEditMode.value ? "put" : "post",
        url: isEditMode.value ? "users.update" : "users.store",
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
                label="Username"
                :validation-status="errors.name ? 'error' : null"
                :feedback="errors.name"
                required
            >
                <n-input v-model:value="name" placeholder="Masukkan user" />
            </n-form-item>
            <n-form-item
                label="Email"
                :validation-status="errors.email ? 'error' : null"
                :feedback="errors.email"
                required
            >
                <n-input v-model:value="email" placeholder="Masukkan email" />
            </n-form-item>
            <n-form-item
                label="Password"
                :validation-status="errors.password ? 'error' : null"
                :feedback="errors.password"
                required
            >
                <n-input
                    v-model:value="password"
                    placeholder="Masukkan Password"
                />
            </n-form-item>
            <n-form-item
                label="Department"
                :validation-status="errors.department_id ? 'error' : null"
                :feedback="errors.department_id"
                required
            >
                <n-select
                    v-model:value="department_id"
                    :options="departmentsOptions"
                    placeholder="Pilih Department"
                    clearable
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
