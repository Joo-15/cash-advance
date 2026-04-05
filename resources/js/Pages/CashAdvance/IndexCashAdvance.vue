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

// Props definition
const props = defineProps({
    cashadvance: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statData: Object,
});

const collapsed = inject("sidebarCollapsed");

// Animation states
const isPageLoaded = ref(false);
const animatedCards = ref(false);
const animatedFilters = ref(false);
const animatedTable = ref(false);

// Trigger animations on mount
onMounted(() => {
    setTimeout(() => {
        isPageLoaded.value = true;
    }, 100);
    setTimeout(() => {
        animatedCards.value = true;
    }, 0);
    setTimeout(() => {
        animatedFilters.value = true;
    }, 0);
    setTimeout(() => {
        animatedTable.value = true;
    }, 0);
});

// Watch for statData changes to re-animate cards
watch(
    () => props.statData,
    () => {
        animatedCards.value = false;
        setTimeout(() => {
            animatedCards.value = true;
        }, 50);
    },
);

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
const rows = computed(() =>
    props.cashadvance.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration

const columnConfig = computed(() => {
    const columns = [
        {
            title: "Tgl. Pengajuan",
            key: "request_date",
            type: "date",
            width: 120,
            sorter: true,
            visible: true,
        },
        {
            title: "Keperluan",
            key: "purpose",
            width: 200,
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
            width: 150,
            visible: true,
        },
        {
            title: "Status",
            key: "status",
            type: "status",
            width: 80,
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
                    title="Pengajuan Pinjaman"
                    :show-add="true"
                    :show-download="true"
                    @add="tambah('cash-advance', 'create')"
                    @download="handleDownload"
                />
            </div>
        </template>

        <template #statCards v-if="!collapsed">
            <div
                class="transform transition-all duration-300"
                :class="
                    animatedCards
                        ? 'translate-y-0 opacity-100 scale-100'
                        : 'translate-y-10 opacity-0 scale-95'
                "
            >
                <StatCards
                    :stats="CASH_ADVANCE_STATS"
                    :stat-data="statData"
                    class="transition-all duration-150"
                />
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
                    :show-status="true"
                    :status-options="STATUS_OPTIONS"
                    :loading-search="loadingSearch"
                    @update:search="filters.search = $event"
                    @update:status="filters.status = $event"
                    class="transition-all duration-150"
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
            </div>
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

/* Table row hover animation */
:deep(.n-data-table tr) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.n-data-table tr:hover) {
    background: linear-gradient(
        90deg,
        rgba(59, 130, 246, 0.05) 0%,
        rgba(147, 51, 234, 0.05) 100%
    );
    transform: translateX(4px);
}

/* Button hover animations */
:deep(.n-button) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

:deep(.n-button:hover) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

:deep(.n-button:active) {
    transform: translateY(0);
}

/* Ripple effect on buttons */
:deep(.n-button::after) {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition:
        width 0.4s,
        height 0.4s;
}

:deep(.n-button:active::after) {
    width: 100%;
    height: 100%;
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

/* Input focus animations */
:deep(.n-input) {
    transition: all 0.2s ease;
}

:deep(.n-input:focus-within) {
    transform: scale(1.02);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

/* Loading skeleton shimmer */
:deep(.n-skeleton) {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 1000px 100%;
    animation: shimmer 2s infinite;
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

/* Notification animations */
:deep(.n-notification) {
    animation: slideInRight 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
