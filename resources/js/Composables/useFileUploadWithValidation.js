// composables/useFileUploadWithValidation.js
import { watch, ref } from "vue";
import { useFileUpload } from "./useFileUpload";

export function useFileUploadWithValidation(options = {}) {
    const {
        fieldName = "attachment",
        required = true,
        setFieldValue = null,
        validateField = null,
        setFieldError = null,
        ...uploadOptions
    } = options;

    const fileUpload = useFileUpload(uploadOptions);
    const validationError = ref("");
    const isTouched = ref(false);

    // Validate current files
    const validate = () => {
        if (required && fileUpload.getFileCount() === 0) {
            validationError.value = "Dokumen bukti wajib diisi";
            if (setFieldError) {
                setFieldError(fieldName, validationError.value);
            }
            return false;
        }

        if (fileUpload.uploadError.value) {
            validationError.value = fileUpload.uploadError.value;
            if (setFieldError) {
                setFieldError(fieldName, validationError.value);
            }
            return false;
        }

        validationError.value = "";
        if (setFieldError) {
            setFieldError(fieldName, undefined);
        }
        return true;
    };

    // Set touched on first interaction
    const setTouched = () => {
        isTouched.value = true;
        validate();
    };

    // Clear validation error
    const clearValidationError = () => {
        validationError.value = "";
        if (setFieldError) {
            setFieldError(fieldName, undefined);
        }
    };

    // Handle file change with validation integration
    const handleFileChange = (data) => {
        const added = fileUpload.handleChange(data);
        if (added && setFieldValue) {
            // Set the first file as the value for vee-validate
            if (fileUpload.fileList.value.length > 0) {
                setFieldValue(fieldName, fileUpload.fileList.value[0].file);
            }
            if (validateField) {
                validateField(fieldName);
            }
        }
        setTouched();
        return added;
    };

    // Handle remove file with validation integration
    const handleRemoveFile = (index) => {
        fileUpload.removeFile(index);
        if (setFieldValue) {
            setFieldValue(
                fieldName,
                fileUpload.fileList.value.length > 0
                    ? fileUpload.fileList.value[0].file
                    : null,
            );
        }
        setTouched();
        if (validateField) {
            validateField(fieldName);
        }
    };

    // Reset all
    const reset = () => {
        fileUpload.removeAllFiles();
        validationError.value = "";
        isTouched.value = false;
        if (setFieldValue) {
            setFieldValue(fieldName, null);
        }
        if (setFieldError) {
            setFieldError(fieldName, undefined);
        }
    };

    // Watch for upload errors
    watch(
        () => fileUpload.uploadError.value,
        (newError) => {
            if (newError && isTouched.value) {
                validationError.value = newError;
                if (setFieldError) {
                    setFieldError(fieldName, newError);
                }
            }
        },
    );

    // Initialize from existing file (for edit mode)
    const initializeFromExisting = (existingFile) => {
        if (existingFile && typeof existingFile === "string") {
            // This is a URL/path, not an actual File object
            // We'll just store the URL for display
            fileUpload.pdfUrl.value = existingFile;
        } else if (existingFile && existingFile instanceof File) {
            fileUpload.addFile(existingFile);
            if (setFieldValue) {
                setFieldValue(fieldName, existingFile);
            }
        }
    };

    return {
        // From useFileUpload
        ...fileUpload,

        // Additional state
        validationError,
        isTouched,

        // Additional methods
        validate,
        setTouched,
        clearValidationError,
        handleFileChange,
        handleRemoveFile,
        reset,
        initializeFromExisting,
    };
}
