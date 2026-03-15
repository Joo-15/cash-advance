<script setup>
import { NModal } from "naive-ui";
import { computed } from "vue";
const props = defineProps({
    showModal: Boolean,
    dataEdit: Object,

    editTitle: {
        type: String,
        default: "Edit Data",
    },
    createTitle: {
        type: String,
        default: "Tambah Data",
    },
});

const emit = defineEmits(["update:showModal"]);

const closeModal = () => {
    emit("update:showModal", false);
};

const isEditMode = computed(() => !!props.dataEdit?.id);
const modalTitle = computed(() =>
    isEditMode.value ? props.editTitle : props.createTitle,
);
</script>
<template>
    <n-modal
        :show="showModal"
        preset="card"
        class="max-w-2xl w-full rounded-2xl p-2"
        :mask-closable="false"
        :title="modalTitle"
        @update:show="closeModal"
    >
        <slot name="form" :close-modal="closeModal" />
    </n-modal>
</template>
