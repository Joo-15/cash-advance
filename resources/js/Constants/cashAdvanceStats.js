export const CASH_ADVANCE_STATS = [
    {
        key: "total_pengajuan",
        label: "Total Pengajuan",
        color: "blue",
        icon: "document",
        subLabel: "semua status",
        tagType: "info",        // n-tag type: default | info | success | warning | error
    },
    {
        key: "pengajuan_disetujui",
        label: "Pengajuan Disetujui",
        color: "green",
        icon: "check",
        subLabel: "approved",
        tagType: "success",
    },
    {
        key: "pengajuan_pending",
        label: "Menunggu Approval",
        color: "orange",
        icon: "clock",
        subLabel: "pending",
        tagType: "warning",
    },
    {
        key: "pengajuan_ditolak",
        label: "Pengajuan Ditolak",
        color: "red",
        icon: "close",
        subLabel: "rejected",
        tagType: "error",
    },
]