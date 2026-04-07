<script setup>
import { NInput, NSelect } from "naive-ui";
import { onMounted, ref } from "vue";

const props = defineProps({
    placeholder: { type: String, default: "Cari data disini.." },
    filters: { type: Object, required: true },
    showSearch: { type: Boolean, default: false },
    showStatus: { type: Boolean, default: false },
    showDepartment: { type: Boolean, default: false },
    departmentOptions: { type: Array, default: () => [] },
    statusOptions: { type: Array, default: () => [] },
    loadingSearch: { type: Boolean, default: false },
    loadingOptions: { type: Boolean, default: false },
});

const emit = defineEmits([
    "update:search",
    "update:status",
    "update:department",
]);

const animatedFilters = ref(false);

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
