<script setup>
import { NInput, NSelect, NSkeleton } from "naive-ui";
import { computed } from "vue";

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
</script>
<template>
    <div class="mb-4">
        <div class="flex items-center gap-4">
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
                <!-- ✅ Tampilkan skeleton saat loading -->
                <!-- <n-skeleton
                    v-if="loadingOptions"
                    width="100%"
                    height="40px"
                    sharp
                /> -->
                <!-- ✅ Tampilkan select jika data sudah siap -->
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
    </div>
</template>
