import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { useMessage } from "naive-ui";

export function useFormSubmit(options = {}) {
    const {
        errorMessage = "Terjadi kesalahan",
        preserveScroll = true,
        preserveState = true,
        sleepTime = 500,
    } = options;

    const message = useMessage();
    const loadingButton = ref(false);

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

    // Generic submit function
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
            if (sleepTime > 0) {
                await sleep(sleepTime);
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
                    if (typeof onSuccess === "function") {
                        onSuccess(response);
                    }
                },
                onError: (errors) => {
                    console.error("Submit error:", errors);

                    // Handle validation errors
                    if (errors && typeof errors === "object") {
                        const firstError = Object.values(errors)[0];
                        message.error(firstError || errorMessage);
                    } else {
                        message.error(errorMessage);
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
            message.error(errorMessage);
            loadingButton.value = false;
            if (onError) onError(error);
        }
    };

    return {
        loadingButton,
        submit,
        getCurrentFilters,
    };
}
