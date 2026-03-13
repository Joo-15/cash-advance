import { ref, computed } from "vue";
import { useForm, useField } from "vee-validate";
import { useMessage } from "naive-ui";
import { router } from "@inertiajs/vue3";
import { sleep } from "@/utils/helpers";

/**
 * Composable reusable untuk menangani form dengan VeeValidate
 * @param {Object} options - Konfigurasi form
 * @param {Object} options.validationRules - Rules validasi dari vee-validate
 * @param {Object} options.initialValues - Nilai awal form
 * @param {Object} options.props - Props dari komponen (harus berisi showModal & dataEdit)
 * @param {Function} options.emit - Emit function dari komponen
 * @param {Object} options.routes - Routes untuk store dan update
 * @param {Function} options.transformSubmitData - Function untuk transform data sebelum submit
 * @param {Function} options.transformEditData - Function untuk transform data saat edit
 * @param {Object} options.messages - Custom messages (opsional)
 */
export function useFormHandler({
    validationRules,
    initialValues,
    props,
    emit,
    routes,
    transformSubmitData = (data) => data,
    transformEditData = (data) => data,
    messages = {
        success: {
            create: "Data berhasil disimpan",
            update: "Data berhasil diupdate",
        },
        error: {
            validation: "Validasi gagal. Periksa kembali form Anda",
            submit: "Gagal menyimpan data",
        },
    },
}) {
    const message = useMessage();
    const loadingButton = ref(false);

    // Mode edit
    const isEditMode = computed(() => !!props.dataEdit?.id);

    // Setup form dengan VeeValidate
    const form = useForm({
        validationRules,
        initialValues: props.dataEdit?.id
            ? transformEditData(props.dataEdit, initialValues)
            : initialValues,
    });

    const { handleSubmit, setValues, resetForm, errors, setErrors, values } =
        form;

    // ============ Helper Methods ============
    const getError = (field) => errors.value[field] || null;
    const hasError = (field) => !!errors.value[field];

    const scrollToFirstError = () => {
        setTimeout(() => {
            const firstError = document.querySelector(".has-error");
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
        }, 100);
    };

    // ============ Reset Form ============
    const resetFormHandler = () => {
        resetForm({ values: initialValues });
        setErrors({});
    };

    // ============ Set Edit Data ============
    const setEditData = (data) => {
        if (data?.id) {
            setValues(transformEditData(data, initialValues));
        } else {
            resetFormHandler();
        }
    };

    // ============ Submit Handler ============
    const onSubmit = handleSubmit(
        async (formValues) => {
            loadingButton.value = true;
            await sleep(500);

            try {
                const submitData = transformSubmitData(
                    formValues,
                    isEditMode.value,
                );

                const options = {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        message.success(
                            isEditMode.value
                                ? messages.success.update
                                : messages.success.create,
                        );
                        emit("updated");
                        emit("update:showModal", false);
                    },
                    onError: (serverErrors) => {
                        setErrors(serverErrors);
                        message.error(messages.error.validation);
                        scrollToFirstError();
                    },
                    onFinish: () => {
                        loadingButton.value = false;
                    },
                };

                if (isEditMode.value) {
                    await router.put(
                        routes.update(props.dataEdit.id),
                        submitData,
                        options,
                    );
                } else {
                    await router.post(routes.store, submitData, options);
                }
            } catch (error) {
                console.error("Submit error:", error);
                message.error(messages.error.submit);
                loadingButton.value = false;
            }
        },
        () => {
            message.error(messages.error.validation);
            scrollToFirstError();
        },
    );

    // ============ Close Modal ============
    const closeModal = () => {
        emit("update:showModal", false);
        if (!isEditMode.value) {
            resetFormHandler();
        }
    };

    return {
        // State
        loadingButton,
        isEditMode,
        errors,
        values,
        form,

        // Methods
        onSubmit,
        getError,
        hasError,
        resetForm: resetFormHandler,
        setEditData,
        closeModal,
        setValues,

        // Form instance
        ...form,
    };
}

/**
 * Helper untuk membuat field bindings
 */
export function useFormField(fieldName, formHandler) {
    const { value } = useField(fieldName);
    return {
        value,
        error: computed(() => formHandler.getError(fieldName)),
        hasError: computed(() => formHandler.hasError(fieldName)),
    };
}
