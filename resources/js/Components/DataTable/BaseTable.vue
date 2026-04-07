<!-- Components/DataTable/BaseTable.vue -->
<script setup>
import { NButton, NIcon, NTag } from "naive-ui";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";
import { RefreshOutline } from "@vicons/ionicons5";
import { onMounted, ref } from "vue";

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

const animatedTable = ref(false);

const getStatusLabel = (value) => {
    const option = props.selectOptions.find((opt) => opt.value === value);
    return option?.label || value;
};

onMounted(() => {
    setTimeout(() => {
        animatedTable.value = true;
    }, 0);
});
</script>

<template>
    <div
        class="transform transition-all duration-500"
        :class="
            animatedTable
                ? 'translate-y-0 opacity-100'
                : 'translate-y-10 opacity-0'
        "
    >
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
                <n-button
                    size="small"
                    ghost
                    @click="emit('clear-filter')"
                    strong
                >
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
    </div>
</template>

<style scoped>
.base-table-container {
    @apply w-full;
}

/* Table row hover animation */
:deep(.n-data-table tr) {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

:deep(.n-data-table tr:hover) {
    background: linear-gradient(
        90deg,
        rgba(59, 130, 246, 0.05) 0%,
        rgba(147, 51, 234, 0.05) 100%
    );
    transform: translateX(4px);
}
</style>
