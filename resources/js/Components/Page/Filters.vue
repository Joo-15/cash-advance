<script setup>
import { NInput, NSelect } from "naive-ui";

// Props dari parent
const props = defineProps({
    placeholder: { type: String, default: "Search.." },
    filters: { type: Object, required: true },
    showSearch: { type: Boolean, required: false },
    showSelect: { type: Boolean, required: false },
    selectOptions: { type: Array, default: () => [] },
    loadingSearch: { type: Boolean, default: false },
});

// Emit events ke parent
const emit = defineEmits(["update:search", "update:status"]);
</script>
<template>
    <div class="shadow-sm mb-4">
        <div class="flex items-center gap-4">
            <div class="flex-1" v-if="showSearch">
                <n-input
                    :value="filters.search"
                    :placeholder="placeholder"
                    clearable
                    :loading="loadingSearch"
                    @update:value="$emit('update:search', $event)"
                />
            </div>

            <div v-if="showSelect" class="w-56">
                <n-select
                    :value="filters.status"
                    :options="selectOptions"
                    placeholder="Filter Status"
                    clearable
                    @update:value="$emit('update:status', $event)"
                />
            </div>
        </div>
    </div>
</template>
