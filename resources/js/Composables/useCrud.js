// Composables/useCrud.js
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useDialog, useMessage } from "naive-ui";

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
    } = options;

    const dialog = useDialog();
    const message = useMessage();
    const modal = ref(false);
    const selectedRow = ref(null);
    const loading = ref(false);

    const tambah = () => {
        selectedRow.value = null;
        modal.value = true;
    };

    const edit = async (row, fetchDetail = true) => {
        try {
            loading.value = true;
            const data = fetchDetail && !row.detail
                ? (await axios.get(route(`${routePrefix}.show`, row.id))).data
                : row;

            selectedRow.value = { ...data };
            modal.value = true;
        } catch (error) {
            console.error("Error:", error);
            message.error(messages.errorFetch);
        } finally {
            loading.value = false;
        }
    };

    const hapus = (id, options = {}) => {
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
                    onSuccess: async () => {
                        message.success(messages.successDelete);
                        if (onSuccess) onSuccess();
                    },
                    onError: async (errors) => {
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
        modal,
        selectedRow,
        loading,

        // Methods
        tambah,
        edit,
        hapus,
        refresh,
    };
}