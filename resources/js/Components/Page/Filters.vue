<script setup>
import { router } from "@inertiajs/vue3";
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

// ========== PROPS ==========
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
    printPdf: { type: Boolean, default: false },
    pdfUrl: { type: String, default: "" },
    loadingSearch: { type: Boolean, default: false },
    loadingOptions: { type: Boolean, default: false },
    loadingPdf: { type: Boolean, default: false },
});

// ========== EMITS ==========
const emit = defineEmits([
    "update:search",
    "update:dateRange",
    "update:status",
    "update:department",
    "update:pdfUrl",
    "update:loadingPdf",
    "update:printPdf",
    "downloadPdf", // ✅ Tambahkan event download
    "closePdf", // ✅ Tambahkan event close
]);

const animatedFilters = ref(false);
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

    // Set loading true
    emit("update:loadingPdf", true);

    try {
        // Validasi tanggal
        const startDateObj = new Date(props.filters.dateRange[0]);
        const endDateObj = new Date(props.filters.dateRange[1]);

        if (isNaN(startDateObj.getTime()) || isNaN(endDateObj.getTime())) {
            message.error("Format tanggal tidak valid!");
            return;
        }

        // Cek apakah start date lebih kecil dari end date
        if (startDateObj > endDateObj) {
            message.warning(
                "Tanggal mulai harus lebih kecil dari tanggal akhir!",
            );
            return;
        }

        // Format date untuk dikirim ke backend
        const startDate = toIndonesiaDate(startDateObj);
        const endDate = toIndonesiaDate(endDateObj);

        const dateParam = `${startDate}_${endDate}`;

        const response = await axios.get(
            route("report.cetakPdf", {
                date: dateParam,
            }),
            {
                responseType: "blob",
                timeout: 30000, // 30 detik timeout
            },
        );

        // Cek response status
        if (response.status !== 200) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Cek apakah response adalah PDF
        const contentType = response.headers["content-type"];
        if (!contentType || !contentType.includes("application/pdf")) {
            // Jika response berupa error message dalam bentuk JSON
            try {
                const text = await response.data.text();
                const errorData = JSON.parse(text);
                message.error(errorData.message || "Gagal generate PDF");
                return;
            } catch {
                message.error("Response yang diterima bukan file PDF");
                return;
            }
        }

        const blob = new Blob([response.data], { type: "application/pdf" });
        const url = URL.createObjectURL(blob);

        emit("update:printPdf", true);
        emit("update:pdfUrl", url);
    } catch (error) {
        console.error("Error exporting PDF:", error);

        // Handle error dengan lebih baik
        if (error.code === "ECONNABORTED") {
            message.error("Waktu permintaan habis, silakan coba lagi");
        } else if (error.response) {
            // Server merespon dengan error status
            message.error(
                error.response.data?.message || "Gagal menampilkan PDF",
            );
        } else if (error.request) {
            // Request dibuat tapi tidak ada response
            message.error("Tidak ada respon dari server, periksa koneksi Anda");
        } else {
            message.error("Terjadi kesalahan saat menampilkan PDF");
        }
    } finally {
        // Reset loading state
        emit("update:loadingPdf", false);
    }
};

const printPdf = () => {
    const iframe = document.createElement("iframe");
    iframe.style.position = "absolute";
    iframe.style.width = "0";
    iframe.style.height = "0";
    iframe.style.border = "none";
    document.body.appendChild(iframe);

    // Tunggu iframe benar-benar loaded
    iframe.onload = () => {
        // Beri sedikit delay untuk memastikan konten siap
        setTimeout(() => {
            iframe.contentWindow.print();
        }, 500);

        // Hapus iframe setelah print dialog selesai
        const checkPrint = setInterval(() => {
            if (iframe.contentWindow.document.hidden) {
                clearInterval(checkPrint);
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 1000);
            }
        }, 500);
    };

    // Handle error jika gagal load
    iframe.onerror = () => {
        console.error("Failed to load PDF");
        document.body.removeChild(iframe);
    };

    iframe.src = props.pdfUrl;
};

defineExpose({
    printPdf,
});

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
                        :loading="loadingPdf"
                        @click="exportPDF"
                    >
                        <template #icon>
                            <n-icon><download-outline /></n-icon>
                        </template>
                        {{ loadingPdf ? "Loading..." : "Preview PDF" }}
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
