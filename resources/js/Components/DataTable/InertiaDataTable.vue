<script setup>
import { NDataTable, NPagination } from "naive-ui";

const props = defineProps({
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
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["update:page", "update:pageSize", "update:sorter"]);

const handleSorterChange = (sorter) => {
    console.log("Sorter changed:", sorter);

    let sortOptions = null;
    if (sorter && sorter.columnKey && sorter.order) {
        sortOptions = {
            field: sorter.columnKey,
            order: sorter.order === "ascend" ? "asc" : "desc",
        };
    }
    emit("update:sorter", sortOptions);
};

const handlePageChange = (page) => emit("update:page", page);
const handlePageSizeChange = (pageSize) => emit("update:pageSize", pageSize);
</script>

<template>
    <div>
        <n-data-table
            remote
            bordered
            :columns="columns"
            :loading="loading"
            :data="data"
            :pagination="false"
            @update:sorter="handleSorterChange"
        />

        <div
            class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
            <div class="text-sm text-gray-600">
                Menampilkan <b>{{ meta.from }}</b> – <b>{{ meta.to }}</b> dari
                <b>{{ meta.total }}</b> data
            </div>

            <n-pagination
                :page="meta.current_page"
                :page-size="pageSize"
                :item-count="meta.total"
                show-size-picker
                show-quick-jumper
                :page-sizes="[5, 10, 20, 50, 100]"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            />
        </div>
    </div>
</template>
