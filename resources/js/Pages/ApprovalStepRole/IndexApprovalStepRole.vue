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

// Props definition
const props = defineProps({
    approvalStepRole: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    roles: Array,
});

// Composables initialization
const page = usePage();
// Refs
const formRef = ref(null);

const {
    loadingButton,
    modalForm,
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
const rows = computed(() =>
    props.approvalStepRole.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration
const columnConfig = [
    {
        title: "Role",
        key: "role.name",
        width: 120,
        sorter: false,
    },
    {
        title: "Persetujuan",
        key: "approval_step_id",
        width: 120,
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
    onEdit: edit,
    onDelete: hapus,
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Tambah"
                :title="page.props.pageHeader ?? 'Cash Advance'"
                :show-add="true"
                :show-download="true"
                @add="tambah"
                @download="handleDownload"
            ></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :show-select="false"
                :select-options="null"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
                @update:status="filters.status = $event"
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
                edit-title="Edit Urutan Persetujuan"
                create-title="Tambah Urutan Persetujuan"
                :data-edit="selectedRow"
            >
                <template #form="{ closeModal }">
                    <FormApprovalStep
                        v-model:show-modal="modalForm"
                        :loading="loadingButton"
                        :data-edit="selectedRow"
                        :roles-options="roles"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
