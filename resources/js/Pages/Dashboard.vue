<script setup>
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";

const stats = ref([
    {
        label: "Total Pengguna",
        value: "2,543",
        change: "+12.5%",
        trend: "up",
    },
    {
        label: "Pendapatan",
        value: "Rp 45.2M",
        change: "+8.2%",
        trend: "up",
    },
    {
        label: "Pesanan Baru",
        value: "156",
        change: "+23.1%",
        trend: "up",
    },
    {
        label: "Produk Terjual",
        value: "892",
        change: "-3.4%",
        trend: "down",
    },
]);

const recentActivities = ref([
    {
        user: "Ahmad Rizki",
        action: "melakukan pembelian",
        time: "5 menit lalu",
    },
    {
        user: "Siti Nurhaliza",
        action: "mendaftar akun baru",
        time: "12 menit lalu",
    },
    { user: "Budi Santoso", action: "mengupdate profil", time: "1 jam lalu" },
    { user: "Dewi Lestari", action: "menambahkan review", time: "2 jam lalu" },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                v-for="(stat, index) in stats"
                :key="index"
                class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200"
            >
                <div class="flex items-start justify-between mb-3">
                    <p class="text-sm text-gray-600 font-medium">
                        {{ stat.label }}
                    </p>
                    <div
                        :class="[
                            'w-9 h-9 rounded-lg flex items-center justify-center',
                            index === 0
                                ? 'bg-indigo-50'
                                : index === 1
                                  ? 'bg-purple-50'
                                  : index === 2
                                    ? 'bg-pink-50'
                                    : 'bg-blue-50',
                        ]"
                    >
                        <svg
                            class="w-5 h-5 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                v-if="index === 0"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                            />
                            <path
                                v-else-if="index === 1"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                            />
                            <path
                                v-else-if="index === 2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                            <path
                                v-else
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-3xl font-bold text-gray-900 mb-1">
                        {{ stat.value }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ stat.change }} Completed
                    </p>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart Card -->
            <div
                class="lg:col-span-2 bg-white rounded-lg p-6 border border-gray-100"
            >
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Statistik Penjualan
                </h2>
                <div
                    class="h-64 flex items-center justify-center bg-gray-50 rounded-lg"
                >
                    <p class="text-gray-500">Chart akan ditampilkan di sini</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Aktivitas Terbaru
                </h2>
                <div class="space-y-4">
                    <div
                        v-for="(activity, index) in recentActivities"
                        :key="index"
                        class="pb-4 border-b border-gray-100 last:border-0 last:pb-0"
                    >
                        <p class="text-sm font-medium text-gray-900">
                            {{ activity.user }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ activity.action }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ activity.time }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Aksi Cepat
                </h2>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        class="px-4 py-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium text-sm"
                    >
                        Tambah Produk
                    </button>
                    <button
                        class="px-4 py-3 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors font-medium text-sm"
                    >
                        Lihat Laporan
                    </button>
                    <button
                        class="px-4 py-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors font-medium text-sm"
                    >
                        Kelola User
                    </button>
                    <button
                        class="px-4 py-3 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition-colors font-medium text-sm"
                    >
                        Pengaturan
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div class="bg-white rounded-lg p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    Ringkasan Hari Ini
                </h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"
                            >Transaksi Berhasil</span
                        >
                        <span class="text-sm font-semibold text-gray-900"
                            >24</span
                        >
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"
                            >Transaksi Pending</span
                        >
                        <span class="text-sm font-semibold text-gray-900"
                            >8</span
                        >
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600"
                            >Produk Terjual</span
                        >
                        <span class="text-sm font-semibold text-gray-900"
                            >67</span
                        >
                    </div>
                    <div
                        class="flex justify-between items-center pt-3 border-t border-gray-100"
                    >
                        <span class="text-sm font-medium text-gray-900"
                            >Total Pendapatan</span
                        >
                        <span class="text-sm font-bold text-blue-600"
                            >Rp 12.4M</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
