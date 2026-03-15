<script setup>
import { NButton, NInput, NSelect, NSpace, NIcon } from "naive-ui";
import { Add, DownloadOutline } from "@vicons/ionicons5";
import {
    DocumentOutline,
    CheckmarkOutline,
    TimeOutline,
    CloseOutline,
} from "@vicons/ionicons5";
import StatsCard from "./StatsCard.vue";

// Props dari parent
const props = defineProps({
    title: { type: String, required: true },
    stats: { type: Array, default: () => [] },
    statData: { type: Object, default: () => ({}) },
    filters: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
    loadingSearch: { type: Boolean, default: false },
    hasActiveSort: { type: Boolean, default: false }, // Props dari parent
    showDownload: { type: Boolean, default: true },
    showAdd: { type: Boolean, default: true },
    addButtonText: { type: String, default: "Tambah Data" },
    downloadButtonText: { type: String, default: "Download Excel" },
});

// Emit events ke parent
const emit = defineEmits(["update:search", "update:status", "add", "download"]);

// Mapping icon string ke komponen icon
const iconMap = {
    document: DocumentOutline,
    check: CheckmarkOutline,
    clock: TimeOutline,
    close: CloseOutline,
};

// Mapping warna
const colorMap = {
    blue: { bg: "bg-blue-50", icon: "text-blue-600" },
    green: { bg: "bg-green-50", icon: "text-green-600" },
    orange: { bg: "bg-orange-50", icon: "text-orange-600" },
    red: { bg: "bg-red-50", icon: "text-red-600" },
};

const getIcon = (iconName) => iconMap[iconName] || null;
const getColor = (colorName) =>
    colorMap[colorName] || { bg: "bg-gray-50", icon: "text-gray-600" };
</script>

<template>
    <div class="min-h-screen">
        <!-- Header -->
        <div
            class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between py-2"
        >
            <h1 class="text-2xl font-bold text-gray-800 p-2">
                {{ title }}
            </h1>

            <n-space wrap>
                <n-button
                    v-if="showDownload"
                    ghost
                    type="primary"
                    @click="$emit('download')"
                >
                    <template #icon>
                        <n-icon><DownloadOutline /></n-icon>
                    </template>
                    {{ downloadButtonText }}
                </n-button>

                <n-button v-if="showAdd" color="#8a2be2" @click="$emit('add')">
                    <template #icon>
                        <n-icon><Add /></n-icon>
                    </template>
                    {{ addButtonText }}
                </n-button>
            </n-space>
        </div>

        <!-- Stats Cards -->
        <div
            v-if="stats.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
        >
            <div
                v-for="stat in stats"
                :key="stat.key"
                class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition"
            >
                <StatsCard :stat="stat" :value="statData?.[stat.key] ?? 0" />
            </div>
        </div>

        <!-- Filter Section -->
        <div class="shadow-sm mb-4">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <n-input
                        :value="filters.search"
                        placeholder="Cari..."
                        clearable
                        :loading="loadingSearch"
                        @update:value="$emit('update:search', $event)"
                    />
                </div>

                <div v-if="statusOptions.length > 0" class="w-56">
                    <n-select
                        :value="filters.status"
                        :options="statusOptions"
                        placeholder="Filter Status"
                        clearable
                        @update:value="$emit('update:status', $event)"
                    />
                </div>
            </div>
        </div>

        <!-- Slot untuk konten tambahan -->
        <slot name="before-table" />

        <!-- Table Slot -->
        <slot name="table" />

        <!-- Slot untuk konten setelah tabel -->
        <slot name="after-table" />
    </div>
</template>
