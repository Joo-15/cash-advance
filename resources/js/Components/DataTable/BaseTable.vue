<!-- Components/DataTable/BaseTable.vue -->
<script setup>
import { NButton, NIcon, NTag } from "naive-ui";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";
import { RefreshOutline } from "@vicons/ionicons5";

// Props - TERIMA semua dari parent
const props = defineProps({
    columns: { type: Array, required: true },
    dataRef: { type: Array, required: true },
    meta: { type: Object, required: true },
    filters: { type: Object, required: true },
    loadingReset: { type: Boolean, default: false },
    loadingRef: { type: Boolean, default: false },
    actions: { type: Object, default: () => ({}) },
    pageSize: { type: Number, default: 10 },
    showActiveFilters: { type: Boolean, default: true },
    selectOptions: { type: Array, default: () => [] },

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

const getStatusLabel = (value) => {
    const option = props.selectOptions.find((opt) => opt.value === value);
    return option?.label || value;
};
</script>

<template>
    <!-- ✅ SATU ROOT ELEMENT - wrapper div -->
    <div class="base-table-container">
        <!-- Active Filters -->
        <div
            v-if="
                filters.search ||
                filters.status ||
                filters.department ||
                hasActiveSortFn()
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
                v-if="filters.department"
                type="info"
                closable
                @close="filters.department = null"
            >
                Departemen
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
            <n-button size="small" ghost @click="emit('clear-filter')" strong>
                <template #icon>
                    <n-icon><refresh-outline /></n-icon>
                </template>
                Reset All
            </n-button>
        </div>

        <!-- Data Table -->
        <inertia-data-table
            :columns="columns"
            :data="dataRef"
            :meta="meta"
            :page-size="pageSize"
            :loading="loadingRef"
            @update:page="$emit('update:page', $event)"
            @update:pageSize="$emit('update:pageSize', $event)"
            @update:sorter="$emit('update:sorter', $event)"
        />
    </div>
</template>

<style scoped>
.base-table-container {
    @apply w-full;
}
</style>
