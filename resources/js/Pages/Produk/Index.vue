<script setup>
/* =====================
 | Imports
 ===================== */
import { ref, h } from "vue";
import { router } from "@inertiajs/vue3";
import { NButton, NInput, useMessage, useDialog } from "naive-ui";

import { useInertiaDataTable } from "@/Composables/useInertiaDataTable";
import InertiaDataTable from "@/Components/DataTable/InertiaDataTable.vue";

/* =====================
 | Props (Inertia)
 ===================== */
const props = defineProps({
    produks: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

/* =====================
 | DataTable Composable
 ===================== */
// NOTE:
// - route() dipanggil di Page (BENAR)
// - composable hanya menerima URL, tidak tahu Ziggy
const { search, pageSize, handlePageChange, handlePageSizeChange } =
    useInertiaDataTable({
        route: route("produk.index"),
        initialSearch: props.filters.search ?? "",
        initialPageSize: Number(props.produks.per_page ?? 10),
        only: ["produks"],
    });

/* =====================
 | Local State
 ===================== */
const form = ref({
    nama: "",
    harga: "",
});

const dialog = useDialog();
const message = useMessage();

/* =====================
 | Table Columns
 ===================== */
const columns = [
    {
        title: "Nama",
        key: "nama",
    },
    {
        title: "Harga",
        key: "harga",
    },
    {
        title: "Aksi",
        key: "aksi",
        render(row) {
            return h(
                NButton,
                {
                    type: "error",
                    size: "small",
                    onClick: () => hapus(row.id),
                },
                { default: () => "Hapus" },
            );
        },
    },
];

/* =====================
 | Actions
 ===================== */
const hapus = (id) => {
    dialog.warning({
        title: "Konfirmasi",
        content: "Hapus data?",
        positiveText: "Hapus",
        negativeText: "Batal",
        onPositiveClick: () => {
            router.delete(route("produk.destroy", id), {
                preserveScroll: true,
            });
        },
    });
};

const submitForm = () => {
    if (!form.value.nama || !form.value.harga) {
        message.warning("Nama dan harga harus diisi");
        return;
    }

    router.post(route("produk.store"), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            message.success("Produk berhasil ditambahkan");
            form.value = { nama: "", harga: "" };
        },
    });
};
</script>

<template>
    <div class="p-4">
        <!-- =====================
             Header
        ===================== -->
        <h1 class="text-xl font-bold mb-4">Produk</h1>

        <!-- =====================
             Form Tambah Produk
        ===================== -->
        <div class="mb-6 flex flex-wrap gap-2 items-end">
            <n-input
                v-model:value="form.nama"
                placeholder="Nama produk"
                style="width: 200px"
            />
            <n-input
                v-model:value="form.harga"
                type="number"
                placeholder="Harga"
                style="width: 120px"
            />
            <n-button type="primary" @click="submitForm"> Tambah </n-button>
        </div>

        <!-- =====================
             Search
        ===================== -->
        <div class="mb-4 w-64">
            <n-input
                v-model:value="search"
                placeholder="Cari produk..."
                clearable
            />
        </div>

        <!-- =====================
             Data Table
        ===================== -->
        <inertia-data-table
            :columns="columns"
            :data="produks.data"
            :meta="produks"
            :page-size="pageSize"
            @update:page="handlePageChange"
            @update:pageSize="handlePageSizeChange"
        />
    </div>
</template>
