<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

// Composables
import { useDataTable } from "@/Composables/useDataTable";
import { useCrud } from "@/Composables/useCrud";

// Constants

// Components
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import Filters from "@/Components/Page/Filters.vue";
import ModalForm from "@/Components/Page/ModalForm.vue";
import FormApprovalStep from "./FormApprovalStepRole.vue";
import FormApprovalStepRole from "./FormApprovalStepRole.vue";

// Props definition
const props = defineProps({
    approvalStepRole: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    roles: Array,
    approvalStep: Array,
});

// Refs
const formRef = ref(null);

const {
    loadingButton,
    modalForm,
    currentFormType,
    modalMode,
    selectedRow,
    tambah,
    edit,
    hapus,
    refresh,
    submit,
} = useCrud({
    routePrefix: "approval-step-roles",
    formRef,
});

// DataTable setup
const {
    loadingSearch,
    loadingTable,
    filters,
    handlePageChange,
    handlePageSizeChange,
    handleSortChange, // Langsung dari composable
    handleClear,
    handleResetSort,

    // Column management functions
    createColumns,
    hasActiveSort,
} = useDataTable({
    route: route("approval-step-roles.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.approvalStepRole.per_page ?? 10),
        page: Number(props.approvalStepRole.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["approvalStepRole"],
    debounceTime: 300, // Tambahkan debounce time
    tableConfig: {
        currency: "IDR",
        dateFormat: "DD-MM-YYYY",
        actionSize: "small",
        ellipsisTooltip: true,
    },
});

// Table data transformation
const rows = computed(() => {
    const currentPage = props.approvalStepRole.current_page || 1;
    const perPage = props.approvalStepRole.per_page || 10;
    const startIndex = (currentPage - 1) * perPage;

    return props.approvalStepRole.data.map((row, idx) => ({
        ...row,
        rowNumber: startIndex + idx + 1,
        detail: true,
    }));
});

// Column configuration
const columnConfig = [
    {
        title: "No",
        key: "rowNumber", // Gunakan key dari data
        width: 80,
        align: "center",
        sorter: false,
    },
    {
        title: "Role",
        key: "role.name",
        sorter: false,
    },
    {
        title: "Persetujuan",
        key: "approval_step_id",
        sorter: false,
    },
    {
        title: "Aksi",
        key: "actions",
        type: "action",
        width: 120,
        fixed: "right",
        align: "center",
        actionConfig: {
            showEdit: true,
            showDelete: true,
            showView: true,
            size: "small",
        },
        sorter: false,
    },
];

// Actions configuration
const actions = {
    onEdit: (row) => edit("persetujuan", "edit", row),
    onDelete: hapus,
};

const modalTitle = computed(() => {
    if (modalMode.value === "edit") {
        return "Edit Persetujuan";
    }

    return "Tambah Persetujuan";
});

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Pengaturan Persetujuan"
                title="Pengaturan Persetujuan"
                :show-add="true"
                :show-download="false"
                @add="tambah('persetujuan', 'create')"
            ></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
            ></Filters>
        </template>
        <template #content>
            <BaseTable
                :columns="tableColumns"
                :data-ref="rows"
                :meta="approvalStepRole"
                :filters="filters"
                :page-size="filters.pageSize"
                :loading-ref="loadingSearch || loadingTable"
                :has-active-sort-fn="hasActiveSort"
                :reset-sort-fn="handleResetSort"
                @update:page="handlePageChange"
                @update:pageSize="handlePageSizeChange"
                @update:sorter="handleSortChange"
                @clear-filter="handleClear"
            />
            <ModalForm
                v-model:show-modal="modalForm"
                :title="modalTitle"
                :is-detail-mode="currentFormType === 'persetujuan'"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <template #form="{ closeModal }">
                    <FormApprovalStepRole
                        v-if="currentFormType === 'persetujuan'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :data-edit="selectedRow"
                        :roles-options="roles"
                        :approval-step="approvalStep"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
