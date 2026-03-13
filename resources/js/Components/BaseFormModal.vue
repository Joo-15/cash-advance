<script setup>
import { watch } from "vue";
import { NButton, NModal, NSpace } from "naive-ui";

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    dataEdit: {
        type: Object,
        default: null,
    },
    title: {
        type: String,
        default: "Form Modal",
    },
    loading: {
        type: Boolean,
        default: false,
    },
    width: {
        type: String,
        default: "max-w-2xl",
    },
});

const emit = defineEmits(["update:showModal", "submit", "close"]);

const closeModal = () => {
    emit("update:showModal", false);
    emit("close");
};

const onSubmit = () => {
    emit("submit");
};
</script>

<template>
    <n-modal
        :show="showModal"
        :mask-closable="false"
        preset="card"
        :class="`${width} w-full rounded-2xl p-2`"
        :title="title"
        @update:show="closeModal"
    >
        <form @submit.prevent="onSubmit">
            <!-- Slot untuk form fields -->
            <slot :dataEdit="dataEdit" :isEditMode="!!dataEdit?.id" />

            <!-- Form Actions -->
            <div class="flex justify-end border-t pt-4 px-2 mt-4">
                <n-space>
                    <n-button
                        type="secondary"
                        @click="closeModal"
                        :disabled="loading"
                    >
                        Batal
                    </n-button>
                    <n-button
                        type="primary"
                        attr-type="submit"
                        :loading="loading"
                        :disabled="loading"
                    >
                        <slot name="submitButton">
                            {{ dataEdit?.id ? "Update" : "Simpan" }}
                        </slot>
                    </n-button>
                </n-space>
            </div>
        </form>
    </n-modal>
</template>
