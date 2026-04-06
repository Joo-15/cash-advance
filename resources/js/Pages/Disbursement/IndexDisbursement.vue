<script setup>
import { computed, onMounted, ref } from "vue";

// Composables
import { useDataTable } from "@/Composables/useDataTable";
import { useCrud } from "@/Composables/useCrud";

// Constants
import { STATUS_OPTIONS, STATUS_OPTIONS_PENCAIRAN } from "@/Constants/status";
import { CASH_ADVANCE_STATS } from "@/Constants/cashAdvanceStats";

// Components
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import StatCards from "@/Components/Page/StatCards.vue";
import Filters from "@/Components/Page/Filters.vue";
import ModalForm from "@/Components/Page/ModalForm.vue";
import FormCashAdvance from "./FormDisbursement.vue";
import FormApproval from "../Approval/FormApproval.vue";
import { useAuth } from "@/Composables/useAuth";
import FormDisbursement from "./FormDisbursement.vue";

// Props definition
const props = defineProps({
    disbursement: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

// Refs
const formRef = ref(null);

// Animation states
const isPageLoaded = ref(false);
const animatedCards = ref(false);
const animatedFilters = ref(false);
const animatedTable = ref(false);

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
    routePrefix: "pencairan-dana",
    // routeDetail: "approvals",
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
    route: route("pencairan-dana.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.disbursement.per_page ?? 10),
        page: Number(props.disbursement.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["pencairan-dana"],
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
    props.disbursement.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration
const columnConfig = [
    {
        title: "Pemohon",
        key: "user.name",
        width: 120,
        // sorter: true,
    },
    {
        title: "Departement",
        key: "user.department.name",
        width: 200,
        ellipsis: { tooltip: true },
        sorter: false,
    },
    {
        title: "Tujuan",
        key: "purpose",
        width: 200,
        ellipsis: { tooltip: true },
        sorter: true, // Tambahkan sorter jika perlu
    },

    {
        title: "Jumlah Diminta",
        key: "amount",
        type: "currency",
        currency: "IDR",
        align: "right",
        sorter: true, // Aktifkan sorting
        width: 150,
    },
    {
        title: "Jumlah Dicairkan",
        key: "disbursement.amount",
        type: "currency",
        currency: "IDR",
        align: "right",
        width: 150,
    },
    {
        title: "Bukti",
        key: "attachment",
        type: "attachment",
        align: "center",
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
            disbursed: { type: "info", label: "Disbursed" },
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
            showDetail: false,
            showProses: true,
            size: "small",
        },
        sorter: false, // Aksi tidak perlu sorting
    },
];

// Actions configuration
const actions = {
    onProses: (row) => proses("disbursement", "proses", row),
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));

// Trigger animations on mount
onMounted(() => {
    setTimeout(() => {
        isPageLoaded.value = true;
    }, 100);
    setTimeout(() => {
        animatedFilters.value = true;
    }, 0);
    setTimeout(() => {
        animatedTable.value = true;
    }, 0);
});
</script>

<template>
    <Container>
        <template #header>
            <div
                class="transform transition-all duration-1000"
                :class="
                    isPageLoaded
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-[-20px] opacity-0'
                "
            >
                <PageHeader
                    add-button-text="Ajukan Pinjaman"
                    title="Pencairan Dana"
                    :show-add="false"
                    @add="tambah('cash-advance', 'create')"
                ></PageHeader>
            </div>
        </template>
        <template #filters>
            <div
                class="transform transition-all duration-500"
                :class="
                    animatedFilters
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0'
                "
            >
                <Filters
                    :filters="filters"
                    :show-search="true"
                    :show-select="true"
                    :select-options="STATUS_OPTIONS_PENCAIRAN"
                    :loading-search="loadingSearch"
                    @update:search="filters.search = $event"
                    @update:status="filters.status = $event"
                ></Filters>
            </div>
        </template>
        <template #content>
            <div
                class="transform transition-all duration-500"
                :class="
                    animatedTable
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-10 opacity-0'
                "
            >
                <BaseTable
                    :columns="tableColumns"
                    :data-ref="rows"
                    :meta="disbursement"
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
            </div>
            <ModalForm
                v-model:show-modal="modalForm"
                create-title="Pencairan Dana"
                :is-detail-mode="false"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <template #form="{ closeModal }">
                    <FormDisbursement
                        v-if="currentFormType === 'disbursement'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :data-selected="selectedRow"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
