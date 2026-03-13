import * as yup from "yup";

export const cashAdvanceSchema = yup.object({
    tanggal: yup.number().required("Tanggal wajib diisi"),

    keperluan: yup
        .string()
        .required("Keperluan wajib diisi")
        .min(3, "Minimal 3 karakter"),

    jumlah: yup
        .number()
        .typeError("Jumlah harus berupa angka")
        .required("Jumlah wajib diisi")
        .min(1, "Jumlah minimal 1"),
});
