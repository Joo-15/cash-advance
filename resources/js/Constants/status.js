export const STATUS = {
    PENDING: "pending",
    APPROVED: "approved",
    REJECTED: "rejected"
}

export const STATUS_OPTIONS = [
    { label: "Semua Status", value: null },
    { label: "Pending", value: STATUS.PENDING },
    { label: "Disetujui", value: STATUS.APPROVED },
    { label: "Ditolak", value: STATUS.REJECTED }
]