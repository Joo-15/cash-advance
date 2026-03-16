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
            errorSubmit: "Terjadi kesalahan saat menyimpan data",
        },
        formRef,
        preserveScroll = true,
        preserveState = true,
        submitSleepTime = 500,
    } = options;

    const dialog = useDialog();
    const message = useMessage();

    // State
    const modalForm = ref(false);
    const selectedRow = ref(null);
    const loadingButton = ref(false);
    const loadingFetch = ref(false);

    // Helper untuk mengambil filters dari URL
    const getCurrentFilters = () => {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            search: urlParams.get("search") || "",
            status: urlParams.get("status") || null,
            page: urlParams.get("page") || 1,
            per_page: urlParams.get("per_page") || 10,
            sort: urlParams.get("sort") || null,
            order: urlParams.get("order") || null,
        };
    };

    // Helper untuk sleep
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    // Method CRUD
    const tambah = () => {
        selectedRow.value = null;
        modalForm.value = true;
        formRef.value?.resetForm();
    };

    const edit = async (row, fetchDetail = true) => {
        try {
            loadingFetch.value = true;
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
            loadingFetch.value = false;
        }
    };

    const hapus = (id, options = {}) => {
        const currentFilters = getCurrentFilters();

        const {
            onSuccess,
            onError,
            onFinish,
            preserveState: customPreserveState = preserveState,
            preserveScroll: customPreserveScroll = preserveScroll,
        } = options;

        dialog.warning({
            title: messages.confirmTitle,
            content: messages.confirmContent,
            positiveText: messages.confirmPositive,
            negativeText: messages.confirmNegative,
            onPositiveClick: () => {
                router.delete(route(`${routePrefix}.destroy`, id), {
                    preserveScroll: customPreserveScroll,
                    preserveState: customPreserveState,
                    data: currentFilters,
                    onSuccess: async () => {
                        if (onSuccess) onSuccess();
                    },
                    onError: async (errors) => {
                        if (onError) onError(errors);
                    },
                    onFinish: () => {
                        if (onFinish) onFinish();
                    },
                });
            },
        });
    };

    // Method Form Submit
    const submit = async ({
        values,
        method,
        url,
        id = null,
        onSuccess,
        onError,
        onFinish,
        customFilters = {},
        successMessage = "Data berhasil disimpan",
    }) => {
        loadingButton.value = true;

        try {
            if (submitSleepTime > 0) {
                await sleep(submitSleepTime);
            }

            // Ambil filters dari URL
            const currentFilters = getCurrentFilters();

            // Gabungkan data
            const submitData = {
                ...values,
                ...currentFilters,
                ...customFilters,
            };

            // Tentukan method dan URL
            let requestUrl = null;
            if (id) {
                requestUrl = route(url, id);
            } else {
                requestUrl = route(url);
            }

            const options = {
                preserveScroll,
                preserveState,
                onSuccess: (response) => {
                    modalForm.value = false;

                    if (typeof onSuccess === "function") {
                        onSuccess(response);
                    }
                },
                onError: (errors) => {
                    console.error("Submit error:", errors);

                    // Handle validation errors
                    if (errors && typeof errors === "object") {
                        const firstError = Object.values(errors)[0];
                        message.error(firstError || messages.errorSubmit);
                    } else {
                        message.error(messages.errorSubmit);
                    }

                    if (onError) onError(errors);
                },
                onFinish: () => {
                    loadingButton.value = false;
                    if (onFinish) onFinish();
                },
            };

            // Execute request
            if (method === "put" || method === "patch") {
                router.put(requestUrl, submitData, options);
            } else if (method === "post") {
                router.post(requestUrl, submitData, options);
            } else if (method === "delete") {
                router.delete(requestUrl, {
                    ...options,
                    data: submitData,
                });
            }
        } catch (error) {
            console.error("Submit error:", error);

            loadingButton.value = false;
            if (onError) onError(error);
        }
    };

    // Method Utility
    const refresh = (only = []) => {
        router.reload({ only });
    };

    const closeModal = () => {
        modalForm.value = false;
        selectedRow.value = null;
    };

    return {
        // State
        modalForm,
        selectedRow,
        loadingButton,
        loadingFetch,

        // Methods CRUD
        tambah,
        edit,
        hapus,

        // Methods Form Submit
        submit,

        // Methods Utility
        refresh,
        closeModal,
        getCurrentFilters,
    };
}
