<script setup>
import { computed, h, onMounted, ref } from "vue";

// Composables
import { useDataTable } from "@/Composables/useDataTable";
import { useCrud } from "@/Composables/useCrud";

// Constants
import { STATUS_OPTIONS_PENCAIRAN } from "@/Constants/status";

// Components
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import Filters from "@/Components/Page/Filters.vue";
import ModalForm from "@/Components/Page/ModalForm.vue";

import { useAuth } from "@/Composables/useAuth";

import { Head } from "@inertiajs/vue3";
// import FormFundUsage1 from "./FormReport.vue";
import { NTag } from "naive-ui";

// Props definition
const props = defineProps({
    fundUsage: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

// Refs
const formRef = ref(null);

const { roleName } = useAuth();

const {
    loadingButton,
    modalForm,
    currentFormType,
    modalMode,
    selectedRow,
    tambah,
    edit,
    hapus,
    proses,
    refresh,
    submit,
} = useCrud({
    routePrefix: "laporan",
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
    route: route("laporan.index"),
    filters: {
        search: props.filters.search || "",
        dateRange: props.filters.dateRange || "",
        status: props.filters.status || null,
        pageSize: Number(props.fundUsage.per_page ?? 10),
        page: Number(props.fundUsage.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["fundUsage"],
    debounceTime: 300, // Tambahkan debounce time
    tableConfig: {
        currency: "IDR",
        dateFormat: "DD-MM-YYYY",
        actionSize: "small",
        ellipsisTooltip: true,
    },
});

// Table data transformation dengan remaining_amount
const rows = computed(() => {
    const currentPage = props.fundUsage.current_page || 1;
    const perPage = props.fundUsage.per_page || 10;
    const startIndex = (currentPage - 1) * perPage;

    return props.fundUsage.data.map((row, idx) => {
        // Hitung remaining_amount
        const disbursementAmount = parseFloat(row.disbursement?.amount) || 0;
        const totalSpent = parseFloat(row.disbursement?.total_spent) || 0;
        const remainingAmount = disbursementAmount - totalSpent;

        return {
            ...row,
            detail: true,
            rowNumber: startIndex + idx + 1,
            // Tambahkan remaining_amount
            remaining_amount: remainingAmount,
            // Tambahkan formatted jika perlu
            formatted_remaining: new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(remainingAmount),
        };
    });
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
        title: "Tanggal Pengajuan",
        key: "created_at",
        type: "date",
        width: 130,
        align: "center",
    },
    {
        title: "Departemen",
        key: "user.department.name",
        width: 200,
        ellipsis: { tooltip: true },
    },
    {
        title: "Deskripsi Penggunaan Dana",
        key: "purpose",
        width: 200,
        ellipsis: { tooltip: true },
    },
    {
        title: "Jumlah Pengajuan",
        key: "disbursement.amount",
        type: "currency",
        currency: "IDR",
        align: "right",
        width: 150,
    },
    {
        title: "Jumlah Dicairkan",
        key: "disbursement.total_spent",
        type: "currency",
        currency: "IDR",
        align: "right",
        width: 150,
    },
    {
        title: "Dikembalikan",
        key: "remaining_amount",
        type: "currency",
        currency: "IDR",
        align: "right",
        width: 150,
        sorter: true,
    },
    {
        title: "Status",
        key: "disbursement.report_status",
        type: "status",
        width: 130,
        align: "center",
        statusMap: {
            approved: { type: "success", label: "Selesai" },
            not_submitted: { type: "warning", label: "Belum dikirim" },
            submitted: { type: "error", label: "Menunggu verifikasi" },
        },
    },
    // {
    //     title: "Aksi",
    //     key: "actions",
    //     type: "action",
    //     width: 120,
    //     fixed: "right",
    //     align: "center",
    //     actionConfig: {
    //         showProses: (row) => row?.status === "disbursed",
    //     },
    //     sorter: false,
    // },
];

// Actions configuration
const actions = {
    onProses: (row) => proses("fundUsage", "proses", row),
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));
</script>

<template>
    <Head title="Laporan" />
    <Container>
        <template #header>
            <PageHeader title="Laporan Cash Advance"></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-date-range="true"
                :show-download="true"
                :select-options="STATUS_OPTIONS_PENCAIRAN"
                :loading-search="loadingSearch"
                @update:dateRange="filters.dateRange = $event"
                @update:search="filters.search = $event"
                @update:status="filters.status = $event"
                @update:download="filters.download = $event"
            ></Filters>
        </template>
        <template #content>
            <BaseTable
                :columns="tableColumns"
                :data-ref="rows"
                :meta="fundUsage"
                :filters="filters"
                :select-options="STATUS_OPTIONS_PENCAIRAN"
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
                create-title="Laporan Penggunaan Dana"
                :is-detail-mode="false"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <template #form="{ closeModal }">
                    <!-- <FormFundUsage1
                        v-if="currentFormType === 'fundUsage'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :data-selected="selectedRow"
                        :role-name="roleName"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    /> -->
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
