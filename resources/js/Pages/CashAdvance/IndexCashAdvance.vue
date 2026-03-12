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
import { sleep } from "@/utils/helpers";

const page = usePage();
const props = defineProps({
    cashadvance: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statData: Object,
});

const { createColumns, resetSort, hasActiveSort, updateSort } =
    useTableColumns();

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
    loadingSort,
    filters,
    debouncedFetch,
    handlePageChange,
    handlePageSizeChange,
    handleSortChange: datatableHandleSortChange,
    handleClear: datatableHandleClear,
    fetchData,
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

const tableColumns = computed(() => createColumns(columnConfig, actions));

// Handlers
const handleSortChange = async (sortOptions) => {
    // Update sort di composable
    if (sortOptions?.field) {
        const order = sortOptions.order === "asc" ? "ascend" : "descend";
        updateSort(sortOptions.field, order);

        loadingSort.value = true;

        // await sleep(500);
        // loadingSort.value = false;
        debouncedFetch();
    } else {
        resetSort();
    }
    // Kirim ke InertiaDataTable
    datatableHandleSortChange(sortOptions);
};

const handleClear = () => {
    resetSort(); // Reset sort di composable
    datatableHandleClear(); // Clear filters di Inertia
};

const handleDownload = () => {
    console.log("Download Excel");
};

const handleResetSort = async () => {
    resetSort();
    filters.sort = null;
    filters.order = null;
    loadingSort.value = true;

    await sleep(500);
    loadingSort.value = false;

    fetchData();
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
                :columns="tableColumns"
                :data-ref="rows"
                :meta="cashadvance"
                :filters="filters"
                :status-options="STATUS_OPTIONS"
                :page-size="filters.pageSize"
                :loading-ref="
                    loadingSearch ||
                    loadingReset ||
                    loadingStatus ||
                    loadingSort
                "
                :has-active-sort-fn="hasActiveSort"
                :reset-sort-fn="handleResetSort"
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
