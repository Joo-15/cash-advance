<script setup>
import { watch, reactive, computed } from "vue";
import { NDatePicker, NForm, NFormItem, NInput } from "naive-ui";

import { validations } from "@/Validations";
import { useFormField, useFormHandler } from "@/Composables/useFormHandler";
import BaseFormModal from "@/Components/BaseFormModal.vue";

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    dataEdit: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["update:showModal", "updated"]);

// Ambil konfigurasi module
const moduleConfig = validations.cashAdvance;

// Setup form handler
const formHandler = useFormHandler({
    validationRules: moduleConfig.rules,
    initialValues: moduleConfig.initialValues,
    props,
    emit,
    routes: moduleConfig.routes,
    transformSubmitData: moduleConfig.transformSubmit,
    transformEditData: moduleConfig.transformEdit,
    messages: moduleConfig.messages,
});

// Field bindings
const tanggal = useFormField("tanggal", formHandler);
const keperluan = useFormField("keperluan", formHandler);
const jumlah = useFormField("jumlah", formHandler);
const keterangan = useFormField("keterangan", formHandler);

// Format helpers
const formatRupiah = (value) => {
    if (!value) return value;
    return new Intl.NumberFormat("id-ID").format(value);
};

const parseRupiah = (value) => {
    if (!value) return value;
    return Number(value.toString().replace(/[^0-9]/g, ""));
};

// Watch for dataEdit changes
watch(
    () => props.dataEdit,
    (newData) => {
        formHandler.setEditData(newData);
    },
    { immediate: true },
);

// Computed title
const modalTitle = computed(() =>
    formHandler.isEditMode.value ? "Edit Cash Advance" : "Tambah Cash Advance",
);
</script>

<template>
    <BaseFormModal
        :showModal="showModal"
        :dataEdit="dataEdit"
        :title="modalTitle"
        :loading="formHandler.loadingButton"
        @update:showModal="emit('update:showModal', $event)"
        @submit="formHandler.onSubmit"
    >
        <template #default="{ isEditMode }">
            <n-form
                :model="{
                    tanggal: tanggal.value,
                    keperluan: keperluan.value,
                    jumlah: jumlah.value,
                    keterangan: keterangan.value,
                }"
                size="medium"
            >
                <div class="grid gap-4">
                    <!-- Tanggal Field -->
                    <n-form-item
                        label="Tanggal"
                        path="tanggal"
                        :validation-status="tanggal.hasError ? 'error' : null"
                        :feedback="tanggal.error"
                    >
                        <n-date-picker
                            v-model:value="tanggal.value"
                            type="date"
                            clearable
                            :status="tanggal.hasError ? 'error' : null"
                            placeholder="Pilih tanggal"
                        />
                    </n-form-item>

                    <!-- Keperluan Field -->
                    <n-form-item
                        label="Keperluan"
                        path="keperluan"
                        :validation-status="keperluan.hasError ? 'error' : null"
                        :feedback="keperluan.error"
                    >
                        <n-input
                            v-model:value="keperluan.value"
                            placeholder="Masukkan keperluan"
                            clearable
                            :status="keperluan.hasError ? 'error' : null"
                            show-count
                            :maxlength="255"
                            type="textarea"
                            :autosize="{ minRows: 2, maxRows: 4 }"
                        />
                        <template #extra>
                            <span class="text-xs text-gray-400">
                                Minimal 5 karakter
                            </span>
                        </template>
                    </n-form-item>

                    <!-- Jumlah Field -->
                    <n-form-item
                        label="Jumlah"
                        path="jumlah"
                        :validation-status="jumlah.hasError ? 'error' : null"
                        :feedback="jumlah.error"
                    >
                        <n-input
                            :value="formatRupiah(jumlah.value)"
                            @update:value="
                                (val) => (jumlah.value = parseRupiah(val))
                            "
                            placeholder="Masukkan jumlah"
                            clearable
                            :status="jumlah.hasError ? 'error' : null"
                        >
                            <template #prefix>Rp</template>
                        </n-input>
                        <template #extra>
                            <span class="text-xs text-gray-400">
                                Minimal Rp 10.000, maksimal Rp 100.000.000
                            </span>
                        </template>
                    </n-form-item>

                    <!-- Keterangan Field -->
                    <n-form-item
                        label="Keterangan"
                        path="keterangan"
                        :validation-status="
                            keterangan.hasError ? 'error' : null
                        "
                        :feedback="keterangan.error"
                    >
                        <n-input
                            v-model:value="keterangan.value"
                            placeholder="Masukkan keterangan (opsional)"
                            clearable
                            :status="keterangan.hasError ? 'error' : null"
                            show-count
                            :maxlength="500"
                            type="textarea"
                            :autosize="{ minRows: 2, maxRows: 4 }"
                        />
                    </n-form-item>
                </div>
            </n-form>
        </template>
    </BaseFormModal>
</template>
