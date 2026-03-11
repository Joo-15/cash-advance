<script setup>
/* =====================
 | Imports
 ===================== */
import { ref, h, computed, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import {
    NButton,
    NInput,
    useMessage,
    useDialog,
    NIcon,
    NSelect,
    NSpace,
} from "naive-ui";

import { useInertiaDataTable } from "@/Composables/useInertiaDataTable";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";
import {
    Add,
    CashOutline,
    DownloadOutline,
    EyeOutline,
    PencilOutline,
    RefreshOutline,
    TrashOutline,
} from "@vicons/ionicons5";
import { STATUS_OPTIONS } from "@/Constants/status";
import { CASH_ADVANCE_STATS } from "@/Constants/cashAdvanceStats";
import FormCashAdvance from "./FormCashAdvance.vue";
import { formatRupiah } from "@/utils/helpers";
import { useTableColumns } from "@/Composables/useTableColumns";

/* =====================
 | Props (Inertia)
 ===================== */
const page = usePage();

const props = defineProps({
    cashadvance: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            sort: null,
            order: null,
            pageSize: 10,
            page: 1,
            search: "",
        }),
    },
    statData: Object,
    flash: Object,
});

/* =====================
 | Local State
 ===================== */
const statusOptions = STATUS_OPTIONS;
const stats = CASH_ADVANCE_STATS;

const modal = ref(false);
const selectedRow = ref(null);

const dialog = useDialog();
const message = useMessage();

/* =====================
 Computed / Derived State
 ===================== */
// const emit = defineEmits(["edit", "delete", "view-detail"]);

const { createColumns } = useTableColumns();

const rows = computed(() => {
    return props.cashadvance.data.map((row) => ({
        ...row,
        detail: true,
    }));
});

/* =====================
 | DataTable Composable
 ===================== */
// NOTE:
// - route() dipanggil di Page (BENAR)
// - composable hanya menerima URL, tidak tahu Ziggy
const {
    loadingSearch,
    loadingReset,
    filters,
    handlePageChange,
    handlePageSizeChange,
    handleSortChange,
    handleClear,
} = useInertiaDataTable({
    route: route("pengajuan-pinjaman.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.cashadvance.per_page ?? 10),
        sort: props.filters.sort || null, // ✅ Tambahkan dari props
        order: props.filters.order || null, // ✅ Tambahkan dari props
    },
    only: ["cashadvance"],
});

/* =====================
 | Table Columns
 ===================== */

// Konfigurasi kolom dari component
const columnConfig = [
    {
        title: "Tanggal",
        key: "tanggal",
        type: "date",
        width: 120,
        sorter: true, // Auto detect dari type
        sortOrder:
            filters.sort === "tanggal"
                ? filters.order === "asc"
                    ? "ascend"
                    : "descend"
                : false,
        width: 120,
    },
    {
        title: "Keperluan",
        key: "keperluan",
        width: 200,
        ellipsis: {
            tooltip: true,
        },
    },
    {
        title: "Jumlah",
        key: "jumlah",
        type: "currency",
        currency: "IDR",
        align: "right",
        sorter: true, // Auto detect dari type
        sortOrder:
            props.filters?.sort === "jumlah"
                ? props.filters?.order === "asc"
                    ? "ascend"
                    : "descend"
                : false,
        width: 150,
    },
    {
        title: "Status",
        key: "status",
        type: "status",
        width: 80,
        align: "center",
        statusMap: {
            pending: { type: "warning", label: "Pandding" },
            approved: { type: "success", label: "Approved" },
            rejected: { type: "error", label: "Rejected" },
            default: { type: "default", label: "Unknown" },
        },
    },
    {
        title: "Aksi",
        key: "actions",
        type: "action",
        width: 100,
        fixed: "center",
        actionConfig: {
            showEdit: true,
            showDelete: true,
            showView: true,
            size: "small",
            editProps: {
                disabled: false,
            },
            deleteProps: {
                // Props khusus untuk delete button
            },
            // customButtons: [
            //     {
            //         type: "warning",
            //         icon: CashOutline,
            //         tooltip: "Proses Pembayaran",
            //         onClick: (row) => handlePayment(row),
            //     },
            // ],
        },
    },
];

// Generate columns dengan actions
const columns = computed(() => {
    return createColumns(columnConfig, actions);
});

/* =====================
 | Actions
 ===================== */
const tambah = () => {
    selectedRow.value = null;
    modal.value = true;
};

const edit = async (row) => {
    try {
        const data = row.detail
            ? row
            : (await axios.get(route("pengajuan-pinjaman.show", row.id))).data;

        selectedRow.value = { ...data };
        modal.value = true;
    } catch (error) {
        console.error("Error mengambil detail:", error);
    }
};

const hapus = (id) => {
    dialog.warning({
        title: "Konfirmasi",
        content: "Hapus data?",
        positiveText: "Hapus",
        negativeText: "Batal",
        onPositiveClick: () => {
            console.log("ID yang dikirim:", id);
            const currentPage = page.props.cashadvance.current_page;

            router.delete(route("pengajuan-pinjaman.destroy", id), {
                data: {
                    page: currentPage,
                },
                preserveScroll: true,
                preserveState: true,
                onSuccess: async () => {
                    message.success(page.props.flash.success);
                },
            });
        },
    });
};

// Actions handlers
const actions = {
    onEdit: edit,
    onDelete: hapus,
    // onView: viewDetail, // Jika ada tombol view
};

const refreshData = () => {
    // Refresh data dari Inertia
    router.reload({ only: ["cashadvance"] });
};
</script>

<template>
    <div class="min-h-screen">
        <!-- =====================
         Header
        ===================== -->
        <div
            class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between py-2"
        >
            <h1 class="text-2xl font-bold text-gray-800 p-2">
                {{ page.props.pageHeader ?? "Dashboard" }}
            </h1>

            <n-space wrap>
                <n-button ghost type="primary" @click="">
                    <template #icon>
                        <n-icon>
                            <DownloadOutline />
                        </n-icon>
                    </template>
                    Download Excel
                </n-button>

                <n-button color="#8a2be2" @click="tambah">
                    <template #icon>
                        <n-icon>
                            <Add />
                        </n-icon>
                    </template>
                    Ajukan Pinjaman
                </n-button>
            </n-space>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div
                v-for="stat in stats"
                :key="stat.key"
                class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition"
            >
                <!-- Header -->
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-medium text-gray-600">
                        {{ stat.label }}
                    </p>

                    <div
                        :class="[
                            'w-10 h-4 rounded-lg flex items-center justify-center',
                            stat.key === 'total_pengajuan' && 'bg-blue-50',
                            stat.key === 'pengajuan_disetujui' && 'bg-green-50',
                            stat.key === 'pengajuan_pending' && 'bg-yellow-50',
                            stat.key === 'pengajuan_ditolak' && 'bg-red-50',
                        ]"
                    >
                        <!-- Icon -->
                        <svg
                            class="w-5 h-5"
                            :class="[
                                stat.key === 'total_pengajuan' &&
                                    'text-blue-600',
                                stat.key === 'pengajuan_disetujui' &&
                                    'text-green-600',
                                stat.key === 'pengajuan_pending' &&
                                    'text-yellow-600',
                                stat.key === 'pengajuan_ditolak' &&
                                    'text-red-600',
                            ]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <!-- Total -->
                            <path
                                v-if="stat.key === 'total_pengajuan'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"
                            />

                            <!-- Approved -->
                            <path
                                v-else-if="stat.key === 'pengajuan_disetujui'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />

                            <!-- Pending -->
                            <path
                                v-else-if="stat.key === 'pengajuan_pending'"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />

                            <!-- Rejected -->
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Value -->
                <div>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ statData?.[stat.key] ?? 0 }}
                    </p>

                    <p class="text-xs text-gray-400 mt-1">Data pengajuan</p>
                </div>
            </div>
        </div>

        <!-- =====================
         Filter / Search
        ===================== -->
        <div class="shadow-sm mb-4">
            <div class="flex items-center gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <n-input
                        class=""
                        v-model:value="filters.search"
                        placeholder="Cari pinjaman..."
                        clearable
                        :loading="loadingSearch"
                    />
                </div>

                <!-- Filter Status -->
                <div class="w-56">
                    <n-select
                        v-model:value="filters.status"
                        :options="statusOptions"
                        placeholder="Filter Status"
                        clearable
                    />
                </div>
                <n-button
                    :loading="loadingReset"
                    @click="handleClear"
                    strong
                    secondary
                >
                    <template #icon>
                        <n-icon>
                            <RefreshOutline />
                        </n-icon>
                    </template>
                    Reset
                </n-button>
            </div>
        </div>

        <!-- =====================
         Data Table
        ===================== -->
        <div class="bg-white rounded-xl shadow-sm">
            <inertia-data-table
                :columns="columns"
                :data="rows"
                :meta="cashadvance"
                :page-size="filters.pageSize"
                @update:page="handlePageChange"
                @update:pageSize="handlePageSizeChange"
                @update:sorter="handleSortChange"
            />
        </div>
    </div>

    <FormCashAdvance
        v-model:show="modal"
        :data-edit="selectedRow"
        @updated="refreshData"
    />
</template>
