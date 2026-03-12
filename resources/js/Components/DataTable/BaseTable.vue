<!-- Components/DataTable/BaseTable.vue -->
<script setup>
import { computed, ref, watch } from "vue";
import { NButton, NIcon, NTag } from "naive-ui";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";
import { RefreshOutline } from "@vicons/ionicons5";

// Props - TERIMA semua dari parent
const props = defineProps({
    columns: { type: Array, required: true }, // Columns SUDAH diproses
    dataRef: { type: Array, required: true },
    meta: { type: Object, required: true },
    filters: { type: Object, required: true },
    loadingReset: { type: Boolean, default: false },
    loadingRef: { type: Boolean, default: false },
    actions: { type: Object, default: () => ({}) },
    pageSize: { type: Number, default: 10 },
    showActiveFilters: { type: Boolean, default: true },
    statusOptions: { type: Array, default: () => [] },

    // Props baru dari parent - fungsi dari composable
    hasActiveSortFn: { type: Function, required: true },
    resetSortFn: { type: Function, required: true },
});

const emit = defineEmits([
    "update:page",
    "update:pageSize",
    "update:sorter",
    "clear-filter",
]);

// Key untuk memaksa re-render tabel (optional)
const tableKey = ref(0);

// HAPUS panggilan useTableColumns di sini!
// Langsung gunakan props.columns yang sudah diproses

const handleSortChange = (sortOptions) => {
    // Emit ke parent, parent yang akan handle update di composable
    emit("update:sorter", sortOptions);
};

const handleClear = () => {
    props.resetSortFn(); // Panggil resetSort dari parent
    emit("clear-filter");
};

const getStatusLabel = (value) => {
    const option = props.statusOptions.find((opt) => opt.value === value);
    return option?.label || value;
};

// // Watch untuk debugging (optional)
// watch(
//     () => props.filters,
//     () => {
//         // Force re-render jika perlu
//         tableKey.value += 1;
//     },
//     { deep: true },
// );
//
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm">
        <!-- Active Filters -->
        <div
            v-if="
                showActiveFilters &&
                (filters.search || filters.status || hasActiveSortFn())
            "
            class="mb-4 flex flex-wrap gap-2"
        >
            <n-tag
                v-if="filters.search"
                type="info"
                closable
                @close="filters.search = ''"
            >
                Search: {{ filters.search }}
            </n-tag>
            <n-tag
                v-if="filters.status"
                type="info"
                closable
                @close="filters.status = null"
            >
                Status: {{ getStatusLabel(filters.status) }}
            </n-tag>
            <n-tag
                v-if="hasActiveSortFn()"
                type="warning"
                closable
                @close="resetSortFn()"
            >
                Sort Active
            </n-tag>
            <n-button size="small" ghost @click="handleClear" strong>
                <template #icon>
                    <n-icon><refresh-outline /></n-icon>
                </template>
                Reset All
            </n-button>
        </div>

        <inertia-data-table
            :key="tableKey"
            :columns="columns"
            :data="dataRef"
            :meta="meta"
            :page-size="pageSize"
            :loading="loadingRef"
            @update:page="$emit('update:page', $event)"
            @update:pageSize="$emit('update:pageSize', $event)"
            @update:sorter="handleSortChange"
        />
    </div>
</template>
