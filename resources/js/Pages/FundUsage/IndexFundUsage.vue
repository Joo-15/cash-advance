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

// import FormFundUsage from "./FormFundUsage.vue";
import { Head } from "@inertiajs/vue3";
import FormFundUsage1 from "./FormFundUsage1.vue";
import { NTag } from "naive-ui";

// Props definition
const props = defineProps({
    fundUsage: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});
console.log("fundusage", props.fundUsage);
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
    routePrefix: "penggunaan-dana",
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
    route: route("penggunaan-dana.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.fundUsage.per_page ?? 10),
        page: Number(props.fundUsage.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["penggunaan-dana"],
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
    const currentPage = props.fundUsage.current_page || 1;
    const perPage = props.fundUsage.per_page || 10;
    const startIndex = (currentPage - 1) * perPage;

    return props.fundUsage.data.map((row, idx) => ({
        ...row,
        detail: true,
        rowNumber: startIndex + idx + 1,
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
        title: "Tanggal Pengajuan",
        key: "created_at",
        type: "date",
        width: 130,
        align: "center",
    },
    {
        title: "Tujuan",
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
        title: "Total Pengeluaran",
        key: "disbursement.total_spent",
        type: "currency",
        currency: "IDR",
        align: "right",
        width: 150,
    },
    {
        title: "Catatan",
        key: "disbursement.report_notes",
        align: "left",
        width: 200,
        ellipsis: true,
        render(row) {
            return row.disbursement?.report_notes || "-";
        },
    },
    // {
    //     title: "Laporan",
    //     key: "attachment",
    //     type: "attachment",
    //     align: "center",
    //     width: 200,
    // },
    {
        title: "Status",
        key: "disbursement.report_status",
        align: "center",
        width: 200,
        render(row) {
            const statusMap = {
                not_submitted: { label: "Belum Dikirim", type: "default" },
                submitted: { label: "Menunggu Review", type: "warning" },
                approved: { label: "Disetujui", type: "success" },
                rejected: { label: "Ditolak", type: "error" },
            };

            const status = row.disbursement?.report_status || "not_submitted";
            const statusInfo = statusMap[status];

            return h(
                NTag,
                {
                    type: statusInfo.type,
                    size: "small",
                    bordered: false,
                },
                {
                    default: () => statusInfo.label,
                },
            );
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
            showProses: (row) => row?.status === "disbursed",
        },
        sorter: false,
    },
];

// Actions configuration
const actions = {
    onProses: (row) => proses("fundUsage", "proses", row),
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));
</script>

<template>
    <Head title="Penggunaan Dana" />
    <Container>
        <template #header>
            <PageHeader title="Penggunaan Dana"></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :show-select="true"
                :select-options="STATUS_OPTIONS_PENCAIRAN"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
                @update:status="filters.status = $event"
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
                create-title="Upload Bukti"
                :is-detail-mode="false"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <template #form="{ closeModal }">
                    <FormFundUsage1
                        v-if="currentFormType === 'fundUsage'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :data-selected="selectedRow"
                        :role-name="roleName"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
