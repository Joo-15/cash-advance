<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Generate Tanda Terima</h3>

        <!-- Form Sederhana -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1"
                    >Nomor Transaksi</label
                >
                <NInput
                    v-model:value="transactionNumber"
                    placeholder="Masukkan nomor transaksi"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1"
                    >Nama Peminjam</label
                >
                <NInput
                    v-model:value="borrowerName"
                    placeholder="Nama peminjam"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1"
                    >Jumlah Pinjaman</label
                >
                <NInputNumber
                    v-model:value="amount"
                    placeholder="Jumlah pinjaman"
                    :min="0"
                    class="w-full"
                >
                    <template #prefix>Rp</template>
                </NInputNumber>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex gap-2 pt-4">
                <NButton @click="resetForm">Reset</NButton>
                <NButton
                    type="primary"
                    @click="generateAndPrint"
                    :loading="loading"
                >
                    <template #icon>
                        <NIcon><PrinterOutline /></NIcon>
                    </template>
                    Generate & Cetak
                </NButton>
                <NButton
                    type="success"
                    @click="generateAndDownload"
                    :loading="downloading"
                >
                    <template #icon>
                        <NIcon><DownloadOutline /></NIcon>
                    </template>
                    Download PDF
                </NButton>
            </div>
        </div>

        <!-- Preview PDF -->
        <NDrawer v-model:show="showPreview" placement="bottom" height="80%">
            <NDrawerContent title="Preview Tanda Terima" closable>
                <div class="h-full">
                    <iframe
                        :src="previewUrl"
                        class="w-full h-[calc(100%-60px)] border-0"
                    ></iframe>
                </div>
                <template #footer>
                    <div class="flex justify-end gap-2">
                        <NButton @click="showPreview = false">Tutup</NButton>
                        <NButton type="primary" @click="downloadFromPreview">
                            Download
                        </NButton>
                    </div>
                </template>
            </NDrawerContent>
        </NDrawer>
    </div>
</template>

<script setup>
import { ref } from "vue";
import {
    NButton,
    NIcon,
    NInput,
    NInputNumber,
    NDrawer,
    NDrawerContent,
    useMessage,
} from "naive-ui";

const message = useMessage();
const loading = ref(false);
const downloading = ref(false);
const showPreview = ref(false);
const previewUrl = ref("");

// Form data
const transactionNumber = ref("");
const borrowerName = ref("");
const amount = ref(null);

const resetForm = () => {
    transactionNumber.value = "";
    borrowerName.value = "";
    amount.value = null;
};

const generateAndPrint = async () => {
    if (!validateForm()) return;

    loading.value = true;

    try {
        const response = await axios.post(
            "/api/generate-receipt",
            {
                transaction_number: transactionNumber.value,
                borrower_name: borrowerName.value,
                amount: amount.value,
            },
            {
                responseType: "blob",
            },
        );

        const url = URL.createObjectURL(response.data);
        window.open(url, "_blank");

        message.success("Dokumen siap dicetak");
    } catch (error) {
        message.error("Gagal generate dokumen");
    } finally {
        loading.value = false;
    }
};

const generateAndDownload = async () => {
    if (!validateForm()) return;

    downloading.value = true;

    try {
        const response = await axios.post(
            "/api/generate-receipt",
            {
                transaction_number: transactionNumber.value,
                borrower_name: borrowerName.value,
                amount: amount.value,
            },
            {
                responseType: "blob",
            },
        );

        const url = URL.createObjectURL(response.data);
        const link = document.createElement("a");
        link.href = url;
        link.download = `tanda_terima_${transactionNumber.value || Date.now()}.pdf`;
        link.click();

        message.success("PDF berhasil diunduh");

        // Preview
        previewUrl.value = url;
        showPreview.value = true;
    } catch (error) {
        message.error("Gagal generate dokumen");
    } finally {
        downloading.value = false;
    }
};

const downloadFromPreview = () => {
    const link = document.createElement("a");
    link.href = previewUrl.value;
    link.download = `tanda_terima_${transactionNumber.value || Date.now()}.pdf`;
    link.click();
};

const validateForm = () => {
    if (!transactionNumber.value) {
        message.warning("Nomor transaksi harus diisi");
        return false;
    }
    if (!borrowerName.value) {
        message.warning("Nama peminjam harus diisi");
        return false;
    }
    if (!amount.value || amount.value <= 0) {
        message.warning("Jumlah pinjaman harus diisi");
        return false;
    }
    return true;
};
</script>
