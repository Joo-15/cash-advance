<script setup>
import { DownloadOutline, SearchOutline } from "@vicons/ionicons5";
import {
    NButton,
    NDatePicker,
    NIcon,
    NInput,
    NSelect,
    useMessage,
} from "naive-ui";
import { onMounted, ref } from "vue";

const props = defineProps({
    placeholder: { type: String, default: "Cari data disini..." },
    filters: { type: Object, required: true },
    showSearch: { type: Boolean, default: false },
    showDateRange: { type: Boolean, default: false },
    showStatus: { type: Boolean, default: false },
    showDepartment: { type: Boolean, default: false },
    showDownload: { type: Boolean, default: false },
    departmentOptions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    loadingSearch: { type: Boolean, default: false },
    loadingOptions: { type: Boolean, default: false },
});

const emit = defineEmits([
    "update:search",
    "update:dateRange",
    "update:status",
    "update:department",
    "download",
]);

const animatedFilters = ref(false);
const showPdfModal = ref(false);
const pdfUrl = ref("");
const currentReceiptId = ref(null);
const isLoadingPdf = ref(false);
const message = useMessage();

const toIndonesiaDate = (timestamp) => {
    if (!timestamp) return null;
    const date = new Date(timestamp);
    if (isNaN(date.getTime())) return null;

    // Format manual (YYYY-MM-DD)
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

const exportPDF = async () => {
    // Validasi date range
    if (!props.filters?.dateRange || props.filters.dateRange.length !== 2) {
        message.warning("Silakan pilih rentang tanggal terlebih dahulu!");
        return;
    }

    const [startDate, endDate] = props.filters.dateRange;

    // Validasi tanggal
    if (!startDate || !endDate) {
        message.warning("Tanggal mulai dan tanggal akhir harus diisi!");
        return;
    }

    // Validasi tanggal mulai tidak boleh lebih besar dari tanggal akhir
    if (new Date(startDate) > new Date(endDate)) {
        message.warning(
            "Tanggal mulai tidak boleh lebih besar dari tanggal akhir!",
        );
        return;
    }

    // Set loading true
    isLoadingPdf.value = true;

    try {
        // Format date untuk dikirim ke backend
        const params = {
            start_date: formatDateTimestamp(startDate),
            end_date: formatDateTimestamp(endDate),
            // Tambahkan parameter tambahan jika diperlukan
            form_type: props.formType || "default",
            mode: "print",
        };

        const response = await axios.get(route(`report.cetakPdf`), {
            params: params,
            responseType: "blob",
            timeout: 30000, // 30 detik timeout
        });

        // Cek response
        if (!response.data || response.data.size === 0) {
            throw new Error("PDF kosong atau tidak valid");
        }

        const blob = new Blob([response.data], {
            type: response.headers["content-type"] || "application/pdf",
        });

        // Validasi blob
        if (blob.size === 0) {
            throw new Error("File PDF kosong");
        }

        const url = URL.createObjectURL(blob);

        // Simpan URL untuk preview
        pdfUrl.value = url;
        currentFormType.value = props.formType || "default";
        showPdfModal.value = true;

        message.success("PDF berhasil dimuat!");
    } catch (error) {
        console.error("Error printing receipt:", error);

        // Error handling yang lebih detail
        if (error.response) {
            // Server meresponse dengan error
            if (error.response.status === 404) {
                message.error("Endpoint tidak ditemukan!");
            } else if (error.response.status === 422) {
                message.error("Data tidak valid! Periksa kembali input Anda.");
            } else if (error.response.status === 500) {
                message.error("Terjadi kesalahan pada server!");
            } else {
                message.error(
                    error.response.data?.message || "Gagal menampilkan PDF",
                );
            }
        } else if (error.request) {
            // Request dibuat tapi tidak ada response
            message.error(
                "Tidak ada respons dari server. Periksa koneksi Anda!",
            );
        } else {
            // Error lainnya
            message.error(error.message || "Gagal menampilkan PDF");
        }
    } finally {
        setTimeout(() => {
            isLoadingPdf.value = false;
        }, 500);
    }
};

// Fungsi untuk format date ke timestamp atau format yang diinginkan
const formatDateTimestamp = (date) => {
    if (!date) return null;

    // Jika date adalah string, konversi ke Date object
    const dateObj = typeof date === "string" ? new Date(date) : date;

    // Opsi 1: Kirim sebagai timestamp (milliseconds)
    return dateObj.getTime();

    // Opsi 2: Kirim sebagai ISO string
    // return dateObj.toISOString();

    // Opsi 3: Kirim sebagai format YYYY-MM-DD
    // return dateObj.toISOString().split('T')[0];
};

onMounted(() => {
    setTimeout(() => {
        animatedFilters.value = true;
    }, 0);
});
</script>
<template>
    <div
        class="transform transition-all duration-500"
        :class="
            animatedFilters
                ? 'translate-y-0 opacity-100'
                : 'translate-y-10 opacity-0'
        "
    >
        <div class="mb-4">
            <!-- Desktop Layout (horizontal) -->
            <div class="hidden md:flex items-center gap-4">
                <!-- Search Input -->
                <div class="flex-1" v-if="showSearch">
                    <n-input
                        :value="filters.search"
                        :placeholder="placeholder"
                        clearable
                        :loading="loadingSearch"
                        @update:value="$emit('update:search', $event)"
                    >
                        <template #prefix>
                            <n-icon :component="SearchOutline" />
                        </template>
                    </n-input>
                </div>

                <div v-if="showDateRange">
                    <n-date-picker
                        class="filter-datepicker w-full"
                        v-model:value="filters.disbursed_at"
                        type="daterange"
                        clearable
                        @update:value="$emit('update:dateRange', $event)"
                    />
                </div>

                <!-- Department Select -->
                <div v-if="showDepartment" class="w-56">
                    <n-select
                        :value="filters.department"
                        :options="departmentOptions"
                        placeholder="Pilih Departemen"
                        clearable
                        @update:value="$emit('update:department', $event)"
                    />
                </div>

                <!-- Status Select -->
                <div v-if="showStatus" class="w-40">
                    <n-select
                        :value="filters.status"
                        :options="statusOptions"
                        placeholder="Pilih Status"
                        clearable
                        @update:value="$emit('update:status', $event)"
                    />
                </div>

                <!-- Download Button -->
                <div v-if="showDownload">
                    <n-button
                        ghost
                        type="primary"
                        class="!w-full md:!w-auto"
                        :loading="isLoadingPdf"
                        @click="exportPDF"
                    >
                        <template #icon>
                            <n-icon><download-outline /></n-icon>
                        </template>
                        {{ isLoadingPdf ? "Loading..." : "Cetak PDF" }}
                    </n-button>
                </div>
            </div>

            <!-- Mobile Layout (vertical with grid) -->
            <div class="md:hidden space-y-3">
                <!-- Search Input - Full width on mobile -->
                <div v-if="showSearch" class="w-full">
                    <n-input
                        :value="filters.search"
                        :placeholder="placeholder"
                        clearable
                        :loading="loadingSearch"
                        @update:value="$emit('update:search', $event)"
                    />
                </div>

                <div v-if="showDepartment" class="w-full">
                    <n-select
                        :value="filters.department"
                        :options="departmentOptions"
                        placeholder="Pilih Departemen"
                        clearable
                        @update:value="$emit('update:department', $event)"
                    />
                </div>

                <div v-if="showStatus" class="w-full">
                    <n-select
                        :value="filters.status"
                        :options="statusOptions"
                        placeholder="Pilih Status"
                        clearable
                        @update:value="$emit('update:status', $event)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
