export const STATUS = {
    PENDING: "pending",
    APPROVED: "approved",
    REJECTED: "rejected",
    DISBURSED: "disbursed",
}

export const STATUS_OPTIONS = [
    { label: "Semua Status", value: null },
    { label: "Pending", value: STATUS.PENDING },
    { label: "Approved", value: STATUS.APPROVED },
    { label: "Rejected", value: STATUS.REJECTED }
]

export const STATUS_OPTIONS_PENCAIRAN = [
    { label: "Semua Status", value: null },
    { label: "Approved", value: STATUS.APPROVED },
    { label: "Disbursed", value: STATUS.DISBURSED },

]