<script setup>
import { ref, defineProps, defineEmits } from "vue";
import { NButton, NForm, NFormItem, NInput, NModal, NSpace } from "naive-ui";
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

const rules = cashAdvanceRules;

const { form, loading, submit, showModal } = useEditCashAdvance({
    props,
    emits,
    routeName: "pengajuan-pinjaman.update",
    formDefault: {
        keperluan: "",
        jumlah: 0,
        status: "",
    },
    formRef: editFormRef,
});

const resetForm = () => {
    form.value = { keperluan: "", jumlah: "" };
};
</script>
<template>
    <n-modal
        v-model:show="showModal"
        :mask-closable="false"
        preset="card"
        class="max-w-2xl w-full rounded-2xl p-2"
        title="Edit Pengajuan"
    >
        <div class="">
            <n-form
                :model="form"
                :rules="rules"
                ref="editFormRef"
                size="medium"
            >
                <div class="grid">
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
        </div>

        <template #footer>
            <div class="flex justify-end border-t pt-4 px-2">
                <n-space>
                    <n-button
                        type="primary"
                        @click="submit"
                        :loading="loading"
                        :disabled="loading"
                        size="medium"
                    >
                        Simpan
                    </n-button>
                </n-space>
            </div>
        </template>
    </n-modal>
</template>
