<script setup>
import { ref, defineProps, defineEmits } from "vue";
import {
    NButton,
    NDatePicker,
    NForm,
    NFormItem,
    NInput,
    NModal,
    NSpace,
    NSpin,
} from "naive-ui";
import { useEditCashAdvance } from "@/Composables/CashAdvance/useEditCashAdvance.js";
import { cashAdvanceRules } from "../../Validations/cashAdvanceRules";

/* =====================
 | Props (Inertia)
 ===================== */
const props = defineProps({
    show: Boolean, //show model
    dataEdit: {
        type: Object,
        default: () => ({}),
    },
});

/* =====================
 | Emits
 ===================== */
const emits = defineEmits(["update:show"]);

/* =====================
 | Local State
 ===================== */
const editFormRef = ref(null);
const loadingEdit = ref(false);

const rules = cashAdvanceRules;

const { form, loading, submit, showModal } = useEditCashAdvance({
    props,
    loadingEdit,
    emits,
    routeName: "pengajuan-pinjaman.update",
    formRef: editFormRef,
});
</script>

<template>
    <n-modal
        v-model:show="showModal"
        :mask-closable="false"
        preset="card"
        class="max-w-2xl w-full rounded-2xl p-2"
        title="Edit Pengajuan"
    >
        <n-spin :show="loadingEdit">
            <div>
                <n-form
                    :model="form"
                    :rules="rules"
                    ref="editFormRef"
                    size="medium"
                >
                    <div class="grid">
                        <n-form-item label="Tanggal" path="tanggal">
                            <n-date-picker
                                v-model:value="form.tanggal"
                                type="date"
                            />
                        </n-form-item>

                        <n-form-item label="Keperluan" path="keperluan">
                            <n-input
                                v-model:value="form.keperluan"
                                placeholder="Masukkan keperluan"
                                clearable
                            />
                        </n-form-item>

                        <n-form-item label="Jumlah" path="jumlah">
                            <n-input
                                v-model:value="form.jumlah"
                                placeholder="Masukkan jumlah"
                                clearable
                            />
                        </n-form-item>
                    </div>
                </n-form>

                <!-- footer manual -->
                <div class="flex justify-end border-t pt-4 px-2 mt-4">
                    <n-space>
                        <n-button
                            type="primary"
                            @click="submit"
                            :loading="loading"
                            :disabled="loading"
                        >
                            Simpan
                        </n-button>
                    </n-space>
                </div>
            </div>

            <template #description> Mengambil data... </template>
        </n-spin>
    </n-modal>
</template>
