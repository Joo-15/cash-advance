// Composables/useCrud.js
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useDialog } from "naive-ui";

export function useCrud(options = {}) {

    const {
        routePrefix = "",
        messages = {
            confirmTitle: "Konfirmasi",
            confirmContent: "Apakah Anda yakin ingin menghapus data ini?",
            confirmPositive: "Hapus",
            confirmNegative: "Batal",
            successDelete: "Data berhasil dihapus",
            errorDelete: "Gagal menghapus data",
            errorFetch: "Gagal mengambil data detail",
        },
        formRef,
    } = options;

    const dialog = useDialog();
    const modalForm = ref(false);
    const selectedRow = ref(null);

    const tambah = () => {
        selectedRow.value = null;
        modalForm.value = true;
        formRef.value?.resetForm();

    };

    const edit = async (row, fetchDetail = true) => {
        try {
            const data =
                fetchDetail && !row.detail
                    ? (await axios.get(route(`${routePrefix}.show`, row.id)))
                        .data
                    : row;

            selectedRow.value = { ...data };
            modalForm.value = true;
        } catch (error) {
            console.error("Error:", error);
            message.error(messages.errorFetch);
        } finally {
            // loading.value = false;
        }
    };

    const hapus = (id, options = {}) => {

        const urlParams = new URLSearchParams(window.location.search);
        const currentFilters = {
            search: urlParams.get('search') || '',
            status: urlParams.get('status') || null,
            page: urlParams.get('page') || 1,
            per_page: urlParams.get('per_page') || 10,
            sort: urlParams.get('sort') || null,
            order: urlParams.get('order') || null,
        };

        const {
            onSuccess,
            onError,
            onFinish,
            preserveState = true,
            preserveScroll = true,
        } = options;

        dialog.warning({
            title: messages.confirmTitle,
            content: messages.confirmContent,
            positiveText: messages.confirmPositive,
            negativeText: messages.confirmNegative,
            onPositiveClick: () => {
                router.delete(route(`${routePrefix}.destroy`, id), {
                    preserveScroll,
                    preserveState,
                    data: currentFilters,
                    onSuccess: async () => {
                        // Flash message sudah otomatis ditampilkan oleh watchEffect
                        if (onSuccess) onSuccess();
                    },
                    onError: async (errors) => {
                        // Untuk validation errors (bukan flash messages)
                        message.error(messages.errorDelete);
                        if (onError) onError(errors);
                    },
                    onFinish: () => {
                        if (onFinish) onFinish();
                    },
                });
            },
        });
    };

    const refresh = (only = []) => {
        router.reload({ only });

    };

    return {
        // State
        modalForm,
        selectedRow,

        // Methods
        tambah,
        edit,
        hapus,
        refresh,
    };
}
