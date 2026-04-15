export const STATUS = {
    NOT_SUBMITTED: "not_submitted",
    SUBMITTED: "submitted",
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

export const STATUS_REPORT_OPTIONS = [
    { label: "Semua Status", value: null },
    { label: "Belum Dikirim", value: STATUS.NOT_SUBMITTED },
    { label: "Menunggu Review", value: STATUS.SUBMITTED },
    { label: "Disetujui", value: STATUS.APPROVED },
    { label: "Ditolak", value: STATUS.REJECTED },
];
