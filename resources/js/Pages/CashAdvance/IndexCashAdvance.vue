<!-- Pages/CashAdvance/IndexCashAdvance.vue -->
<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

// Composables
import { useDataTable } from "@/Composables/useDataTable";
import { useCrud } from "@/Composables/useCrud";

// Constants
import { STATUS_OPTIONS } from "@/Constants/status";
import { CASH_ADVANCE_STATS } from "@/Constants/cashAdvanceStats";

// Components
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import StatCards from "@/Components/Page/StatCards.vue";
import Filters from "@/Components/Page/Filters.vue";
import ModalForm from "@/Components/Page/ModalForm.vue";
import FormCashAdvance from "./FormCashAdvance.vue";

// Props definition
const props = defineProps({
    cashadvance: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statData: Object,
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
    routePrefix: "pengajuan-pinjaman",
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
    route: route("pengajuan-pinjaman.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.cashadvance.per_page ?? 10),
        page: Number(props.cashadvance.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["cashadvance"],
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
    props.cashadvance.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration
const columnConfig = [
    {
        title: "Tanggal",
        key: "tanggal",
        type: "date",
        width: 120,
        sorter: true, // Aktifkan sorting
    },
    {
        title: "Keperluan",
        key: "keperluan",
        width: 200,
        ellipsis: { tooltip: true },
        sorter: true, // Tambahkan sorter jika perlu
    },
    {
        title: "Jumlah",
        key: "jumlah",
        type: "currency",
        currency: "IDR",
        align: "right",
        sorter: true, // Aktifkan sorting
        width: 150,
    },
    {
        title: "Status",
        key: "status",
        type: "status",
        width: 80,
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
            showEdit: true,
            showDelete: true,
            showView: true,
            size: "small",
        },
        sorter: false, // Aksi tidak perlu sorting
    },
];

// Actions configuration
const actions = {
    onEdit: edit,
    onDelete: hapus,
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));

const handleDownload = () => {
    console.log("Download Excel");
};
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Ajukan Pinjaman"
                :title="page.props.pageHeader ?? 'Cash Advance'"
                :show-add="true"
                :show-download="true"
                @add="tambah"
                @download="handleDownload"
            ></PageHeader>
        </template>
        <template #statCards>
            <StatCards
                :stats="CASH_ADVANCE_STATS"
                :stat-data="statData"
            ></StatCards>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :show-select="true"
                :select-options="STATUS_OPTIONS"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
                @update:status="filters.status = $event"
            ></Filters>
        </template>
        <template #content>
            <BaseTable
                :columns="tableColumns"
                :data-ref="rows"
                :meta="cashadvance"
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
                edit-title="Edit Pinjaman"
                create-title="Tambah Pinjaman"
                :data-edit="selectedRow"
            >
                <template #form="{ closeModal }">
                    <FormCashAdvance
                        v-model:show-modal="modalForm"
                        :loading="loadingButton"
                        :data-edit="selectedRow"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
