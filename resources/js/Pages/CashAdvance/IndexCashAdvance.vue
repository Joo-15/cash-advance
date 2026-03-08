<script setup>
/* =====================
 | Imports
 ===================== */
import { ref, h, nextTick, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import {
    NButton,
    NInput,
    useMessage,
    useDialog,
    NIcon,
    NSelect,
} from "naive-ui";

import { useInertiaDataTable } from "@/Composables/useInertiaDataTable";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";
import { PencilOutline, TrashOutline } from "@vicons/ionicons5";
import EditCashAdvance from "./EditCashAdvance.vue";
import { STATUS_OPTIONS } from "@/Constants/status";
import { CASH_ADVANCE_STATS } from "@/Constants/cashAdvanceStats";
import { useCashAdvanceForm } from "@/Composables/CashAdvance/useCashAdvanceForm";

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
        required: true,
    },
    statData: Object,
    flash: Object,
});

/* =====================
 | Local State
 ===================== */
const status = ref(null);
const statusOptions = STATUS_OPTIONS;
const stats = CASH_ADVANCE_STATS;
const { form } = useCashAdvanceForm;

const editModal = ref(false);
const selectedRow = ref(null);

const dialog = useDialog();
const message = useMessage();

/* =====================
 Computed / Derived State
 ===================== */

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
    loading,
    search,
    pageSize,
    handlePageChange,
    handlePageSizeChange,
    handleClear,
} = useInertiaDataTable({
    route: route("pengajuan-pinjaman.index"),
    initialSearch: props.filters.search ?? "",
    initialPageSize: Number(props.cashadvance.per_page ?? 10),
    only: ["cashadvance"],
});

/* =====================
 | Table Columns
 ===================== */
const columns = [
    {
        title: "Tanggal",
        key: "tanggal",
        sorter: true,
        sortOrder: false,
    },
    {
        title: "Keperluan",
        key: "keperluan",
    },
    {
        title: "Jumlah",
        key: "jumlah",
    },
    {
        title: "Aksi",
        key: "aksi",
        render(row) {
            return [
                h(
                    NButton,
                    {
                        strong: true,
                        secondary: true,
                        circle: true,
                        type: "info",
                        size: "small",
                        onClick: () => edit(row),
                    },
                    {
                        icon: () =>
                            h(NIcon, null, {
                                default: () => h(PencilOutline),
                            }),
                    },
                ),
                h(
                    NButton,
                    {
                        strong: true,
                        secondary: true,
                        circle: true,
                        type: "error",
                        size: "small",
                        style: "margin-left:6px",
                        onClick: () => hapus(row.id),
                    },
                    {
                        // slot default bisa diisi dengan text, tapi kalau pakai icon biasanya pakai #icon slot
                        icon: () =>
                            h(NIcon, null, { default: () => h(TrashOutline) }),
                    },
                ),
            ];
        },
    },
];

/* =====================
 | Actions
 ===================== */
const edit = async (row) => {
    if (row.detail) {
        selectedRow.value = { ...row }; // langsung pakai
    } else {
        const { data } = await axios.get(`/pengajuan-pinjaman/${row.id}`);
        selectedRow.value = data; // ambil detail
    }
    editModal.value = true;
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

const submitForm = () => {
    if (!form.value.nama || !form.value.harga) {
        message.warning("Nama dan harga harus diisi");
        return;
    }

    router.post(route("produk.store"), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            message.success("Produk berhasil ditambahkan");
            form.value = { nama: "", harga: "" };
        },
    });
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
        <div class="flex items-center justify-between py-2">
            <h1 class="text-2xl font-semibold text-gray-800 p-2">
                {{ page.props.pageHeader ?? "Dashboard" }}
            </h1>
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
                            'w-10 h-10 rounded-lg flex items-center justify-center',
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
                        size="large"
                        v-model:value="search"
                        placeholder="Cari pinjaman..."
                        clearable
                        :loading="loading"
                        @clear="handleClear"
                    />
                </div>

                <!-- Filter Status -->
                <div class="w-56">
                    <n-select
                        size="large"
                        v-model:value="status"
                        :options="statusOptions"
                        placeholder="Filter Status"
                        clearable
                    />
                </div>
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
                :page-size="pageSize"
                @update:page="handlePageChange"
                @update:pageSize="handlePageSizeChange"
            />
        </div>
    </div>
    <!-- =====================
             Modal
    ===================== -->
    <EditCashAdvance
        v-model:show="editModal"
        :data-edit="selectedRow"
        @updated="refreshData"
    />
</template>
