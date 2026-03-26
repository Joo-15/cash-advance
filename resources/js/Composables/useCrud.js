// Composables/useCrud.js
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useDialog, useMessage } from "naive-ui";

export function useCrud(options = {}) {
    const {
        routePrefix = "",
        routeDetail = "",
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
    const currentFormType = ref(null);
    const modalMode = ref(null);
    const selectedApproval = ref(null);
    const approvalStep = ref(null);
    const loadingButton = ref(false);
    const loadingFetch = ref(false);

    // Helper untuk mengambil filters dari URL
    const getCurrentFilters = () => {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            search: urlParams.get("search") || "",
            // status: urlParams.get("status") || null,
            page: urlParams.get("page") || 1,
            per_page: urlParams.get("per_page") || 10,
            sort: urlParams.get("sort") || null,
            order: urlParams.get("order") || null,
        };
    };

    // Helper untuk sleep
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    // Method CRUD
    const tambah = (formType, mode = 'create') => {
        console.log('Tambah dipanggil:', { formType, mode });

        currentFormType.value = formType;
        modalMode.value = mode;
        selectedRow.value = null;
        modalForm.value = true;
        formRef.value?.resetForm();
    };

    const edit = async (formType, mode = 'edit', row, fetchDetail = true) => {
        currentFormType.value = formType;
        modalMode.value = mode;

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

    const proses = async (formType, mode = 'bayar', id) => {
        console.log('Tambah dipanggil:', { formType, mode });

        currentFormType.value = formType;
        modalMode.value = mode;

        try {
            // loadingDetail.value = true

            // Panggil endpoint getDetail
            const response = await axios.get(route(`${routePrefix}.show`, id))

            // Cek response
            if (response.data.success) {
                selectedRow.value = response.data.data
                console.log('data response proses', selectedRow)
                modalForm.value = true // Buka modal setelah data didapat

            } else {
                message.error('Gagal mengambil data detail')
            }
        } catch (error) {
            console.error('Error fetching detail:', error)

            // Tampilkan pesan error
            if (error.response) {
                // Server merespons dengan status code error
                message.error(`Error: ${error.response.status} - ${error.response.data.message || 'Gagal mengambil data'}`)
            } else if (error.request) {
                // Request dibuat tapi tidak ada respons
                message.error('Tidak ada respons dari server')
            } else {
                // Error lainnya
                message.error('Terjadi kesalahan: ' + error.message)
            }
        } finally {
            // loadingDetail.value = false
        }
    }

    // FUNGSI DETAIL MENGGUNAKAN AXIOS
    const fetchDetail = async (formType, mode = 'create', id) => {
        console.log('Tambah dipanggil:', { formType, mode });

        currentFormType.value = formType;
        modalMode.value = mode;

        try {
            // loadingDetail.value = true

            // Panggil endpoint getDetail
            const response = await axios.get(route(`${routeDetail}.detail`, id))

            // Cek response
            if (response.data.success) {
                selectedApproval.value = response.data.data
                approvalStep.value = response.data.approvalStep

                modalForm.value = true // Buka modal setelah data didapat
                console.log('test', selectedApproval.value);
            } else {
                message.error('Gagal mengambil data detail')
            }
        } catch (error) {
            console.error('Error fetching detail:', error)

            // Tampilkan pesan error
            if (error.response) {
                // Server merespons dengan status code error
                message.error(`Error: ${error.response.status} - ${error.response.data.message || 'Gagal mengambil data'}`)
            } else if (error.request) {
                // Request dibuat tapi tidak ada respons
                message.error('Tidak ada respons dari server')
            } else {
                // Error lainnya
                message.error('Terjadi kesalahan: ' + error.message)
            }
        } finally {
            // loadingDetail.value = false
        }
    }

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
    }) => {
        loadingButton.value = true;

        try {
            if (submitSleepTime > 0) {
                await sleep(submitSleepTime);
            }

            // Ambil filters dari URL
            const currentFilters = getCurrentFilters();

            console.log("📦 values:", values);
            console.log("📦 currentFilters:", currentFilters);
            console.log("📦 customFilters:", customFilters);

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
        currentFormType,
        modalMode,
        selectedRow,
        selectedApproval,
        approvalStep,
        loadingButton,
        loadingFetch,

        // Methods CRUD
        tambah,
        edit,
        hapus,
        fetchDetail,
        proses,

        // Methods Form Submit
        submit,

        // Methods Utility
        refresh,
        closeModal,
        getCurrentFilters,
    };
}
