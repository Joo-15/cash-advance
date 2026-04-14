export const STATUS = {
    PENDING: "pending",
    APPROVED: "approved",
    REJECTED: "rejected",
    DISBURSED: "disbursed",
};

export const STATUS_OPTIONS = [
    { label: "Semua Status", value: null },
    { label: "Menunggu", value: STATUS.PENDING },
    { label: "Disetujui", value: STATUS.APPROVED },
    { label: "Ditolak", value: STATUS.REJECTED },
];

export const STATUS_OPTIONS_PENCAIRAN = [
    { label: "Semua Status", value: null },
    { label: "Disetujui", value: STATUS.APPROVED },
    { label: "Dicairkan", value: STATUS.DISBURSED },
];
