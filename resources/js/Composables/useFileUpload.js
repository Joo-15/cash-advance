// composables/useFileUpload.js
import { ref } from "vue";

export function useFileUpload(options = {}) {
    const {
        maxFiles = 1,
        acceptedTypes = ["application/pdf"],
        maxSize = 2 * 1024 * 1024, // 2MB
        autoClearError = true,
    } = options;

    // State
    const fileList = ref([]);
    const pdfUrl = ref(null);
    const uploadError = ref("");
    const isUploading = ref(false);
    const uploadProgress = ref(0);

    // Helper Functions
    const formatFileSize = (bytes) => {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB", "GB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    };

    const getFileExtension = (filename) => {
        return filename.slice(((filename.lastIndexOf(".") - 1) >>> 0) + 2);
    };

    const isFileTypeAccepted = (file) => {
        if (acceptedTypes.length === 0) return true;

        // Check by MIME type
        if (acceptedTypes.includes(file.type)) return true;

        // Check by extension
        const extension = getFileExtension(file.name).toLowerCase();
        const acceptedExtensions = acceptedTypes
            .filter((type) => type.startsWith("."))
            .map((type) => type.toLowerCase());

        if (acceptedExtensions.includes(`.${extension}`)) return true;

        return false;
    };

    const validateFile = (file) => {
        // Check file type
        if (!isFileTypeAccepted(file)) {
            const acceptedExtensions = acceptedTypes
                .map((type) => type.replace("application/", "").toUpperCase())
                .join(", ");
            return {
                valid: false,
                error: `Tipe file tidak didukung. Format yang diterima: ${acceptedExtensions}`,
            };
        }

        // Check file size
        if (file.size > maxSize) {
            return {
                valid: false,
                error: `Ukuran file melebihi batas maksimal (${formatFileSize(maxSize)})`,
            };
        }

        return { valid: true, error: null };
    };

    const clearError = () => {
        uploadError.value = "";
    };

    const setError = (error) => {
        uploadError.value = error;
    };

    // File Management
    const addFile = (file, customMetadata = {}) => {
        const validation = validateFile(file);

        if (!validation.valid) {
            uploadError.value = validation.error;
            return false;
        }

        if (fileList.value.length >= maxFiles) {
            uploadError.value = `Maksimal upload ${maxFiles} file`;
            return false;
        }

        const fileItem = {
            id: Date.now() + Math.random(),
            file: file,
            name: file.name,
            size: file.size,
            type: file.type,
            progress: 0,
            status: "pending",
            url: URL.createObjectURL(file),
            metadata: customMetadata,
            uploadedAt: new Date(),
        };

        fileList.value.push(fileItem);

        if (autoClearError) {
            clearError();
        }

        return true;
    };

    const removeFile = (index) => {
        if (index >= 0 && index < fileList.value.length) {
            const fileToRemove = fileList.value[index];
            if (fileToRemove && fileToRemove.url) {
                URL.revokeObjectURL(fileToRemove.url);
            }
            fileList.value.splice(index, 1);
        }

        if (fileList.value.length === 0) {
            clearError();
            pdfUrl.value = null;
        }
    };

    const removeAllFiles = () => {
        fileList.value.forEach((file) => {
            if (file.url) {
                URL.revokeObjectURL(file.url);
            }
        });
        fileList.value = [];
        pdfUrl.value = null;
        clearError();
    };

    const getFileUrl = (index = 0) => {
        if (fileList.value[index] && fileList.value[index].url) {
            return fileList.value[index].url;
        }
        return null;
    };

    const getFileName = (index = 0) => {
        if (fileList.value[index]) {
            return fileList.value[index].name;
        }
        return null;
    };

    const getFileSize = (index = 0) => {
        if (fileList.value[index]) {
            return formatFileSize(fileList.value[index].size);
        }
        return null;
    };

    // For Naive UI Upload component
    const beforeUpload = (data) => {
        const file = data.file?.file || data.file;
        const validation = validateFile(file);

        if (!validation.valid) {
            uploadError.value = validation.error;
            return false;
        }

        clearError();
        return true;
    };

    const handleChange = (data, onFileAdd = null) => {
        const fileItem = data.file;
        const file = fileItem?.file;

        if (!file) return;

        const added = addFile(file);

        if (added && onFileAdd) {
            onFileAdd(file);
        }

        return added;
    };

    const updatePdfUrl = () => {
        if (fileList.value.length > 0 && fileList.value[0].url) {
            pdfUrl.value = fileList.value[0].url;
        } else {
            pdfUrl.value = null;
        }
    };

    // Create FormData for upload
    const createFormData = (fieldName = "files") => {
        const formData = new FormData();

        fileList.value.forEach((fileItem, index) => {
            formData.append(`${fieldName}[${index}]`, fileItem.file);

            // Add metadata if exists
            if (Object.keys(fileItem.metadata).length > 0) {
                formData.append(
                    `${fieldName}_metadata[${index}]`,
                    JSON.stringify(fileItem.metadata),
                );
            }
        });

        return formData;
    };

    // Simulate upload (for demo, replace with actual upload function)
    const uploadFiles = async (uploadFunction, onProgress = null) => {
        if (fileList.value.length === 0) {
            uploadError.value = "Tidak ada file yang diupload";
            return false;
        }

        isUploading.value = true;
        uploadProgress.value = 0;

        const formData = createFormData();

        try {
            const result = await uploadFunction(formData, (progressEvent) => {
                if (progressEvent.total) {
                    const progress = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                    uploadProgress.value = progress;

                    if (onProgress) {
                        onProgress(progress);
                    }

                    // Update individual file progress
                    fileList.value.forEach((file) => {
                        file.progress = progress;
                    });
                }
            });

            // Mark all files as uploaded
            fileList.value.forEach((file) => {
                file.status = "uploaded";
            });

            return result;
        } catch (error) {
            uploadError.value = error.message || "Gagal mengupload file";
            fileList.value.forEach((file) => {
                file.status = "error";
            });
            return false;
        } finally {
            isUploading.value = false;
            uploadProgress.value = 0;
        }
    };

    // Getters
    const isValid = () => {
        return fileList.value.length > 0 && !uploadError.value;
    };

    const getFileCount = () => fileList.value.length;

    const canUploadMore = () => fileList.value.length < maxFiles;

    const getTotalSize = () => {
        return fileList.value.reduce((total, file) => {
            return total + (file.file?.size || file.size || 0);
        }, 0);
    };

    const getFormattedTotalSize = () => formatFileSize(getTotalSize());

    return {
        // State
        fileList,
        pdfUrl,
        uploadError,
        isUploading,
        uploadProgress,

        // Methods
        addFile,
        removeFile,
        removeAllFiles,
        getFileUrl,
        getFileName,
        getFileSize,
        beforeUpload,
        handleChange,
        updatePdfUrl,
        createFormData,
        uploadFiles,
        clearError,
        setError,
        validateFile,
        formatFileSize,

        // Getters
        isValid,
        getFileCount,
        canUploadMore,
        getTotalSize,
        getFormattedTotalSize,
    };
}
