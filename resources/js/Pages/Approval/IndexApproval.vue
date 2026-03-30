<script setup>
import { computed, onMounted, ref } from "vue";
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
import FormApproval from "./FormApproval.vue";
import { useAuth } from "@/Composables/useAuth";
import { formatDate } from "@/utils/helpers";
import { STATUS_OPTIONS } from "@/Constants/status";
import { useDepartment } from "@/Composables/useCollection";

// Props definition
const props = defineProps({
    approval: Object,
    filters: { type: Object, default: () => ({}) },
});

// Composables initialization
const page = usePage();
// Refs
const formRef = ref(null);

const { userName, departmentName, roleName } = useAuth();

const {
    loadingButton,
    modalForm,
    selectedRow,
    selectedApproval,
    approvalStep,
    tambah,
    edit,
    hapus,
    fetchDetail,
    refresh,
    submit,
} = useCrud({
    routePrefix: "approvals",
    routeDetail: "approvals",
    formRef,
});

// DataTable setup
const {
    loadingSearch,
    loadingTable,
    filters,
    handlePageChange,
    handlePageSizeChange,
    handleSortChange,
    handleClear,
    handleResetSort,

    // Column management functions
    createColumns,
    hasActiveSort,
} = useDataTable({
    route: route("approvals.index"),
    filters: {
        search: props.filters.search || "",
        department: props.filters.department || null,
        status: props.filters.status || null,
        pageSize: Number(props.approval.per_page ?? 10),
        page: Number(props.approval.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["approval"],
    debounceTime: 300, // Tambahkan debounce time
    tableConfig: {
        currency: "IDR",
        dateFormat: "DD-MM-YYYY",
        actionSize: "small",
        ellipsisTooltip: true,
    },
});

const { departments } = useDepartment({});

console.log("department", departments);

// Table data transformation
const rows = computed(() =>
    props.approval.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration
const columnConfig = [
    {
        title: "Pemohon",
        key: "cash_advance.user.name",
        width: 150,
        sorter: false,
    },
    {
        title: "Departemen",
        key: "cash_advance.user.department.name",
        width: 150,
        sorter: false,
    },
    {
        title: "Tujuan",
        key: "cash_advance.purpose",
        // width: 150,
        sorter: false,
    },
    {
        title: "Jumlah",
        key: "cash_advance.amount",
        type: "currency",
        currency: "IDR",
        align: "right",
        sorter: true, // Aktifkan sorting
        width: 150,
    },
    {
        title: "Tgl. Pengajuan",
        key: "cash_advance.request_date",
        align: "center",
        type: "date",
        width: 150,
        sorter: false,
    },
    {
        title: "Status",
        key: "status",
        type: "status",
        width: 100,
        align: "center",
        sorter: true, // Aktifkan sorting
        statusMap: {
            pending: { type: "warning", label: "Pending" },
            approved: { type: "success", label: "Approved" },
            rejected: { type: "error", label: "Rejected" },
            default: { type: "default", label: "Unknown" },
        },
    },
    {
        title: "Aksi",
        key: "actions",
        type: "action",
        width: 120,
        fixed: "right",
        align: "center",
        actionConfig: {
            showEdit: false,
            showDelete: false,
            showView: false,
            showDetail: true,
            size: "small",
        },
        sorter: false,
    },
];

// Actions configuration
const actions = {
    onEdit: edit,
    onDelete: hapus,
    onDetail: (row) => fetchDetail("approval", "detail", row),
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));

// onMounted(() => {
//     fetchDepartments();
// });
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Tambah"
                :title="page.props.pageHeader ?? 'Cash Advance'"
            ></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :show-select="true"
                :department-options="departments"
                :status-options="STATUS_OPTIONS"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
                @update:department="filters.department = $event"
                @update:status="filters.status = $event"
            ></Filters>
        </template>
        <template #content>
            <BaseTable
                :columns="tableColumns"
                :data-ref="rows"
                :meta="approval"
                :filters="filters"
                :select-options="STATUS_OPTIONS"
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
                detail-title="Detail Persetujuan"
                :is-detail-mode="true"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <template #form="{ closeModal }">
                    <FormApproval
                        v-model:show-modal="modalForm"
                        :data-detail="selectedApproval"
                        :approval-step="approvalStep"
                        :loading="loadingButton"
                        :role-name="roleName"
                        :user-name="userName"
                        :department-name="departmentName"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
