<script setup>
import { computed, ref, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/yup";

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

import { cashAdvanceSchema } from "@/Validations/cashAdvanceSchema";

const props = defineProps({
    showModal: Boolean,
    dataEdit: Object,
});

const emit = defineEmits(["update:showModal", "updated"]);

const message = useMessage();

const isEditMode = computed(() => !!props.dataEdit?.id);
const loadingButton = ref(false);

/*
|--------------------------------------------------------------------------
| Vee Validate Form
|--------------------------------------------------------------------------
*/

const { handleSubmit, errors, defineField, setValues, resetForm } = useForm({
    validationSchema: toTypedSchema(cashAdvanceSchema),

    initialValues: {
        id: null,
        tanggal: new Date().getTime(),
        keperluan: "",
        jumlah: "",
    },
});

/*
|--------------------------------------------------------------------------
| Fields
|--------------------------------------------------------------------------
*/

const [tanggal] = defineField("tanggal");
const [keperluan] = defineField("keperluan");
const [jumlah] = defineField("jumlah");

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submitForm = handleSubmit((values) => {
    loadingButton.value = true;

    const options = {
        preserveScroll: true,
        preserveState: true,

        onSuccess: () => {
            message.success(
                isEditMode.value
                    ? "Data berhasil diupdate"
                    : "Data berhasil disimpan",
            );
            emit("updated");
            closeModal();
        },

        onError: () => {
            message.error("Validasi gagal");
        },
        onFinish: () => {
            loadingButton.value = false;
        },
    };

    if (isEditMode.value) {
        router.put(
            route("pengajuan-pinjaman.update", values.id),
            values,
            options,
        );
    } else {
        router.post(route("pengajuan-pinjaman.store"), values, options);
    }
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const closeModal = () => {
    emit("update:showModal", false);
};

/*
|--------------------------------------------------------------------------
| Watch Edit Data
|--------------------------------------------------------------------------
*/

watch(
    () => props.dataEdit,
    (val) => {
        if (val?.id) {
            setValues({
                ...val,
                tanggal: val.tanggal ? new Date(val.tanggal).getTime() : null,
            });
        } else {
            resetForm();
        }
    },
    { immediate: true },
);
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
            <div class="grid gap-4">
                <!-- Tanggal -->
                <n-form-item
                    label="Tanggal"
                    :validation-status="errors.tanggal ? 'error' : null"
                    :feedback="errors.tanggal"
                >
                    <n-date-picker
                        v-model:value="tanggal"
                        type="date"
                        clearable
                    />
                </n-form-item>

                <!-- Keperluan -->
                <n-form-item
                    label="Keperluan"
                    :validation-status="errors.keperluan ? 'error' : null"
                    :feedback="errors.keperluan"
                >
                    <n-input
                        v-model:value="keperluan"
                        placeholder="Masukkan keperluan"
                    />
                </n-form-item>

                <!-- Jumlah -->
                <n-form-item
                    label="Jumlah"
                    :validation-status="errors.jumlah ? 'error' : null"
                    :feedback="errors.jumlah"
                >
                    <n-input
                        v-model:value="jumlah"
                        placeholder="Masukkan jumlah"
                    />
                </n-form-item>
            </div>

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
