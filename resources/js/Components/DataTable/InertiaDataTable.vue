<script setup>
import { NDataTable, NPagination } from "naive-ui";

/* =====================
 | Props
 ===================== */
defineProps({
    columns: {
        type: Array,
        required: true,
    },
    data: {
        type: Array,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
    pageSize: {
        type: Number,
        required: true,
    },
});

/* =====================
 | Emits
 ===================== */
defineEmits(["update:page", "update:pageSize"]);
</script>

<template>
    <div>
        <!-- =====================
             Data Table
        ===================== -->
        <n-data-table
            remote
            bordered
            :columns="columns"
            :data="data"
            :pagination="false"
        />

        <!-- =====================
             Footer
        ===================== -->
        <div
            class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
            <!-- Info -->
            <div class="text-sm text-gray-600">
                Menampilkan
                <b>{{ meta.from }}</b> – <b>{{ meta.to }}</b> dari
                <b>{{ meta.total }}</b> data
            </div>

            <!-- Pagination -->
            <n-pagination
                :page="meta.current_page"
                :page-size="pageSize"
                :item-count="meta.total"
                show-size-picker
                show-quick-jumper
                :page-sizes="[5, 10, 20, 50, 100]"
                @update:page="$emit('update:page', $event)"
                @update:page-size="$emit('update:pageSize', $event)"
            />
        </div>
    </div>
</template>
