<script setup>
import { computed, ref, watch } from "vue";
import { NBadge, NTag } from "naive-ui";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";
import { useTableColumns } from "@/Composables/useTableColumns";

const props = defineProps({
    columns: { type: Array, required: true },
    dataRef: { type: Array, required: true },
    meta: { type: Object, required: true },
    filters: { type: Object, required: true },
    loadingRef: { type: Boolean, default: false },
    actions: { type: Object, default: () => ({}) },
    pageSize: { type: Number, default: 10 },
    showActiveFilters: { type: Boolean, default: true },
    statusOptions: { type: Array, default: () => [] },
});

const emit = defineEmits([
    "update:page",
    "update:pageSize",
    "update:sorter",
    "clear-filter",
    "reset-sort",
]);

// Ambil fungsi dan state dari composable
const {
    createColumns,
    resetSort,
    hasActiveSort,
    updateSort,
    activeSortKey, // ✅ activeSortKey sekarang sudah tersedia
} = useTableColumns();

// Key untuk memaksa re-render tabel
const tableKey = ref(0);

const tableColumns = computed(() =>
    createColumns(props.columns, props.actions),
);

const handleSortChange = (sortOptions) => {
    if (sortOptions?.field) {
        const order = sortOptions.order === "asc" ? "ascend" : "descend";
        updateSort(sortOptions.field, order);
    } else {
        resetSort();
    }
    emit("update:sorter", sortOptions);
};

const handleClear = () => {
    resetSort();
    emit("clear-filter");
};

const getStatusLabel = (value) => {
    const option = props.statusOptions.find((opt) => opt.value === value);
    return option?.label || value;
};

watch(
    () => activeSortKey.value,
    (newVal, oldVal) => {
        console.log("🟣 BaseTable - activeSortKey berubah:", {
            dari: oldVal,
            ke: newVal,
        });
        tableKey.value += 1;
        console.log("🟣 BaseTable - tableKey:", tableKey.value);
    },
);
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm">
        <!-- Active Filters -->
        <div
            v-if="
                showActiveFilters &&
                (filters.search || filters.status || hasActiveSort())
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
                v-if="hasActiveSort()"
                type="warning"
                closable
                @close="resetSort()"
            >
                Sort Active
            </n-tag>
        </div>

        <inertia-data-table
            :key="tableKey"
            :columns="tableColumns"
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
