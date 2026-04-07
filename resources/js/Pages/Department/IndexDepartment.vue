<!-- Pages/CashAdvance/IndexCashAdvance.vue -->
<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

// Composables
import { useDataTable } from "@/Composables/useDataTable";
import { useCrud } from "@/Composables/useCrud";

// Constants

// Components
import BaseTable from "@/Components/DataTable/BaseTable.vue";
import Container from "@/Components/Layout/Container.vue";
import PageHeader from "@/Components/Page/PageHeader.vue";
import Filters from "@/Components/Page/Filters.vue";
import ModalForm from "@/Components/Page/ModalForm.vue";

import FormDepartment from "./FormDepartment.vue";

// Props definition
const props = defineProps({
    departments: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

// Composables initialization
const page = usePage();

// Refs
const formRef = ref(null);

const {
    loadingButton,
    modalForm,
    currentFormType,
    modalMode,
    selectedRow,
    tambah,
    edit,
    hapus,
    refresh,
    submit,
} = useCrud({
    routePrefix: "departments",
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
    route: route("departments.index"),
    filters: {
        search: props.filters.search || "",
        department: props.filters.department
            ? Number(props.filters.department)
            : null,
        pageSize: Number(props.departments.per_page ?? 10),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["departments"],
    debounceTime: 300, // Tambahkan debounce time
});

// Table data transformation
const rows = computed(() => {
    const currentPage = props.departments.current_page || 1;
    const perPage = props.departments.per_page || 10;
    const startIndex = (currentPage - 1) * perPage;

    return props.departments.data.map((row, idx) => ({
        ...row,
        detail: true,
        rowNumber: startIndex + idx + 1,
    }));
});

// Column configuration
const columnConfig = [
    {
        title: "No",
        key: "rowNumber", // Gunakan key dari data
        width: 80,
        align: "center",
        sorter: false,
    },
    {
        title: "Nama Departemen",
        key: "name",
        // width: 120,
        align: "left",
        sorter: false,
    },

    {
        title: "Aksi",
        key: "actions",
        type: "action",
        // width: 120,
        fixed: "right",
        align: "center",
        actionConfig: {
            showEdit: true,
            showDelete: true,
            size: "small",
        },
        sorter: false,
    },
];

// Actions configuration
const actions = {
    onEdit: (row) => edit("department", "edit", row),
    onDelete: hapus,
};

const modalTitle = computed(() => {
    if (modalMode.value === "edit") {
        return "Edit Departemen";
    }

    return "Tambah Departemen";
});
console.log("selectedRow", selectedRow);

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Tambah Departemen"
                title="Data Departemen"
                :show-add="true"
                :show-download="false"
                @add="tambah('department', 'create')"
            ></PageHeader>
        </template>
        <template #filters>
            <Filters
                :filters="filters"
                :show-search="true"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
            ></Filters>
        </template>
        <template #content>
            <BaseTable
                :columns="tableColumns"
                :data-ref="rows"
                :meta="departments"
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
                :title="modalTitle"
                :is-detail-mode="currentFormType === 'department'"
                :data-edit="selectedRow"
                :auto-focus="false"
            >
                <!-- terima closeModal dari slot -->
                <template #form="{ closeModal }">
                    <FormDepartment
                        v-if="currentFormType === 'department'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :data-edit="selectedRow"
                        :close-modal="closeModal"
                        :submit="submit"
                        @updated="refresh"
                    />
                </template>
            </ModalForm>
        </template>
    </Container>
</template>
