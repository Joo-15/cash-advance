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
    const tambah = (formType, mode = "create") => {
        currentFormType.value = formType;
        modalMode.value = mode;
        selectedRow.value = null;
        modalForm.value = true;
        formRef.value?.resetForm();
    };

    const edit = async (formType, mode = "edit", row, fetchDetail = true) => {
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

    const proses = async (formType, mode = "bayar", id) => {
        currentFormType.value = formType;
        modalMode.value = mode;

        try {
            // loadingDetail.value = true

            // Panggil endpoint getDetail
            const response = await axios.get(route(`${routePrefix}.show`, id));

            // Cek response
            if (response.data.success) {
                selectedRow.value = response.data.data;
                modalForm.value = true; // Buka modal setelah data didapat
            } else {
                message.error("Gagal mengambil data detail");
            }
        } catch (error) {
            console.error("Error fetching detail:", error);

            // Tampilkan pesan error
            if (error.response) {
                // Server merespons dengan status code error
                message.error(
                    `Error: ${error.response.status} - ${error.response.data.message || "Gagal mengambil data"}`,
                );
            } else if (error.request) {
                // Request dibuat tapi tidak ada respons
                message.error("Tidak ada respons dari server");
            } else {
                // Error lainnya
                message.error("Terjadi kesalahan: " + error.message);
            }
        } finally {
            // loadingDetail.value = false
        }
    };

    // FUNGSI DETAIL MENGGUNAKAN AXIOS
    const fetchDetail = async (formType, mode = "create", id) => {
        currentFormType.value = formType;
        modalMode.value = mode;

        try {
            // loadingDetail.value = true

            // Panggil endpoint getDetail
            const response = await axios.get(
                route(`${routeDetail}.detail`, id),
            );

            // Cek response
            if (response.data.success) {
                selectedApproval.value = response.data.data;
                approvalStep.value = response.data.approvalStep;

                modalForm.value = true; // Buka modal setelah data didapat
            } else {
                message.error("Gagal mengambil data detail");
            }
        } catch (error) {
            console.error("Error fetching detail:", error);

            // Tampilkan pesan error
            if (error.response) {
                // Server merespons dengan status code error
                message.error(
                    `Error: ${error.response.status} - ${error.response.data.message || "Gagal mengambil data"}`,
                );
            } else if (error.request) {
                // Request dibuat tapi tidak ada respons
                message.error("Tidak ada respons dari server");
            } else {
                // Error lainnya
                message.error("Terjadi kesalahan: " + error.message);
            }
        } finally {
            // loadingDetail.value = false
        }
    };
    const showPdfModal = ref(false);
    const pdfUrl = ref("");
    const currentReceiptId = ref(null);
    const isLoadingPdf = ref(false);

    const printReceipt = async (formType, mode = "print", id) => {
        currentFormType.value = formType;
        modalMode.value = mode;

        // Set loading true
        isLoadingPdf.value = true;

        try {
            const response = await axios.get(
                route(`${routePrefix}.receipt`, id),
                {
                    responseType: "blob",
                },
            );

            const blob = new Blob([response.data], { type: "application/pdf" });
            const url = URL.createObjectURL(blob);

            pdfUrl.value = url;
            currentReceiptId.value = id;
            showPdfModal.value = true;
        } catch (error) {
            console.error("Error printing receipt:", error);
            message.error("Gagal menampilkan tanda terima");
        } finally {
            setTimeout(() => {
                isLoadingPdf.value = false;
            }, 500);
        }
    };

    const closePdfModal = () => {
        showPdfModal.value = false;
        if (pdfUrl.value && pdfUrl.value.startsWith("blob:")) {
            URL.revokeObjectURL(pdfUrl.value);
        }
        pdfUrl.value = "";
        currentReceiptId.value = null;
    };

    const printPdf = () => {
        if (pdfUrl.value) {
            const iframe = document.createElement("iframe");
            iframe.style.position = "absolute";
            iframe.style.width = "0";
            iframe.style.height = "0";
            iframe.style.border = "none";
            document.body.appendChild(iframe);

            // Tunggu iframe benar-benar loaded
            iframe.onload = () => {
                // Beri sedikit delay untuk memastikan konten siap
                setTimeout(() => {
                    iframe.contentWindow.print();
                }, 500);

                // Hapus iframe setelah print dialog selesai
                const checkPrint = setInterval(() => {
                    if (iframe.contentWindow.document.hidden) {
                        clearInterval(checkPrint);
                        setTimeout(() => {
                            document.body.removeChild(iframe);
                        }, 1000);
                    }
                }, 500);
            };

            // Handle error jika gagal load
            iframe.onerror = () => {
                console.error("Failed to load PDF");
                document.body.removeChild(iframe);
            };

            iframe.src = pdfUrl.value;
        }
    };

    const isDownloading = ref(false);

    const downloadPdf = async () => {
        if (!pdfUrl.value) {
            message.warning("Tidak ada dokumen untuk didownload");
            return;
        }

        const loading = message.loading("Mempersiapkan download...", {
            duration: 0,
        });

        try {
            // Fetch ulang PDF untuk memastikan file lengkap
            const response = await fetch(pdfUrl.value);
            const blob = await response.blob();

            // Buat blob URL baru
            const blobUrl = URL.createObjectURL(blob);

            // Download
            const link = document.createElement("a");
            link.href = blobUrl;
            link.download = `tanda_terima_${currentReceiptId.value || Date.now()}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Cleanup
            URL.revokeObjectURL(blobUrl);
        } catch (error) {
            console.error("Download error:", error);
            loading.destroy();
            message.error("Gagal mendownload dokumen");
        } finally {
            setTimeout(() => {
                loading.destroy();
                isDownloading.value = false;
                message.success("Download berhasil");
            }, 500);
        }
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
    }) => {
        loadingButton.value = true;

        try {
            if (submitSleepTime > 0) {
                await sleep(submitSleepTime);
            }

            const currentFilters = getCurrentFilters();

            let submitData;

            // ✅ HANDLE FORM DATA
            if (values instanceof FormData) {
                submitData = values;

                // append filters ke FormData
                Object.entries({
                    ...currentFilters,
                    ...customFilters,
                }).forEach(([key, val]) => {
                    if (val !== null && val !== undefined) {
                        submitData.append(key, val);
                    }
                });
            } else {
                // ✅ HANDLE OBJECT BIASA
                submitData = {
                    ...values,
                    ...currentFilters,
                    ...customFilters,
                };
            }

            // URL
            const requestUrl = id ? route(url, id) : route(url);

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

            // 🚀 HANDLE METHOD + FILE UPLOAD
            if (method === "put" || method === "patch") {
                if (submitData instanceof FormData) {
                    submitData.append("_method", method.toUpperCase());

                    // 🔥 WAJIB pakai POST untuk file
                    router.post(requestUrl, submitData, options);
                } else {
                    router.put(requestUrl, submitData, options);
                }
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
        showPdfModal,
        pdfUrl,
        currentFormType,
        modalMode,
        selectedRow,
        selectedApproval,
        approvalStep,
        loadingButton,
        loadingFetch,
        isLoadingPdf,

        // Methods CRUD
        tambah,
        edit,
        hapus,
        fetchDetail,
        printReceipt,
        downloadPdf,
        printPdf,
        proses,

        // Methods Form Submit
        submit,

        // Methods Utility
        refresh,
        closeModal,
        closePdfModal,
        getCurrentFilters,
    };
}
