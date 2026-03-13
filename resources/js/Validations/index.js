import { defineRule } from "vee-validate";

// ============ Global Rules Registration ============
// Required rule
defineRule("required", (value) => {
    if (!value && value !== 0) return "Field ini wajib diisi";
    return true;
});

// Min length rule
defineRule("minLength", (value, [min]) => {
    if (!value) return true;
    if (String(value).length < min) {
        return `Minimal ${min} karakter`;
    }
    return true;
});

// Max length rule
defineRule("maxLength", (value, [max]) => {
    if (!value) return true;
    if (String(value).length > max) {
        return `Maksimal ${max} karakter`;
    }
    return true;
});

// Numeric rule
defineRule("numeric", (value) => {
    if (!value) return true;
    if (isNaN(value)) return "Harus berupa angka";
    return true;
});

// Positive number rule
defineRule("positive", (value) => {
    if (!value) return true;
    if (Number(value) <= 0) return "Harus bernilai positif";
    return true;
});

// Min value rule
defineRule("minValue", (value, [min]) => {
    if (!value) return true;
    if (Number(value) < min) {
        return `Minimal ${formatNumber(min)}`;
    }
    return true;
});

// Max value rule
defineRule("maxValue", (value, [max]) => {
    if (!value) return true;
    if (Number(value) > max) {
        return `Maksimal ${formatNumber(max)}`;
    }
    return true;
});

// Not future date rule
defineRule("notFutureDate", (value) => {
    if (!value) return true;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const inputDate = new Date(value);
    inputDate.setHours(0, 0, 0, 0);

    if (inputDate > today) {
        return "Tanggal tidak boleh melebihi hari ini";
    }
    return true;
});

// Email rule
defineRule("email", (value) => {
    if (!value) return true;
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(value)) {
        return "Format email tidak valid";
    }
    return true;
});

// Phone rule
defineRule("phone", (value) => {
    if (!value) return true;
    const regex = /^[0-9]{10,13}$/;
    if (!regex.test(value)) {
        return "Nomor telepon tidak valid (10-13 digit)";
    }
    return true;
});

// Helper format number
function formatNumber(value) {
    return new Intl.NumberFormat("id-ID").format(value);
}

// ============ Module Validations ============
export const validations = {
    // Cash Advance Module
    cashAdvance: {
        rules: {
            tanggal: "required|notFutureDate",
            keperluan: "required|minLength:5|maxLength:255",
            jumlah: "required|numeric|positive|minValue:10000|maxValue:100000000",
            keterangan: "maxLength:500",
        },
        initialValues: {
            tanggal: new Date().getTime(),
            keperluan: "",
            jumlah: "",
            keterangan: "",
        },
        routes: {
            store: () => route("pengajuan-pinjaman.store"),
            update: (id) => route("pengajuan-pinjaman.update", id),
        },
        transformSubmit: (data, isEditMode) => ({
            ...data,
            tanggal: new Date(data.tanggal).toISOString().split("T")[0],
            jumlah: Number(data.jumlah),
        }),
        transformEdit: (data, initialValues) => ({
            ...initialValues,
            ...data,
            tanggal: data.tanggal
                ? new Date(data.tanggal).getTime()
                : new Date().getTime(),
        }),
        messages: {
            success: {
                create: "Cash advance berhasil diajukan",
                update: "Cash advance berhasil diupdate",
            },
        },
    },

    // User Module
    user: {
        rules: {
            name: "required|minLength:3|maxLength:100",
            email: "required|email",
            phone: "phone",
            role: "required",
            is_active: "required",
        },
        initialValues: {
            name: "",
            email: "",
            phone: "",
            role: "user",
            is_active: true,
        },
        routes: {
            store: () => route("users.store"),
            update: (id) => route("users.update", id),
        },
        transformSubmit: (data) => data,
        transformEdit: (data, initialValues) => ({ ...initialValues, ...data }),
    },

    // Product Module
    product: {
        rules: {
            name: "required|minLength:3|maxLength:200",
            price: "required|numeric|positive|minValue:1000",
            stock: "required|numeric|positive|integer",
            description: "maxLength:1000",
            category_id: "required|numeric",
        },
        initialValues: {
            name: "",
            price: "",
            stock: "",
            description: "",
            category_id: null,
        },
        routes: {
            store: () => route("products.store"),
            update: (id) => route("products.update", id),
        },
        transformSubmit: (data) => ({
            ...data,
            price: Number(data.price),
            stock: Number(data.stock),
        }),
        transformEdit: (data, initialValues) => ({ ...initialValues, ...data }),
    },

    // Category Module
    category: {
        rules: {
            name: "required|minLength:2|maxLength:100",
            description: "maxLength:500",
        },
        initialValues: {
            name: "",
            description: "",
        },
        routes: {
            store: () => route("categories.store"),
            update: (id) => route("categories.update", id),
        },
        transformSubmit: (data) => data,
        transformEdit: (data, initialValues) => ({ ...initialValues, ...data }),
    },
};
