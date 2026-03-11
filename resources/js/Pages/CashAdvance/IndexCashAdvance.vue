<!-- Pages/CashAdvance/IndexCashAdvance.vue -->
<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useInertiaDataTable } from "@/Composables/useInertiaDataTable";
import { useCrud } from "@/Composables/useCrud";
import FormCashAdvance from "./FormCashAdvance.vue";
import { STATUS_OPTIONS } from "@/Constants/status";
import { CASH_ADVANCE_STATS } from "@/Constants/cashAdvanceStats";
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import PagePengajuanBaru from "@/Components/Templates/PagePengajuanBaru.vue";
import { useTableColumns } from "@/Composables/useTableColumns";

const page = usePage();
const props = defineProps({
    cashadvance: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statData: Object,
});

// Table Columns Composable
const { hasActiveSort, resetSort } = useTableColumns();

// ✅ Buat computed value untuk hasActiveSort (fungsi → boolean)
const hasActiveSortValue = computed(() => hasActiveSort());

// CRUD Operations
const { modal, selectedRow, tambah, edit, hapus, refresh } = useCrud({
    routePrefix: "pengajuan-pinjaman",
});

// DataTable
const {
    loadingSearch,
    loadingReset,
    loadingStatus,
    filters,
    handlePageChange,
    handlePageSizeChange,
    handleSortChange: datatableHandleSortChange,
    handleClear: datatableHandleClear,
} = useInertiaDataTable({
    route: route("pengajuan-pinjaman.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.cashadvance.per_page ?? 10),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["cashadvance"],
});

// Table Data
const rows = computed(() =>
    props.cashadvance.data.map((row) => ({ ...row, detail: true })),
);

// Column Configuration
const columnConfig = [
    {
        title: "Tanggal",
        key: "tanggal",
        type: "date",
        width: 120,
        sorter: true,
    },
    {
        title: "Keperluan",
        key: "keperluan",
        width: 200,
        ellipsis: { tooltip: true },
    },
    {
        title: "Jumlah",
        key: "jumlah",
        type: "currency",
        currency: "IDR",
        align: "right",
        sorter: true,
        width: 150,
    },
    {
        title: "Status",
        key: "status",
        type: "status",
        width: 80,
        align: "center",
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
    },
];

const actions = {
    onEdit: edit,
    onDelete: hapus,
};

// Handlers
const handleSortChange = (sortOptions) => {
    datatableHandleSortChange(sortOptions);
};

const handleClear = () => {
    datatableHandleClear();
};

const handleDownload = () => {
    // Implement download logic
    console.log("Download Excel");
};

const handleResetSort = () => {
    console.log(
        "🟢 Index - handleResetSort DIPANGGIL!",
        new Date().toISOString(),
    );
    console.log("🟢 Index - memanggil resetSort()");
    resetSort();
    console.log("🟢 Index - resetSort() selesai dipanggil");
};
</script>

<template>
    <PagePengajuanBaru
        :title="page.props.pageHeader ?? 'Cash Advance'"
        :stats="CASH_ADVANCE_STATS"
        :stat-data="statData"
        :filters="filters"
        :status-options="STATUS_OPTIONS"
        :loading-search="loadingSearch"
        :loading-reset="loadingReset"
        :has-active-sort="hasActiveSortValue"
        add-button-text="Ajukan Pinjaman"
        @update:search="filters.search = $event"
        @update:status="filters.status = $event"
        @clear="handleClear"
        @add="tambah"
        @download="handleDownload"
        @reset-sort="handleResetSort"
    >
        <template #table>
            <BaseTable
                :columns="columnConfig"
                :data-ref="rows"
                :meta="cashadvance"
                :filters="filters"
                :actions="actions"
                :status-options="STATUS_OPTIONS"
                :page-size="filters.pageSize"
                :loading-ref="loadingSearch || loadingReset || loadingStatus"
                @update:page="handlePageChange"
                @update:pageSize="handlePageSizeChange"
                @update:sorter="handleSortChange"
                @clear-filter="handleClear"
            />
        </template>
    </PagePengajuanBaru>

    <FormCashAdvance
        v-model:show="modal"
        :data-edit="selectedRow"
        @updated="refresh"
    />
</template>
