<!-- Pages/CashAdvance/IndexCashAdvance.vue -->
<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

// Composables
import { useCrud } from "@/Composables/useCrud";

// Constants
// import { STATUS_OPTIONS } from "@/Constants/status";

// Components
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import { useDataTable } from "@/Composables/useDataTable";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import Filters from "@/Components/Page/Filters.vue";

import FormUser from "./FormUser.vue";
import ModalForm from "@/Components/Page/ModalForm.vue";

// Props definition
const props = defineProps({
    users: { type: Object, required: true },
    departments: Array,
    filters: { type: Object, default: () => ({}) },
    statData: Object,
});
console.log(props.users);

// Composables initialization
const page = usePage();

// Refs
const formRef = ref(null);

// CRUD Operations
const { modalForm, selectedRow, tambah, edit, hapus, refresh } = useCrud({
    routePrefix: "users",
    formRef,
});

// DataTable setup
const {
    loadingSearch,
    loadingTable,
    filters,
    handlePageChange,
    handlePageSizeChange,
    handleSortChange, // Langsung dari composable
    handleClear,
    handleResetSort,

    // Column management functions
    createColumns,
    hasActiveSort,
} = useDataTable({
    route: route("users.index"),
    filters: {
        search: props.filters.search || "",
        status: props.filters.status || null,
        pageSize: Number(props.users.per_page ?? 10),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["users"],
    debounceTime: 300, // Tambahkan debounce time
    tableConfig: {
        currency: "IDR",
        dateFormat: "DD-MM-YYYY",
        actionSize: "small",
        ellipsisTooltip: true,
    },
});

// Table data transformation
const rows = computed(() =>
    props.users.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration
const columnConfig = [
    {
        title: "Username",
        key: "name",
        width: 120,
        sorter: true, // Aktifkan sorting
    },
    {
        title: "Email",
        key: "email",
        width: 120,
        sorter: true, // Aktifkan sorting
    },
    {
        title: "Department",
        key: "department.name",
        width: 120,
        sorter: true, // Aktifkan sorting
    },

    {
        title: "Aksi",
        key: "actions",
        type: "action",
        width: 120,
        fixed: "right",
        align: "center",
        actionConfig: {
            showEdit: true,
            showDelete: true,
            showView: true,
            size: "small",
        },
        sorter: false, // Aksi tidak perlu sorting
    },
];

// Actions configuration
const actions = {
    onEdit: edit,
    onDelete: hapus,
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));

const handleDownload = () => {
    console.log("Download Excel");
};
console.log("test", !!selectedRow.value);
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Tambah Pengguna"
                :title="page.props.pageHeader ?? 'Pengguna'"
                :show-add="true"
                :show-download="false"
                @add="tambah"
            ></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
                @update:status="filters.status = $event"
            ></Filters>
        </template>
        <template #content>
            <BaseTable
                :columns="tableColumns"
                :data-ref="rows"
                :meta="users"
                :filters="filters"
                :page-size="filters.pageSize"
                :loading-ref="loadingSearch || loadingTable"
                :has-active-sort-fn="hasActiveSort"
                :reset-sort-fn="handleResetSort"
                @update:page="handlePageChange"
                @update:pageSize="handlePageSizeChange"
                @update:sorter="handleSortChange"
                @clear-filter="handleClear"
            />
            <ModalForm
                v-model:show-modal="modalForm"
                edit-title="Edit Pengguna"
                create-title="Tambah Pengguna"
                :data-edit="selectedRow"
            >
                <!-- terima closeModal dari slot -->
                <template #form="{ closeModal }">
                    <FormUser
                        :departments-options="departments"
                        :data-edit="selectedRow"
                        :close-modal="closeModal"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
            <!-- <FormUser
                v-model:show-modal="modalForm"
                ref="formRef"
                :data-edit="selectedRow"
                @updated="refresh"
            /> -->
        </template>
    </Container>
</template>
