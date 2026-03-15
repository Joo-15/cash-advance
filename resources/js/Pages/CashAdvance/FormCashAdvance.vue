<script setup>
import { computed, ref, watch, watchEffect } from "vue";
import { router, usePage } from "@inertiajs/vue3";
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
    NModal,
    NSpace,
    useMessage,
} from "naive-ui";

// Utilities & Validations
import { cashAdvanceSchema } from "@/Validations/validationSchemas";
import { formatNumber, parseNumber, sleep } from "@/utils/helpers";

/*
| Props & Emits
*/
const props = defineProps({
    showModal: Boolean,
    dataEdit: Object,
});

const emit = defineEmits(["update:showModal", "updated"]);

/*
| State & Composables
*/
const message = useMessage();
const loadingButton = ref(false);
const isEditMode = computed(() => !!props.dataEdit?.id);

/*
| Form Setup (VeeValidate)
*/
const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(cashAdvanceSchema),
    initialValues: {
        id: null,
        tanggal: new Date().getTime(),
        keperluan: "",
        jumlah: null,
    },
});

/*
| Form Fields
*/
const [tanggal] = defineField("tanggal");
const [keperluan] = defineField("keperluan");
const [jumlah] = defineField("jumlah");

const submitForm = handleSubmit(async (values) => {
    const urlParams = new URLSearchParams(window.location.search);
    const currentFilters = {
        search: urlParams.get("search") || "",
        status: urlParams.get("status") || null,
        page: urlParams.get("page") || 1,
        per_page: urlParams.get("per_page") || 10,
        sort: urlParams.get("sort") || null,
        order: urlParams.get("order") || null,
    };

    loadingButton.value = true;
    await sleep(500);

    // Gabungkan values dengan currentFilters
    const submitData = {
        ...values,
        ...currentFilters, // ✅ Kirim semua filters
    };

    const options = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            closeModal();
            emit("updated");
        },
        onError: (errors) => {
            console.log("Error response:", errors);

            // Validation errors
            if (errors && typeof errors === "object") {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    message.error(firstError);
                } else {
                    message.error("Validasi gagal");
                }
            } else {
                message.error("Terjadi kesalahan server");
            }
        },
        onFinish: () => {
            loadingButton.value = false;
        },
    };

    if (isEditMode.value) {
        router.put(
            route("pengajuan-pinjaman.update", values.id),
            submitData,
            options,
        );
    } else {
        router.post(route("pengajuan-pinjaman.store"), submitData, options);
    }
});

/*
| Modal Handlers
*/
const closeModal = () => {
    emit("update:showModal", false);
};

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
                tanggal: val.tanggal ? new Date(val.tanggal).getTime() : null,
                jumlah: val.jumlah ? Number(val.jumlah) : null,
            });
        } else {
            resetForm();
        }
    },
    { immediate: true, deep: true },
);

// // Global flash message handler
// watchEffect(() => {
//     const flash = page.props.flash;

//     if (flash.success) message.success(flash.success);
//     if (flash.error) message.error(flash.error);
//     if (flash.warning) message.warning(flash.warning);

//     // Clear setelah ditampilkan (opsional)
//     page.props.flash = {};
// });
</script>

<template>
    <n-modal
        :show="showModal"
        preset="card"
        class="max-w-2xl w-full rounded-2xl p-2"
        :mask-closable="false"
        :title="isEditMode ? 'Edit Cash Advance' : 'Tambah Cash Advance'"
        @update:show="closeModal"
    >
        <n-form @submit.prevent="submitForm">
            <div>
                <!-- Tanggal Field -->
                <n-form-item
                    label="Tanggal"
                    :validation-status="errors.tanggal ? 'error' : null"
                    :feedback="errors.tanggal"
                    required
                >
                    <n-date-picker
                        v-model:value="tanggal"
                        type="date"
                        value-format="yyyy-MM-dd"
                        clearable
                        class="w-full"
                        placeholder="Pilih tanggal"
                    />
                </n-form-item>

                <!-- Keperluan Field -->
                <n-form-item
                    label="Keperluan"
                    :validation-status="errors.keperluan ? 'error' : null"
                    :feedback="errors.keperluan"
                    required
                >
                    <n-input
                        v-model:value="keperluan"
                        placeholder="Masukkan keperluan"
                        type="textarea"
                        :rows="3"
                    />
                </n-form-item>

                <!-- Jumlah Field -->
                <n-form-item
                    label="Jumlah"
                    :validation-status="errors.jumlah ? 'error' : null"
                    :feedback="errors.jumlah"
                    required
                >
                    <n-input-number
                        v-model:value="jumlah"
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
                    <n-button @click="closeModal"> Batal </n-button>

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
    </n-modal>
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
