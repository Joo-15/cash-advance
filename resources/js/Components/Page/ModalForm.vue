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
console.log(props.isDetailMode);
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
        class="max-w-2xl w-full rounded-2xl p-2"
        :mask-closable="false"
        :title="modalTitle"
        @update:show="closeModal"
    >
        <slot name="form" :close-modal="closeModal" />
    </n-modal>
</template>
