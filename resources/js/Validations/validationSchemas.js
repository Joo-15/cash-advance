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

export const userSchema = yup.object({
    name: yup.string().required("Nama harus diisi"),
    email: yup.string().email("Email tidak valid").required("Email harus diisi"),
    password: yup
        .string()
        .nullable()
        .when('id', {
            is: (id) => !id,  // Saat create (id null)
            then: (schema) => schema.required("Password harus diisi"),
            otherwise: (schema) => schema.nullable()  // Saat edit, tidak wajib
        })
        .min(6, "Password minimal 6 karakter"),
    department_id: yup.number().required("Department harus dipilih"),

});
