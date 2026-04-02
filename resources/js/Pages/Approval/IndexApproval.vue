<script setup>
import { computed, h, onMounted, ref } from "vue";
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
import { NTag, NTooltip } from "naive-ui";

// Props definition
const props = defineProps({
    approval: Object,
    filters: { type: Object, default: () => ({}) },
});

console.log("approval", props.approval.data);

// Composables initialization
const page = usePage();
// Refs
const formRef = ref(null);

const { userName, departmentName, roleName, roleId } = useAuth();

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
        department: props.filters.department
            ? Number(props.filters.department)
            : null,
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

const { loading: loadingDepartments, departments } = useDepartment({});

const departmentOptions = computed(() => departments.value || []);

// Table data transformation
const rows = computed(() =>
    props.approval.data.map((row) => ({ ...row, detail: true })),
);

// console.log("role", roleName.value);

// Column configuration
const columnConfig = computed(() => {
    const columns = [
        {
            title: "Pemohon",
            key: "user.name",
            width: 150,
            sorter: false,
            visible: true,
        },
        {
            title: "Departemen",
            key: "user.department.name",
            width: 150,
            sorter: false,
            visible: [
                "Admin",
                "Super Admin",
                "General Manager",
                "Manager Accounting",
                "Finance",
            ].includes(roleName.value),
        },
        {
            title: "Tujuan",
            key: "purpose",
            sorter: false,
            visible: true,
        },
        {
            title: "Jumlah",
            key: "amount",
            type: "currency",
            currency: "IDR",
            align: "right",
            sorter: false,
            width: 150,
            visible: true,
        },
        {
            title: "Tgl. Pengajuan",
            key: "request_date",
            align: "center",
            type: "date",
            width: 150,
            sorter: false,
            visible: true,
        },
        {
            title: "Status",
            key: "approvals.status",
            width: 200,
            align: "center",
            sorter: true,
            visible: true,
            render: (row) => {
                if (!row.approvals || row.approvals.length === 0) {
                    return h(
                        NTag,
                        {
                            type: "default",
                            size: "small",
                            round: true,
                            bordered: false,
                        },
                        { default: () => "No Data" },
                    );
                }

                // Urutkan approvals berdasarkan step_order
                const sortedApprovals = [...row.approvals].sort(
                    (a, b) =>
                        (a.approval_step?.step_order || 0) -
                        (b.approval_step?.step_order || 0),
                );

                // Dapatkan role user yang login
                const currentUserRoleId = roleId.value; // Sesuaikan dengan implementasi Anda
                const currentUserRoleName = roleName.value; // Sesuaikan dengan implementasi Anda

                // Cari step yang menjadi tanggung jawab user login
                const userStepIndex = sortedApprovals.findIndex((approval) => {
                    return approval.approval_step?.approval_step_roles?.some(
                        (stepRole) =>
                            String(stepRole.role_id) ===
                            String(currentUserRoleId),
                    );
                });

                // Cari approval yang pending (belum diproses)
                const currentPending = sortedApprovals.find(
                    (a) => a.status === "pending",
                );
                const currentPendingIndex = sortedApprovals.findIndex(
                    (a) => a.status === "pending",
                );
                const rejectedStep = sortedApprovals.find(
                    (a) => a.status === "rejected",
                );
                const allApproved = sortedApprovals.every(
                    (a) => a.status === "approved",
                );

                // Helper untuk mendapatkan nama role
                const getRoleNames = (approval) => {
                    if (!approval?.approval_step?.approval_step_roles)
                        return [];
                    return approval.approval_step.approval_step_roles.map(
                        (stepRole) => stepRole.role?.name,
                    );
                };

                const formatRoleNames = (roleNames) => {
                    if (!roleNames || roleNames.length === 0) return "Unknown";
                    if (roleNames.length === 1) return roleNames[0];
                    if (roleNames.length === 2)
                        return `${roleNames[0]} & ${roleNames[1]}`;
                    return `${roleNames.slice(0, -1).join(", ")}, & ${roleNames[roleNames.length - 1]}`;
                };

                // 1. Jika ada yang rejected
                if (rejectedStep) {
                    const roleNames = getRoleNames(rejectedStep);
                    const formattedRoles = formatRoleNames(roleNames);

                    return h(
                        NTooltip,
                        { placement: "top" },
                        {
                            trigger: () =>
                                h(
                                    NTag,
                                    {
                                        type: "error", // MERAH untuk ditolak
                                        size: "small",
                                        round: true,
                                        bordered: false,
                                    },
                                    { default: () => `Ditolak` },
                                ),
                            default: () =>
                                h("div", [
                                    h("div", `Ditolak oleh: ${formattedRoles}`),
                                    rejectedStep.notes &&
                                        h(
                                            "div",
                                            `Catatan: ${rejectedStep.notes}`,
                                        ),
                                ]),
                        },
                    );
                }

                // 2. Jika semua sudah approved
                if (allApproved) {
                    return h(
                        NTooltip,
                        { placement: "top" },
                        {
                            trigger: () =>
                                h(
                                    NTag,
                                    {
                                        type: "success", // HIJAU untuk selesai (semua step sudah lewat)
                                        size: "small",
                                        round: true,
                                        bordered: false,
                                    },
                                    { default: () => "Selesai" },
                                ),
                            default: () =>
                                h("div", [
                                    h(
                                        "div",
                                        "✓ Semua persetujuan telah selesai",
                                    ),
                                    h(
                                        "div",
                                        `Total steps: ${sortedApprovals.length}`,
                                    ),
                                ]),
                        },
                    );
                }

                // 3. Jika masih ada yang pending
                if (currentPending && currentPendingIndex !== -1) {
                    const roleNames = getRoleNames(currentPending);
                    const formattedRoles = formatRoleNames(roleNames);
                    const isUserStep = userStepIndex === currentPendingIndex;
                    const isStepBeforeUser =
                        userStepIndex !== -1 &&
                        currentPendingIndex < userStepIndex;
                    const isStepAfterUser =
                        userStepIndex !== -1 &&
                        currentPendingIndex > userStepIndex;

                    // 3a. Step yang sedang pending adalah step role user login (step sekarang)
                    if (isUserStep) {
                        return h(
                            NTooltip,
                            { placement: "top" },
                            {
                                trigger: () =>
                                    h(
                                        NTag,
                                        {
                                            type: "warning", // HIJAU untuk step yang menjadi tanggung jawab user sekarang
                                            size: "small",
                                            round: true,
                                            bordered: false,
                                            class: "font-bold text-yellow-800",
                                        },
                                        {
                                            default: () =>
                                                `Perlu Verifikasi Anda`,
                                        },
                                    ),
                                default: () =>
                                    h("div", [
                                        h(
                                            "div",
                                            `Menunggu persetujuan dari: ${formattedRoles}`,
                                        ),
                                        h(
                                            "div",
                                            `Role Anda: ${currentUserRoleName}`,
                                        ),
                                        h(
                                            "div",
                                            `Step ${currentPending.approval_step?.step_order} dari ${sortedApprovals.length}`,
                                        ),
                                        h(
                                            "div",
                                            "Klik Aksi untuk memverifikasi",
                                        ),
                                    ]),
                            },
                        );
                    }

                    // 3b. Step yang pending adalah step sebelum role user (sudah lewat)
                    if (isStepBeforeUser) {
                        return h(
                            NTooltip,
                            { placement: "top" },
                            {
                                trigger: () =>
                                    h(
                                        NTag,
                                        {
                                            type: "warning", // KUNING untuk step sebelum role user (masih menunggu step sebelumnya)
                                            size: "small",
                                            round: true,
                                            bordered: false,
                                        },
                                        {
                                            default: () =>
                                                `Menunggu ${formattedRoles}`,
                                        },
                                    ),
                                default: () =>
                                    h("div", [
                                        h(
                                            "div",
                                            `Menunggu persetujuan dari: ${formattedRoles}`,
                                        ),
                                        h(
                                            "div",
                                            `Step ${currentPending.approval_step?.step_order} dari ${sortedApprovals.length}`,
                                        ),
                                        h(
                                            "div",
                                            "Menunggu verifikasi dari step sebelumnya",
                                        ),
                                    ]),
                            },
                        );
                    }

                    // 3c. Step yang pending adalah step setelah role user (sudah lewat dari role user)
                    if (isStepAfterUser) {
                        // Cek apakah role user sudah menyetujui atau belum
                        const userApproval = sortedApprovals[userStepIndex];
                        const hasUserApproved =
                            userApproval?.status === "approved";

                        if (hasUserApproved) {
                            return h(
                                NTooltip,
                                { placement: "top" },
                                {
                                    trigger: () =>
                                        h(
                                            NTag,
                                            {
                                                type: "success", // HIJAU jika user sudah approve dan menunggu step berikutnya
                                                size: "small",
                                                round: true,
                                                bordered: false,
                                            },
                                            {
                                                default: () =>
                                                    `Menunggu ${formattedRoles}`,
                                            },
                                        ),
                                    default: () =>
                                        h("div", [
                                            h(
                                                "div",
                                                `Menunggu persetujuan dari: ${formattedRoles}`,
                                            ),
                                            h(
                                                "div",
                                                `Anda telah menyetujui step ${userApproval.approval_step?.step_order}`,
                                            ),
                                            h(
                                                "div",
                                                `Sekarang menunggu step ${currentPending.approval_step?.step_order}`,
                                            ),
                                        ]),
                                },
                            );
                        } else {
                            return h(
                                NTooltip,
                                { placement: "top" },
                                {
                                    trigger: () =>
                                        h(
                                            NTag,
                                            {
                                                type: "warning", // KUNING jika user belum approve dan step setelahnya pending
                                                size: "small",
                                                round: true,
                                                bordered: false,
                                            },
                                            {
                                                default: () =>
                                                    `Menunggu ${formattedRoles}`,
                                            },
                                        ),
                                    default: () =>
                                        h("div", [
                                            h(
                                                "div",
                                                `Menunggu persetujuan dari: ${formattedRoles}`,
                                            ),
                                            h(
                                                "div",
                                                `Anda perlu menyetujui step ${userApproval.approval_step?.step_order} terlebih dahulu`,
                                            ),
                                        ]),
                                },
                            );
                        }
                    }

                    // 3d. Default jika tidak ada role user (misalnya user tidak memiliki role dalam approval ini)
                    return h(
                        NTooltip,
                        { placement: "top" },
                        {
                            trigger: () =>
                                h(
                                    NTag,
                                    {
                                        type: "warning", // KUNING default
                                        size: "small",
                                        round: true,
                                        bordered: false,
                                    },
                                    {
                                        default: () =>
                                            `Menunggu ${formattedRoles}`,
                                    },
                                ),
                            default: () =>
                                h("div", [
                                    h(
                                        "div",
                                        `Menunggu persetujuan dari: ${formattedRoles}`,
                                    ),
                                    h(
                                        "div",
                                        `Step ${currentPending.approval_step?.step_order} dari ${sortedApprovals.length}`,
                                    ),
                                ]),
                        },
                    );
                }

                // Fallback
                return h(
                    NTag,
                    {
                        type: "default",
                        size: "small",
                        round: true,
                        bordered: false,
                    },
                    { default: () => row.status || "Unknown" },
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
                showEdit: false,
                showDelete: false,
                showView: false,
                showDetail: true,
                size: "small",
            },
            sorter: false,
            // ✅ Setting visible berdasarkan role
            visible: [
                "Admin",
                "Super Admin",
                "Supervisor",
                "Chef",
                "Manager",
                "General Manager",
                "Manager Accounting",
                "Finance",
            ].includes(roleName.value),
        },
    ];

    // Filter kolom yang visible
    return columns.filter((column) => column.visible !== false);
});

// Actions configuration
const actions = {
    onEdit: edit,
    onDelete: hapus,
    onDetail: (row) => fetchDetail("approval", "detail", row),
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig.value, actions));
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
                :loading-options="loadingDepartments"
                :filters="filters"
                :show-search="true"
                :show-status="true"
                :show-department="true"
                :department-options="departmentOptions"
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
