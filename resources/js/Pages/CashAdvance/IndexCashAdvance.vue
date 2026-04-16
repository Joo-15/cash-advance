<!-- Pages/CashAdvance/IndexCashAdvance.vue -->
<script setup>
import { computed, inject, ref, onMounted, watch } from "vue";

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
import FormApproval from "../Approval/FormApproval.vue";
import { useAuth } from "@/Composables/useAuth";
import { Head } from "@inertiajs/vue3";

// Props definition
const props = defineProps({
    cashadvance: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statData: Object,
});

const collapsed = inject("sidebarCollapsed");

// Refs
const formRef = ref(null);

const {
    userName,
    userAvatar,
    greeting,
    fullNameWithTitle,
    departmentName,
    roleName,
    isAdmin,
    isSupervisor,
    isEmployee,
    hasRole,
    inDepartmentName,
} = useAuth();

const {
    loadingButton,
    modalForm,
    currentFormType,
    modalMode,
    selectedRow,
    selectedApproval,
    approvalStep,
    tambah,
    edit,
    hapus,
    fetchDetail,
    printReceipt,
    refresh,
    submit,
} = useCrud({
    routePrefix: "pengajuan-pinjaman",
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
    route: route("pengajuan-pinjaman.index"),
    filters: {
        search: props.filters.search || "",
        department: props.filters.department
            ? Number(props.filters.department)
            : null,
        status: props.filters.status || null,
        pageSize: Number(props.cashadvance.per_page ?? 5),
        page: Number(props.cashadvance.current_page ?? 1),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["cashadvance"],
    debounceTime: 300,
    tableConfig: {
        currency: "IDR",
        dateFormat: "DD-MM-YYYY",
        actionSize: "small",
        ellipsisTooltip: true,
    },
});

// Table data transformation
const rows = computed(() => {
    const currentPage = props.cashadvance.current_page || 1;
    const perPage = props.cashadvance.per_page || 10;
    const startIndex = (currentPage - 1) * perPage;

    return props.cashadvance.data.map((row, idx) => ({
        ...row,
        detail: true,
        rowNumber: startIndex + idx + 1,
    }));
});

// Column configuration

const columnConfig = computed(() => {
    const columns = [
        {
            title: "No",
            key: "rowNumber",
            width: 80,
            align: "center",
            sorter: false,
            visible: true,
        },
        {
            title: "Tgl. Pengajuan",
            key: "request_date",
            type: "date",
            width: 170,
            align: "center",
            sorter: true,
            visible: true,
        },
        {
            title: "Keperluan",
            key: "purpose",
            // width: 200,
            // ellipsis: { tooltip: true },
            sorter: true,
            visible: true,
        },
        {
            title: "Jumlah",
            key: "amount",
            type: "currency",
            currency: "IDR",
            align: "right",
            sorter: true,
            width: 200,
            visible: true,
        },
        {
            title: "Status",
            key: "status",
            type: "status",
            width: 150,
            align: "center",
            sorter: true,
            statusMap: {
                pending: { type: "warning", label: "Pending" },
                approved: { type: "success", label: "Approved" },
                disbursed: { type: "info", label: "Disbursed" },
                rejected: { type: "error", label: "Rejected" },
                default: { type: "default", label: "Unknown" },
            },
            visible: true,
        },
        {
            title: "Aksi",
            key: "actions",
            type: "action",
            width: 120,
            fixed: "right",
            align: "center",
            actionConfig: {
                showEdit: (row) => row?.approvals?.[0].status === "pending",
                showDelete: (row) => row?.approvals?.[0].status === "pending",
                showPrint: (row) => row?.approvals?.[0].status === "approved",
                showDetail: true,
            },
            sorter: false,
            // ✅ Setting visible berdasarkan role
            visible: ["Admin", "Super Admin", "Employee"].includes(
                roleName.value,
            ),
        },
    ];

    // Filter kolom yang visible
    return columns.filter((column) => column.visible !== false);
});

// Actions configuration
const actions = {
    onEdit: (row) => edit("cash-advance", "edit", row),
    onDelete: hapus,
    onPrint: (row) => printReceipt("cash-advance", "print", row),
    onDetail: (row) => fetchDetail("approval", "detail", row),
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig.value, actions));

const handleDownload = () => {
    console.log("Download Excel");
};

const modalTitle = computed(() => {
    if (currentFormType.value === "approval") {
        return "Detail Persetujuan";
    }

    if (modalMode.value === "edit") {
        return "Edit Pinjaman";
    }

    return "Tambah Pinjaman";
});
</script>

<template>
    <Head title="Pengajuan Baru" />
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Ajukan Pinjaman"
                title="Pengajuan Pinjaman"
                :show-add="true"
                :show-download="true"
                @add="tambah('cash-advance', 'create')"
                @download="handleDownload"
            />
        </template>
        <template #statCards>
            <StatCards
                :stats="CASH_ADVANCE_STATS"
                :stat-data="statData"
                class="transition-all duration-150"
            />
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :show-status="true"
                :status-options="STATUS_OPTIONS"
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
                class="transition-all duration-300"
            />
            <ModalForm
                v-model:show-modal="modalForm"
                :title="modalTitle"
                :is-detail-mode="currentFormType === 'approval'"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <template #form="{ closeModal }">
                    <FormCashAdvance
                        v-if="currentFormType === 'cash-advance'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :data-edit="selectedRow"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                    <FormApproval
                        v-else-if="currentFormType === 'approval'"
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

<style scoped>
/* Smooth page transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Staggered animations for stat cards */
.stat-card-enter-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

/* Card hover effects */
:deep(.n-card) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.n-card:hover) {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.2);
}

/* Tag animations */
:deep(.n-tag) {
    transition: all 0.2s ease;
}

:deep(.n-tag:hover) {
    transform: scale(1.05);
    filter: brightness(1.05);
}

/* Pagination animations */
:deep(.n-pagination .n-pagination-item) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.n-pagination .n-pagination-item:hover) {
    transform: scale(1.1);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

/* Select dropdown animation */
:deep(.n-select-menu) {
    animation: slideDown 0.2s ease-out;
    transform-origin: top;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

/* Modal animation */
:deep(.n-modal-mask) {
    animation: fadeIn 0.2s ease-out;
}

:deep(.n-modal-container) {
    animation: modalSlideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Tooltip animations */
:deep(.n-tooltip) {
    animation: fadeInUp 0.2s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    :deep(.n-data-table tr:hover) {
        transform: translateX(2px);
    }

    :deep(.n-button:hover) {
        transform: translateY(-1px);
    }

    :deep(.n-card:hover) {
        transform: translateY(-2px);
    }
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: all 0.2s ease;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

/* Loading overlay */
:deep(.n-loading-bar) {
    background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
    background-size: 200% 100%;
    animation: loadingBar 1.5s ease infinite;
}

@keyframes loadingBar {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>
