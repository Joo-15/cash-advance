export const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

export const formatRupiah = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};