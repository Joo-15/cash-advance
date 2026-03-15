<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/yup";

// Naive UI Components
import {
    NButton,
    NForm,
    NFormItem,
    NInput,
    NSelect,
    NSpace,
    useMessage,
} from "naive-ui";

// Utilities & Validations
import { userSchema } from "@/Validations/validationSchemas";
import { sleep } from "@/utils/helpers";
import { useFormSubmit } from "@/Composables/useFormSubmit";

/*
| Props & Emits
*/
const props = defineProps({
    departmentsOptions: Array,
    dataEdit: Object,
    closeModal: Function,
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

// Form submit handler
const { loadingButton, submit } = useFormSubmit();

const isEditMode = computed(() => !!props.dataEdit?.id);

const submitForm = handleSubmit(async (values) => {
    await submit({
        values,
        method: isEditMode.value ? "put" : "post",
        url: isEditMode.value ? "users.update" : "users.store",
        id: isEditMode.value ? values.id : null,
        customMessage: isEditMode.value
            ? "User berhasil diperbarui"
            : "User berhasil ditambahkan",
        onSuccess: () => {
            props.closeModal();
            emit("updated");
            resetForm();
        },
    });
});

/*
|--------------------------------------------------------------------------
| Form Submission
|--------------------------------------------------------------------------
*/
// const submitForm = handleSubmit(async (values) => {
//     const urlParams = new URLSearchParams(window.location.search);
//     const currentFilters = {
//         search: urlParams.get("search") || "",
//         status: urlParams.get("status") || null,
//         page: urlParams.get("page") || 1,
//         per_page: urlParams.get("per_page") || 10,
//         sort: urlParams.get("sort") || null,
//         order: urlParams.get("order") || null,
//     };

//     loadingButton.value = true;
//     await sleep(500);

//     // Gabungkan values dengan currentFilters
//     const submitData = {
//         ...values,
//         ...currentFilters, // ✅ Kirim semua filters
//     };

//     const options = {
//         preserveScroll: true,
//         preserveState: true,
//         onSuccess: () => {
//             props.closeModal();
//             emit("updated");
//         },
//         onError: (errors) => {
//             console.log("Error response:", errors);

//             // Validation errors
//             if (errors && typeof errors === "object") {
//                 const firstError = Object.values(errors)[0];
//                 if (firstError) {
//                     message.error(firstError);
//                 } else {
//                     message.error("Validasi gagal");
//                 }
//             } else {
//                 message.error("Terjadi kesalahan server");
//             }
//         },
//         onFinish: () => {
//             loadingButton.value = false;
//         },
//     };

//     if (isEditMode.value) {
//         router.put(route("users.update", values.id), submitData, options);
//     } else {
//         router.post(route("users.store"), submitData, options);
//     }
// });

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
                    :loading="loadingButton"
                    :disabled="loadingButton"
                >
                    {{ isEditMode ? "Update" : "Simpan" }}
                </n-button>
            </n-space>
        </div>
    </n-form>
</template>
