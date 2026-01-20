<script setup>
import { ref, h, watch, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import {
    NDataTable,
    NPagination,
    NButton,
    NInput,
    useMessage,
    useDialog,
} from "naive-ui";
import { debounce } from "lodash";

const props = defineProps({
    produks: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const pageSize = ref(Number(props.produks.per_page || 10));
const form = ref({ nama: "", harga: "" });
const dialog = useDialog();

const message = useMessage(); // <-- ini yang benar sekarang

const columns = [
    { title: "Nama", key: "nama" },
    { title: "Harga", key: "harga" },
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

    router.post("/produk", form.value, {
        preserveScroll: true,
        onSuccess: () => {
            message.success("Produk berhasil ditambahkan");
            form.value = { nama: "", harga: "" };
        },
        onError: () => {
            message.error("Terjadi kesalahan");
        },
    });
};

// Debounce search
const doSearch = debounce((value) => {
    router.get(
        "/produk",
        { search: value, page: 1, per_page: pageSize.value },
        { preserveState: true, replace: true, only: ["produks"] },
    );
}, 300);

const handlePageChange = (page) => {
    router.get(
        "/produk",
        { page, search: search.value, per_page: pageSize.value },
        { preserveState: true, preserveScroll: true },
    );
};

const handlePageSizeChange = (size) => {
    pageSize.value = size;
    router.get(
        "/produk",
        { page: 1, search: search.value, per_page: size },
        {
            preserveState: true,
            onFinish: () => {
                document
                    .querySelector(".n-data-table")
                    ?.scrollIntoView({ behavior: "smooth" });
            },
        },
    );
};

watch(search, (value) => doSearch(value));

onBeforeUnmount(() => doSearch.cancel());
</script>

<template>
    <div class="p-4">
        <h1 class="text-xl font-bold mb-4">Produk</h1>

        <!-- Form tambah data -->
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

        <!-- Search -->
        <div class="mb-4 w-64">
            <n-input
                v-model:value="search"
                placeholder="Cari produk..."
                clearable
            />
        </div>

        <!-- Table -->
        <n-data-table
            remote
            bordered
            :columns="columns"
            :data="produks.data"
            :pagination="false"
        />

        <!-- Footer -->
        <div
            class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
            <!-- Info -->
            <div class="text-sm text-gray-600">
                Menampilkan
                <b>{{ produks.from }}</b> – <b>{{ produks.to }}</b> dari
                <b>{{ produks.total }}</b> data
            </div>

            <!-- Pagination -->
            <n-pagination
                :page="produks.current_page"
                :page-size="pageSize"
                :item-count="produks.total"
                show-size-picker
                show-quick-jumper
                :page-sizes="[5, 10, 20, 50, 100]"
                @update:page="handlePageChange"
                @update:page-size="handlePageSizeChange"
            />
        </div>
    </div>
</template>
