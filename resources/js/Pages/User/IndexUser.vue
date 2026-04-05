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
import FormUser from "./FormUser.vue";
import { useDepartment } from "@/Composables/useCollection";

// Props definition
const props = defineProps({
    users: { type: Object, required: true },
    departments: Array,
    roles: Array,
    filters: { type: Object, default: () => ({}) },
    statData: Object,
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
        department: props.filters.department
            ? Number(props.filters.department)
            : null,
        pageSize: Number(props.users.per_page ?? 10),
        sort: props.filters.sort || null,
        order: props.filters.order || null,
    },
    only: ["users"],
    debounceTime: 300, // Tambahkan debounce time
});

const { loading: loadingDepartments, departments } = useDepartment({});

const departmentOptions = computed(() => departments.value || []);

// Table data transformation
const rows = computed(() =>
    props.users.data.map((row) => ({ ...row, detail: true })),
);

// Column configuration
const columnConfig = [
    {
        title: "Nama Lengkap",
        key: "name",
        width: 120,
        align: "left",
        sorter: false,
    },
    {
        title: "Username",
        key: "username",
        width: 120,
        align: "center",
        sorter: false,
    },
    {
        title: "Email",
        key: "email",
        width: 120,
        align: "left",
        sorter: false,
    },
    {
        title: "Department",
        key: "department.name",
        width: 120,
        align: "center",
        sorter: false, // Aktifkan sorting
    },
    {
        title: "Role",
        key: "role.name",
        width: 120,
        align: "center",
        sorter: false, // Aktifkan sorting
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
    onEdit: (row) => edit("user", "edit", row),
    onDelete: hapus,
};

// Table columns
const tableColumns = computed(() => createColumns(columnConfig, actions));
</script>

<template>
    <Container>
        <template #header>
            <PageHeader
                add-button-text="Tambah Pengguna"
                :title="page.props.pageHeader ?? 'Pengguna'"
                :show-add="true"
                :show-download="false"
                @add="tambah('user', 'create')"
            ></PageHeader>
        </template>
        <template #filters>
            <Filters
                :loading-options="loadingDepartments"
                :filters="filters"
                :show-search="true"
                :show-department="true"
                :department-options="departmentOptions"
                :loading-search="loadingSearch"
                @update:search="filters.search = $event"
                @update:department="filters.department = $event"
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
                v-if="currentFormType === 'user'"
                v-model:show-modal="modalForm"
                edit-title="Edit Pengguna"
                create-title="Tambah Pengguna"
                :data-edit="selectedRow"
            >
                <!-- terima closeModal dari slot -->
                <template #form="{ closeModal }">
                    <FormUser
                        v-if="currentFormType === 'user'"
                        :modal-mode="modalMode"
                        :loading="loadingButton"
                        :departments-options="departments"
                        :roles-options="roles"
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
