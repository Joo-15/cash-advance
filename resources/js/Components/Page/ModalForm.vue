<script setup>
import { NModal } from "naive-ui";
import { computed } from "vue";
const props = defineProps({
    showModal: Boolean,
    dataEdit: Object,
    isDetailMode: Boolean,
    detailTitle: {
        type: String,
        default: "Detail",
    },
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

const modalTitle = computed(() => {
    // Prioritas 1: Mode detail
    if (props.isDetailMode) {
        return props.detailTitle;
    }

    // Prioritas 2: Mode edit
    if (isEditMode.value) {
        return props.editTitle;
    }

    // Prioritas 3: Mode create
    return props.createTitle;
});
</script>
<template>
    <n-modal
        :show="showModal"
        preset="card"
        class="w-full h-full lg:max-w-6xl lg:rounded-2xl px-4 my-5"
        :mask-closable="false"
        :title="modalTitle"
        @update:show="closeModal"
    >
        <slot name="form" :close-modal="closeModal" />
    </n-modal>
</template>
