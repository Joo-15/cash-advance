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

// Computed untuk validasi value
// const getSafeDepartmentValue = computed(() => {
//     // Jika masih loading atau options kosong
//     if (props.loadingOptions || !props.departmentOptions?.length) {
//         return null;
//     }

//     const value = props.filters.department;
//     if (!value && value !== 0) return null;

//     // Konversi ke number untuk perbandingan
//     const numericValue = Number(value);

//     // Cek apakah value valid
//     const isValid = props.departmentOptions.some(
//         (opt) => opt.value === numericValue,
//     );

//     return isValid ? numericValue : null;
// });

// console.log("get", getSafeDepartmentValue);

// Handler untuk department change
// const handleDepartmentChange = (value) => {
//     emit("update:department", value ? Number(value) : null);
// };
</script>
<template>
    <div class="shadow-sm mb-4">
        <div class="flex items-center gap-4">
            <!-- Search Input -->
            <div class="flex-1" v-if="showSearch">
                <n-input
                    size="large"
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
                    size="large"
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
                    size="large"
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
