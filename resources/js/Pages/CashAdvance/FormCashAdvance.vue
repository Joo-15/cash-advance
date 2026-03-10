<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    NButton,
    NDatePicker,
    NForm,
    NFormItem,
    NInput,
    NModal,
    NSpace,
    useMessage,
} from "naive-ui";
import { sleep } from "@/utils/helpers";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    // Data untuk edit mode (optional)
    dataEdit: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:show", "updated"]);

// State untuk form

const message = useMessage();
const loadingButton = ref(false);

// Definisikan default values
const DEFAULT_FORM = {
    id: null,
    tanggal: new Date().getTime(),
    keperluan: "",
    jumlah: "",
};

const formData = ref({ ...DEFAULT_FORM });
const resetForm = () => {
    formData.value = { ...DEFAULT_FORM };
};

const formErrors = ref({});
// Helper untuk mendapatkan pesan error (SESUAIKAN DENGAN FORMAT ANDA)
const getError = (field) => {
    const error = formErrors.value[field];
    if (!error) return null;

    return error;
};

// Helper untuk cek apakah field error
const hasError = (field) => {
    return !!formErrors.value[field];
};

// Computed untuk mode form
const isEditMode = computed(() => !!props.dataEdit?.id);

// Watch untuk mengisi form saat edit
watch(
    () => props.dataEdit,
    (newData) => {
        if (newData && newData.id) {
            // Mode Edit: isi dengan data yang diedit
            formData.value = {
                ...newData,
                tanggal: newData.tanggal
                    ? new Date(newData.tanggal).getTime()
                    : null,
            };
        } else {
            // Mode Create: reset ke default
            resetForm();
        }
    },
    { immediate: true, deep: true },
);

// Watch untuk reset form saat modal ditutup
watch(
    () => props.show,
    (newVal) => {
        if (!newVal) {
            // Reset form hanya jika create mode
            resetForm();
            formErrors.value = {};
        }
    },
);

const submitForm = async () => {
    formErrors.value = {};
    loadingButton.value = true;
    await sleep(500);

    try {
        if (isEditMode.value) {
            // Mode Edit
            await router.put(
                route("pengajuan-pinjaman.update", formData.value.id),
                formData.value,
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: async () => {
                        message.success("Data berhasil diupdate");
                        emit("updated");
                        emit("update:show", false);
                    },
                    onError: async (errors) => {
                        formErrors.value = errors;

                        message.error(
                            "Validasi gagal. Periksa kembali form Anda",
                        );

                        setTimeout(() => {
                            const firstError =
                                document.querySelector(".has-error");
                            if (firstError) {
                                firstError.scrollIntoView({
                                    behavior: "smooth",
                                });
                            }
                        }, 100);
                    },
                    onFinish: () => {
                        loadingButton.value = false;
                    },
                },
            );
        } else {
            // Mode Create
            await router.post(
                route("pengajuan-pinjaman.store"),
                formData.value,
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: async () => {
                        message.success("Data berhasil disimpan");
                        emit("updated");
                        emit("update:show", false);
                    },
                    onError: async (errors) => {
                        formErrors.value = errors;

                        message.error(
                            "Validasi gagal. Periksa kembali form Anda",
                        );

                        // Scroll ke error pertama
                        setTimeout(() => {
                            const firstError =
                                document.querySelector(".has-error");
                            if (firstError) {
                                firstError.scrollIntoView({
                                    behavior: "smooth",
                                });
                            }
                        }, 100);
                    },
                    onFinish: () => {
                        loadingButton.value = false;
                    },
                },
            );
        }
    } catch (error) {
        console.error("Error:", error);
        message.error("Gagal menyimpan data");
    }
};

const closeModal = () => {
    emit("update:show", false);
};
</script>

<template>
    <n-modal
        :show="show"
        :mask-closable="false"
        preset="card"
        class="max-w-2xl w-full rounded-2xl p-2"
        :title="isEditMode ? 'Edit Cash Advance' : 'Tambah Cash Advance'"
        @update:show="closeModal"
    >
        <n-form
            :model="formData"
            ref="editFormRef"
            size="medium"
            @submit.prevent="submitForm"
        >
            <!-- Form fields -->
            <div class="grid">
                <n-form-item label="Tanggal" path="tanggal">
                    <NDatePicker v-model:value="formData.tanggal" type="date" />
                </n-form-item>

                <n-form-item
                    label="Keperluan"
                    path="keperluan"
                    :validation-status="hasError('keperluan') ? 'error' : null"
                    :feedback="getError('keperluan')"
                >
                    <n-input
                        v-model:value="formData.keperluan"
                        placeholder="Masukkan keperluan"
                        :status="hasError('keperluan') ? 'error' : null"
                        clearable
                    />
                </n-form-item>

                <n-form-item label="Jumlah" path="jumlah">
                    <n-input
                        v-model:value="formData.jumlah"
                        placeholder="Masukkan jumlah"
                        clearable
                    />
                </n-form-item>
            </div>
            <div class="flex justify-end border-t pt-4 px-2 mt-4">
                <NSpace>
                    <n-button type="secondary" @click="closeModal">
                        Batal
                    </n-button>
                    <n-button
                        type="primary"
                        attr-type="submit"
                        :loading="loadingButton"
                        :disabled="loadingButton"
                    >
                        {{ isEditMode ? "Update" : "Simpan" }}
                    </n-button>
                </NSpace>
            </div>
        </n-form>
    </n-modal>
</template>
