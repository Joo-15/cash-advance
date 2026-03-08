export const cashAdvanceRules = {
    keperluan: {
        required: true,
        message: "Keperluan wajib diisi",
        trigger: ["blur", "input"],
    },
    jumlah: {
        required: true,
        message: "Jumlah wajib diisi",
        trigger: ["blur", "input"],
    },
};