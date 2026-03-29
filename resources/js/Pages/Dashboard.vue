<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import {
    NCard,
    NGrid,
    NGridItem,
    NText,
    NIcon,
    NDatePicker,
    NTag,
    NProgress,
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
} from "@vicons/ionicons5";
import Chart from "chart.js/auto";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";

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
const formatCurrency = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value || 0);
};

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
        router.get(
            "/dashboard",
            { month: value },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }
};

// Initialize chart
const initChart = () => {
    if (trendChart.value && props.trend.months.length > 0) {
        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(trendChart.value, {
            type: "bar",
            data: {
                labels: props.trend.months,
                datasets: [
                    {
                        label: "Total Pengajuan",
                        data: props.trend.total,
                        backgroundColor: "#18a058",
                        borderRadius: 6,
                    },
                    {
                        label: "Sudah Dicairkan",
                        data: props.trend.disbursed,
                        backgroundColor: "#2080f0",
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                let label = context.dataset.label || "";
                                if (label) {
                                    label += ": ";
                                }
                                label += formatCurrency(context.raw);
                                return label;
                            },
                        },
                    },
                    legend: {
                        position: "top",
                    },
                },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => {
                                return formatCurrency(value);
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
</script>
<template>
    <Container>
        <template #header>
            <PageHeader title="Dashboard"></PageHeader>
        </template>
        <template #content>
            <div class="finance-dashboard">
                <!-- Header
        <div class="dashboard-header">
            <n-text class="page-title" strong>Dashboard Finance</n-text>
            <n-date-picker
                :value="current_month"
                type="month"
                placeholder="Pilih Bulan"
                clearable
                @update:value="handleMonthChange"
                style="width: 200px"
            />
        </div> -->

                <!-- Statistik Cards -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <!-- Total Pengajuan -->
                    <n-card class="stat-card total-card" :bordered="false">
                        <div
                            class="stat-header flex justify-between items-center"
                        >
                            <n-text depth="2" class="stat-title">
                                Total Pengajuan Bulan
                            </n-text>
                            <n-icon :size="24" color="white">
                                <TrendingUpOutline />
                            </n-icon>
                        </div>

                        <div class="stat-value">
                            {{ formatCurrency(stats.total_pengajuan) }}
                        </div>

                        <div
                            class="stat-trend positive flex items-center gap-1"
                        >
                            <n-icon :size="14"><ArrowUpOutline /></n-icon>
                            {{ stats.percentage_change }}% vs bulan lalu
                        </div>
                    </n-card>

                    <!-- Sudah Dicairkan -->
                    <n-card class="stat-card disbursed-card" :bordered="false">
                        <div
                            class="stat-header flex justify-between items-center"
                        >
                            <n-text depth="2" class="stat-title">
                                Sudah Dicairkan
                            </n-text>
                            <n-icon :size="24" color="white">
                                <CheckmarkCircleOutline />
                            </n-icon>
                        </div>

                        <div class="stat-value">
                            {{ formatCurrency(stats.total_dicairkan) }}
                        </div>

                        <div class="stat-subtitle">
                            {{ stats.transaction_count }} transaksi
                        </div>
                    </n-card>

                    <!-- Pending -->
                    <n-card class="stat-card pending-card" :bordered="false">
                        <div
                            class="stat-header flex justify-between items-center"
                        >
                            <n-text depth="2" class="stat-title">
                                Pending Persetujuan
                            </n-text>
                            <n-icon :size="24" color="white">
                                <TimeOutline />
                            </n-icon>
                        </div>

                        <div class="stat-value">
                            {{ formatCurrency(stats.pending_amount) }}
                        </div>

                        <div class="stat-subtitle">
                            {{ stats.pending_count }} pengajuan menunggu
                        </div>
                    </n-card>

                    <!-- Belum Dipertanggungjawabkan -->
                    <n-card class="stat-card overdue-card" :bordered="false">
                        <div
                            class="stat-header flex justify-between items-center"
                        >
                            <n-text depth="2" class="stat-title">
                                Belum Dipertanggungjawabkan
                            </n-text>
                            <n-icon :size="24" color="white">
                                <WarningOutline />
                            </n-icon>
                        </div>

                        <div class="stat-value">
                            {{ formatCurrency(stats.unaccounted_amount) }}
                        </div>

                        <div class="stat-trend warning flex items-center gap-1">
                            <n-icon :size="14"><AlertOutline /></n-icon>
                            perlu perhatian
                        </div>
                    </n-card>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                    <!-- Pengajuan per Departemen -->
                    <n-card
                        title="Pengajuan per Departemen"
                        :bordered="false"
                        class="border rounded-lg border-slate-100 bg-slate-50/60"
                    >
                        <div class="space-y-3">
                            <div
                                v-for="dept in per_departemen"
                                :key="dept.name"
                                class="flex justify-between items-center"
                            >
                                <div class="flex items-center gap-2">
                                    <n-icon
                                        :size="20"
                                        :color="dept.color || '#18a058'"
                                    >
                                        <component :is="getIcon(dept.icon)" />
                                    </n-icon>

                                    <n-text>{{ dept.name }}</n-text>

                                    <n-text depth="3" size="small">
                                        ({{ dept.total_requests }} pengajuan)
                                    </n-text>
                                </div>

                                <n-text strong style="color: #18a058">
                                    {{ formatCurrency(dept.amount) }}
                                </n-text>
                            </div>
                        </div>
                    </n-card>

                    <!-- Status Pengajuan -->
                    <n-card title="Status Pengajuan" :bordered="false">
                        <div class="space-y-4">
                            <!-- Dicairkan -->
                            <div>
                                <div
                                    class="flex justify-between items-center mb-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <n-tag
                                            type="success"
                                            size="small"
                                            round
                                        >
                                            Dicairkan
                                        </n-tag>
                                        <n-text>{{
                                            status_counts.disbursed
                                        }}</n-text>
                                    </div>
                                </div>

                                <n-progress
                                    type="line"
                                    :percentage="
                                        getPercentage(
                                            status_counts.disbursed,
                                            totalStatus,
                                        )
                                    "
                                    :color="'#18a058'"
                                    :show-indicator="false"
                                    :height="8"
                                />
                            </div>

                            <!-- Disetujui -->
                            <div>
                                <div
                                    class="flex justify-between items-center mb-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <n-tag type="info" size="small" round>
                                            Disetujui
                                        </n-tag>
                                        <n-text>{{
                                            status_counts.approved
                                        }}</n-text>
                                    </div>
                                </div>

                                <n-progress
                                    type="line"
                                    :percentage="
                                        getPercentage(
                                            status_counts.approved,
                                            totalStatus,
                                        )
                                    "
                                    :color="'#2080f0'"
                                    :show-indicator="false"
                                    :height="8"
                                />
                            </div>

                            <!-- Pending -->
                            <div>
                                <div
                                    class="flex justify-between items-center mb-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <n-tag
                                            type="warning"
                                            size="small"
                                            round
                                        >
                                            Pending
                                        </n-tag>
                                        <n-text>{{
                                            status_counts.pending
                                        }}</n-text>
                                    </div>
                                </div>

                                <n-progress
                                    type="line"
                                    :percentage="
                                        getPercentage(
                                            status_counts.pending,
                                            totalStatus,
                                        )
                                    "
                                    :color="'#f0a020'"
                                    :show-indicator="false"
                                    :height="8"
                                />
                            </div>

                            <!-- Ditolak -->
                            <div>
                                <div
                                    class="flex justify-between items-center mb-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <n-tag type="error" size="small" round>
                                            Ditolak
                                        </n-tag>
                                        <n-text>{{
                                            status_counts.rejected
                                        }}</n-text>
                                    </div>
                                </div>

                                <n-progress
                                    type="line"
                                    :percentage="
                                        getPercentage(
                                            status_counts.rejected,
                                            totalStatus,
                                        )
                                    "
                                    :color="'#d03050'"
                                    :show-indicator="false"
                                    :height="8"
                                />
                            </div>
                        </div>
                    </n-card>
                </div>

                <!-- Row 3: Trend Chart -->
                <n-grid :cols="1" :x-gap="16" :y-gap="16" class="mt-4">
                    <n-grid-item>
                        <n-card
                            title="Trend Pengajuan 6 Bulan Terakhir"
                            :bordered="false"
                        >
                            <div style="height: 300px">
                                <canvas ref="trendChart"></canvas>
                            </div>
                        </n-card>
                    </n-grid-item>
                </n-grid>
            </div>
        </template>
    </Container>
</template>

<style scoped>
.finance-dashboard {
    /* padding: 24px; */
    /* background: #f5f7fa; */
    min-height: 100vh;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
}

/* Stat Cards */
.stat-card {
    border-radius: 12px;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.total-card {
    background: linear-gradient(135deg, #76abf1 0%, #6088e4 100%);
}

.disbursed-card {
    background: linear-gradient(135deg, #96f3e4 0%, #2fd1c0 100%);
}

.pending-card {
    background: linear-gradient(135deg, #f0eb93 0%, #dace64 100%);
}

.overdue-card {
    background: linear-gradient(135deg, #f45f4b 0%, #d38373 100%);
}

.total-card .stat-title,
.total-card .stat-value,
.total-card .stat-trend,
.disbursed-card .stat-title,
.disbursed-card .stat-value,
.disbursed-card .stat-subtitle,
.pending-card .stat-title,
.pending-card .stat-value,
.pending-card .stat-subtitle,
.overdue-card .stat-title,
.overdue-card .stat-value,
.overdue-card .stat-trend {
    color: white;
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.stat-title {
    font-size: 14px;
    font-weight: 500;
}

.stat-value {
    font-size: 28px;
    font-weight: 600;
    margin-bottom: 8px;
}

.stat-subtitle {
    font-size: 12px;
    opacity: 0.9;
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
}

/* Department List */
/* .department-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
} */

.department-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.department-item:last-child {
    border-bottom: none;
}

.department-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Status List */
.status-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.status-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.status-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Margin utilities */
.mt-4 {
    margin-top: 16px;
}
</style>
