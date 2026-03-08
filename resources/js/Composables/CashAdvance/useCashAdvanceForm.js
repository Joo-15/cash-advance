import { reactive } from "vue"

export function useCashAdvanceForm(initialData = {}) {
    const form = reactive({
        tanggal: initialData.tanggal || "",
        keperluan: initialData.keperluan || "",
        jumlah: initialData.jumlah || "",
    })

    return { form }
}