<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    NCard,
    NGrid,
    NGridItem,
    NText,
    NIcon,
    NTag,
    NProgress,
    NDatePicker,
} from "naive-ui";
import {
    TrendingUpOutline,
    CheckmarkCircleOutline,
    TimeOutline,
    WarningOutline,
    ArrowUpOutline,
    AlertOutline,
    BusinessOutline,
    BriefcaseOutline,
    CashOutline,
    CodeOutline,
    PeopleOutline,
    GridOutline,
    RefreshOutline,
    BarChartOutline,
} from "@vicons/ionicons5";
import Chart from "chart.js/auto";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import { formatRupiah } from "@/utils/helpers";

// Define props dari Inertia
const props = defineProps({
    stats: {
        type: Object,
        required: true,
        default: () => ({
            total_pengajuan: 0,
            total_dicairkan: 0,
            pending_amount: 0,
            pending_count: 0,
            unaccounted_amount: 0,
            percentage_change: 0,
            transaction_count: 0,
        }),
    },
    per_departemen: {
        type: Array,
        required: true,
        default: () => [],
    },
    status_counts: {
        type: Object,
        required: true,
        default: () => ({
            disbursed: 0,
            approved: 0,
            pending: 0,
            rejected: 0,
        }),
    },
    trend: {
        type: Object,
        required: true,
        default: () => ({
            months: [],
            total: [],
            disbursed: [],
        }),
    },
    current_month: {
        type: String,
        required: true,
        default: () => {
            const now = new Date();
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
        },
    },
});

// Refs
const trendChart = ref(null);
let chartInstance = null;
const isLoading = ref(false);
const timestamp = ref(Date.now());

// Computed
const totalStatus = computed(() => {
    return (
        props.status_counts.disbursed +
        props.status_counts.approved +
        props.status_counts.pending +
        props.status_counts.rejected
    );
});

// Methods

const getPercentage = (value, total) => {
    if (total === 0) return 0;
    return (value / total) * 100;
};

const getIcon = (iconName) => {
    const icons = {
        BusinessOutline,
        BriefcaseOutline,
        CashOutline,
        CodeOutline,
        PeopleOutline,
        GridOutline,
    };
    return icons[iconName] || BusinessOutline;
};

const handleMonthChange = (value) => {
    if (value) {
        isLoading.value = false;

        const date = new Date(value);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");

        router.get(
            "/dashboard",
            { month: `${year}-${month}` },
            {
                preserveState: true,
                preserveScroll: true,
                onFinish: () => {
                    setTimeout(() => {
                        isLoading.value = false;
                    }, 500);
                },
            },
        );
    }
};

// const refreshData = () => {
//     isLoading.value = true;
//     router.get(
//         "/dashboard",
//         {},
//         {
//             preserveState: true,
//             preserveScroll: true,
//             onFinish: () => {
//                 setTimeout(() => {
//                     isLoading.value = false;
//                 }, 500);
//             },
//         },
//     );
// };

// Initialize chart
const initChart = () => {
    if (trendChart.value && props.trend.months.length > 0) {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = trendChart.value.getContext("2d");
        const gradient1 = ctx.createLinearGradient(0, 0, 0, 300);
        gradient1.addColorStop(0, "#18a058");
        gradient1.addColorStop(1, "#0d6e3d");

        const gradient2 = ctx.createLinearGradient(0, 0, 0, 300);
        gradient2.addColorStop(0, "#2080f0");
        gradient2.addColorStop(1, "#0c4e9e");

        chartInstance = new Chart(trendChart.value, {
            type: "line",
            data: {
                labels: props.trend.months,
                datasets: [
                    {
                        label: "Total Pengajuan",
                        data: props.trend.total,
                        borderColor: gradient1,
                        backgroundColor: "rgba(24, 160, 88, 0.1)",
                        borderWidth: 3,
                        borderRadius: 6,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: "#18a058",
                        pointBorderColor: "#fff",
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                    {
                        label: "Sudah Dicairkan",
                        data: props.trend.disbursed,
                        borderColor: gradient2,
                        backgroundColor: "rgba(32, 128, 240, 0.1)",
                        borderWidth: 3,
                        borderRadius: 6,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: "#2080f0",
                        pointBorderColor: "#fff",
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 2000,
                    easing: "easeInOutQuart",
                },
                interaction: {
                    intersect: false,
                    mode: "index",
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                label += formatRupiah(context.raw);
                                return label;
                            },
                        },
                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                        padding: 12,
                        cornerRadius: 8,
                        titleColor: "#fff",
                        bodyColor: "#e0e0e0",
                    },
                    legend: {
                        position: "top",
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: "500",
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                            drawBorder: false,
                        },
                        ticks: {
                            callback: (value) => {
                                return formatRupiah(value);
                            },
                            font: {
                                size: 11,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: "500",
                            },
                        },
                    },
                },
            },
        });
    }
};

// Watch for trend changes
watch(
    () => props.trend,
    () => {
        initChart();
    },
    { deep: true },
);

// Lifecycle
onMounted(() => {
    initChart();
});

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>

<template>
    <Container>
        <template #header>
            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    width: 100%;
                "
            >
                <PageHeader title="Dashboard" />
                <n-date-picker
                    v-model:value="timestamp"
                    type="month"
                    clearable
                    @update:value="handleMonthChange"
                />
            </div>
        </template>

        <template #content>
            <!-- Loading Overlay -->
            <div
                v-if="isLoading"
                class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center transition-all duration-300"
            >
                <div
                    class="bg-white rounded-2xl p-6 shadow-2xl flex flex-col items-center gap-4"
                >
                    <div
                        class="w-12 h-12 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin"
                    ></div>
                    <p class="text-gray-600 text-sm font-medium">Loading...</p>
                </div>
            </div>

            <div
                class="min-h-screen"
                :class="{ 'opacity-50 pointer-events-none': isLoading }"
            >
                <!-- Statistik Cards -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Total Pengajuan -->
                    <div class="w-full">
                        <!-- Tambahkan wrapper dengan w-full -->
                        <div
                            class="group rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fadeInUp h-full"
                            style="animation-delay: 0.1s"
                        >
                            <div
                                class="bg-gradient-to-br from-indigo-500 to-purple-600 p-4 text-white h-full"
                            >
                                <div
                                    class="flex justify-between items-start mb-3"
                                >
                                    <span
                                        class="text-xs font-medium uppercase tracking-wider opacity-90"
                                        >Total Pengajuan Bulan</span
                                    >
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                                    >
                                        <n-icon :size="20" color="white">
                                            <TrendingUpOutline />
                                        </n-icon>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-3">
                                    {{ formatRupiah(stats.total_pengajuan) }}
                                </div>
                                <div
                                    class="inline-flex items-center gap-1 text-xs bg-white/20 rounded-full px-2 py-0.5"
                                >
                                    <n-icon :size="15" color="white">
                                        <ArrowUpOutline />
                                    </n-icon>
                                    <span
                                        >{{ stats.percentage_change }}% vs bulan
                                        lalu</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sudah Dicairkan -->
                    <div class="w-full">
                        <div
                            class="group rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fadeInUp h-full"
                            style="animation-delay: 0.2s"
                        >
                            <div
                                class="bg-gradient-to-br from-teal-500 to-green-600 p-4 text-white h-full"
                            >
                                <div
                                    class="flex justify-between items-start mb-3"
                                >
                                    <span
                                        class="text-xs font-medium uppercase tracking-wider opacity-90"
                                        >Sudah Dicairkan</span
                                    >
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                                    >
                                        <n-icon :size="20" color="white">
                                            <CheckmarkCircleOutline />
                                        </n-icon>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-3">
                                    {{ formatRupiah(stats.total_dicairkan) }}
                                </div>
                                <div class="text-xs opacity-90">
                                    {{ stats.transaction_count }} transaksi
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="w-full">
                        <div
                            class="group rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fadeInUp h-full"
                            style="animation-delay: 0.3s"
                        >
                            <div
                                class="bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white h-full"
                            >
                                <div
                                    class="flex justify-between items-start mb-3"
                                >
                                    <span
                                        class="text-xs font-medium uppercase tracking-wider opacity-90"
                                        >Pending Persetujuan</span
                                    >
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                                    >
                                        <n-icon :size="20" color="white">
                                            <TimeOutline />
                                        </n-icon>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-3">
                                    {{ formatRupiah(stats.pending_amount) }}
                                </div>
                                <div class="text-xs opacity-90">
                                    {{ stats.pending_count }} pengajuan menunggu
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Belum Dipertanggungjawabkan -->
                    <div class="w-full">
                        <div
                            class="group rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 animate-fadeInUp h-full"
                            style="animation-delay: 0.4s"
                        >
                            <div
                                class="bg-gradient-to-br from-red-500 to-pink-600 p-4 text-white h-full"
                            >
                                <div
                                    class="flex justify-between items-start mb-3"
                                >
                                    <span
                                        class="text-xs font-medium uppercase tracking-wider opacity-90"
                                        >Belum Dipertanggungjawabkan</span
                                    >
                                    <div
                                        class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                                    >
                                        <n-icon :size="20" color="white">
                                            <WarningOutline />
                                        </n-icon>
                                    </div>
                                </div>
                                <div class="text-2xl font-bold mb-3">
                                    {{ formatRupiah(stats.unaccounted_amount) }}
                                </div>

                                <!-- <div
                                    class="inline-flex items-center gap-1 text-xs bg-white/20 rounded-full px-2 py-0.5"
                                >
                                    <n-icon :size="15" color="white">
                                        <ArrowUpOutline />
                                    </n-icon>
                                    <span
                                        >{{ stats.percentage_change }}% vs bulan
                                        lalu</span
                                    >
                                </div -->

                                <div
                                    class="inline-flex items-center gap-1 text-xs bg-white/20 rounded-full px-2 py-0.5"
                                >
                                    <n-icon :size="15" color="white">
                                        <AlertOutline />
                                    </n-icon>
                                    <span>perlu perhatian</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid 2 Kolom -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    <!-- Pengajuan per Departemen -->
                    <div
                        class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300"
                    >
                        <div
                            class="border-b border-gray-100 px-4 py-3 flex justify-between items-center"
                        >
                            <h3 class="text-sm font-semibold text-gray-700">
                                Pengajuan per Departemen
                            </h3>
                            <div
                                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-2 py-1 rounded-full text-[10px] font-semibold"
                            >
                                Bulan Ini
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div
                                    v-for="(dept, index) in per_departemen"
                                    :key="dept.name"
                                    class="group flex justify-between items-center p-2 rounded-lg transition-all duration-300 hover:bg-gray-50 hover:translate-x-1 animate-slideInLeft"
                                    :style="{
                                        animationDelay: `${index * 0.05}s`,
                                    }"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center transition-all duration-300 group-hover:scale-110"
                                            :style="{
                                                backgroundColor:
                                                    (dept.color || '#18a058') +
                                                    '20',
                                            }"
                                        >
                                            <n-icon
                                                :size="18"
                                                :color="dept.color || '#18a058'"
                                            >
                                                <component
                                                    :is="getIcon(dept.icon)"
                                                />
                                            </n-icon>
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-semibold text-gray-700"
                                            >
                                                {{ dept.name }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                {{ dept.total_requests }}
                                                pengajuan
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="relative text-right min-w-[120px]"
                                    >
                                        <div
                                            class="text-sm font-bold relative z-10"
                                            :style="{
                                                color: dept.color || '#18a058',
                                            }"
                                        >
                                            {{ formatRupiah(dept.amount) }}
                                        </div>
                                        <div
                                            class="absolute -bottom-1 right-0 h-0.5 rounded-full transition-all duration-500 group-hover:h-1"
                                            :style="{
                                                width: `${(dept.amount / per_departemen[0]?.amount) * 100}%`,
                                                backgroundColor:
                                                    dept.color || '#18a058',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Pengajuan -->
                    <div
                        class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300"
                    >
                        <div class="border-b border-gray-100 px-4 py-3">
                            <h3 class="text-sm font-semibold text-gray-700">
                                Status Pengajuan
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <!-- Dicairkan -->
                                <div
                                    class="animate-fadeIn"
                                    style="animation-delay: 0.1s"
                                >
                                    <div
                                        class="flex justify-between items-center mb-1"
                                    >
                                        <div class="flex items-center gap-2">
                                            <NTag
                                                type="success"
                                                size="small"
                                                round
                                                class="cursor-pointer transition-transform hover:scale-105"
                                            >
                                                Dicairkan
                                            </NTag>
                                            <span
                                                class="text-sm font-semibold text-gray-700"
                                                >{{
                                                    status_counts.disbursed
                                                }}</span
                                            >
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-gray-500"
                                        >
                                            {{
                                                getPercentage(
                                                    status_counts.disbursed,
                                                    totalStatus,
                                                ).toFixed(0)
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="w-full bg-gray-100 rounded-full h-2 overflow-hidden"
                                    >
                                        <div
                                            class="bg-green-500 h-full rounded-full transition-all duration-1000"
                                            :style="{
                                                width: `${getPercentage(status_counts.disbursed, totalStatus)}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Disetujui -->
                                <div
                                    class="animate-fadeIn"
                                    style="animation-delay: 0.2s"
                                >
                                    <div
                                        class="flex justify-between items-center mb-1"
                                    >
                                        <div class="flex items-center gap-2">
                                            <NTag
                                                type="info"
                                                size="small"
                                                round
                                                class="cursor-pointer transition-transform hover:scale-105"
                                            >
                                                Disetujui
                                            </NTag>
                                            <span
                                                class="text-sm font-semibold text-gray-700"
                                                >{{
                                                    status_counts.approved
                                                }}</span
                                            >
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-gray-500"
                                        >
                                            {{
                                                getPercentage(
                                                    status_counts.approved,
                                                    totalStatus,
                                                ).toFixed(0)
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="w-full bg-gray-100 rounded-full h-2 overflow-hidden"
                                    >
                                        <div
                                            class="bg-blue-500 h-full rounded-full transition-all duration-1000"
                                            :style="{
                                                width: `${getPercentage(status_counts.approved, totalStatus)}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Pending -->
                                <div
                                    class="animate-fadeIn"
                                    style="animation-delay: 0.3s"
                                >
                                    <div
                                        class="flex justify-between items-center mb-1"
                                    >
                                        <div class="flex items-center gap-2">
                                            <NTag
                                                type="warning"
                                                size="small"
                                                round
                                                class="cursor-pointer transition-transform hover:scale-105"
                                            >
                                                Pending
                                            </NTag>
                                            <span
                                                class="text-sm font-semibold text-gray-700"
                                                >{{
                                                    status_counts.pending
                                                }}</span
                                            >
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-gray-500"
                                        >
                                            {{
                                                getPercentage(
                                                    status_counts.pending,
                                                    totalStatus,
                                                ).toFixed(0)
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="w-full bg-gray-100 rounded-full h-2 overflow-hidden"
                                    >
                                        <div
                                            class="bg-amber-500 h-full rounded-full transition-all duration-1000"
                                            :style="{
                                                width: `${getPercentage(status_counts.pending, totalStatus)}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Ditolak -->
                                <div
                                    class="animate-fadeIn"
                                    style="animation-delay: 0.4s"
                                >
                                    <div
                                        class="flex justify-between items-center mb-1"
                                    >
                                        <div class="flex items-center gap-2">
                                            <NTag
                                                type="error"
                                                size="small"
                                                round
                                                class="cursor-pointer transition-transform hover:scale-105"
                                            >
                                                Ditolak
                                            </NTag>
                                            <span
                                                class="text-sm font-semibold text-gray-700"
                                                >{{
                                                    status_counts.rejected
                                                }}</span
                                            >
                                        </div>
                                        <span
                                            class="text-xs font-semibold text-gray-500"
                                        >
                                            {{
                                                getPercentage(
                                                    status_counts.rejected,
                                                    totalStatus,
                                                ).toFixed(0)
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="w-full bg-gray-100 rounded-full h-2 overflow-hidden"
                                    >
                                        <div
                                            class="bg-red-500 h-full rounded-full transition-all duration-1000"
                                            :style="{
                                                width: `${getPercentage(status_counts.rejected, totalStatus)}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trend Chart -->
                <div class="mt-4">
                    <div
                        class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300"
                    >
                        <div
                            class="border-b border-gray-100 px-4 py-3 flex justify-between items-center"
                        >
                            <h3 class="text-sm font-semibold text-gray-700">
                                Trend Pengajuan 6 Bulan Terakhir
                            </h3>
                            <div
                                class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full"
                            >
                                <BarChartOutline :size="14" />
                                <span>Interactive Chart</span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="h-80">
                                <canvas ref="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </Container>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
}

.animate-slideInLeft {
    animation: slideInLeft 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
}

.animate-fadeIn {
    animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    opacity: 0;
}

/* Custom Scrollbar */
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
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}
</style>
