<script setup>
import { computed, ref } from "vue";

import {
    NCard,
    NTag,
    NGrid,
    NGridItem,
    NTimeline,
    NTimelineItem,
    NList,
    NListItem,
    NGi,
    NFormItem,
    NInput,
    NForm,
    NButton,
    NIcon,
    NCollapse,
    NCollapseItem,
    NAlert,
    NSpace,
} from "naive-ui";
import {
    CheckmarkCircleOutline,
    CloseCircleOutline,
    PersonOutline,
} from "@vicons/ionicons5";
import { formatDate, formatRupiah } from "@/utils/helpers";
import { useApprovalSteps } from "@/Composables/useApprovalSteps";

// Props & Emits
const props = defineProps({
    loading: Boolean,
    showModal: Boolean,
    dataDetail: Object,
    approvalStep: Number,
    roleName: String,
    userName: String,
    departmentName: String,
    closeModal: Function,
    submit: Function,
});

const value = ref("");
const approvals = props.dataDetail; // Array of approvals
const requestName = props.dataDetail[0]?.cash_advance?.user?.name || "Guest";
const departmentName =
    props.dataDetail[0]?.cash_advance?.user?.department?.name || "Guest";
const getLastStatus = approvals[approvals.length - 1];
const getStatusRequest = props.dataDetail[0]?.cash_advance?.status || "Guest";

const emit = defineEmits(["update:showModal", "updated"]);

const statusMap = {
    approved: "Approved",
    pending: "Pending",
    rejected: "Rejected",
};

const getTimelineType = (status) => {
    switch (status) {
        case "Approved":
            return "success";
        case "Pending":
            return "warning";
        case "Rejected":
            return "error";
        default:
            return "info";
    }
};

// Inisialisasi composable dengan reactive approvals
const { getByStepId, getPrevious, getNext, getStatus, canApprove } =
    useApprovalSteps(computed(() => approvals));

// Contoh penggunaan di computed
const currentStep = computed(() => getByStepId(props.approvalStep));
const currentStatus = computed(() => getStatus(props.approvalStep));
const canApproveCurrent = computed(() => canApprove(props.approvalStep));

const approvalSteps = computed(() => {
    if (!approvals || !Array.isArray(approvals)) return [];

    // Mapping data dari API
    const mappedSteps = approvals.map((approval, index) => {
        // Tentukan status berdasarkan data
        let status = "-";
        if (approval.status === "approved") {
            status = "Approved";
        } else if (approval.status === "pending") {
            status = "Pending";
        } else if (approval.status === "rejected") {
            status = "Rejected";
        }

        // Ambil nama-nama role dari approval_step_roles
        let roleNames = [];
        if (approval.approval_step?.approval_step_roles) {
            roleNames = approval.approval_step.approval_step_roles
                .map((roleItem) => roleItem.role?.name)
                .filter((name) => name);
        }

        // Buat title dari role names
        let title = "";
        if (roleNames.length > 0) {
            title = roleNames.join(" / ");
        } else {
            title = `Step ${approval.approval_step?.step_order || index + 1}`;
        }

        return {
            id: approval.id,
            title: title,
            step_order: approval.approval_step?.step_order || index + 1,
            status: status,
            date: approval.approved_at ? formatDate(approval.approved_at) : "",
            approved_by: approval.user?.name || null, // ✅ Sekarang terisi
            approved_by_id: approval.user?.id,
            approved_by_role: approval.user?.role?.name,
            approved_by_department: approval.user?.department?.name,
            notes: approval.notes,
            raw_status: approval.status,
            role_names: roleNames,
            role_count: roleNames.length,
            first_role: roleNames[0] || "-",
            is_manual: false,
        };
    });

    // Manual step pertama (pengajuan dibuat)
    const firstApproval = approvals[0];
    const manualFirstStep = {
        id: "manual-start",
        title: "Pengajuan Diterima",
        step_order: 0,
        status: "Pengajuan",
        date: formatDate(firstApproval?.created_at),
        approved_by: firstApproval?.cash_advance?.user?.name || "System",
        approved_by_role: firstApproval?.cash_advance?.user?.name,
        notes: "Pengajuan cash advance berhasil dibuat",
        raw_status: "approved",
        role_names: ["System"],
        role_count: 1,
        first_role: "System",
        is_manual: true,
        is_first: true,
    };

    return [manualFirstStep, ...mappedSteps];
});

// Cari semua step pending
const pendingSteps = computed(() => {
    return approvals?.filter((a) => a.status === "pending") || [];
});

// Cari pending terakhir (step_order terbesar)
const lastPendingStep = computed(() => {
    const pending = pendingSteps.value;
    if (pending.length === 0) return null;

    // Urutkan berdasarkan step_order descending
    return [...pending].sort((b, a) => {
        const orderA = a.approval_step?.step_order || a.approval_step_id;
        const orderB = b.approval_step?.step_order || b.approval_step_id;
        return orderB - orderA;
    })[0];
});

const beforeStepPending = approvalSteps.value.find(
    (stepPanding) =>
        Number(stepPanding.step_order) ===
        lastPendingStep?.value?.approval_step_id,
);

const items = [
    {
        label: "Tujuan",
        value: props.dataDetail[0]?.cash_advance?.purpose,
        span: 2, // ⬅️ gabung 2 kolom
    },
    {
        label: "Jumlah",
        value: formatRupiah(props.dataDetail[0]?.cash_advance?.amount),
        highlight: true,
    },
    {
        label: "Tanggal",
        value: formatDate(props.dataDetail[0]?.cash_advance?.request_date),
    },
];

const handleApprove = () => {
    props.submit({
        values: {
            notes: value.value,
            status: "approved",
            approval_step_id: currentStep.value?.approval_step_id,
        },
        method: "put",
        url: "approvals.approve",
        id: currentStep.value?.id,
        onSuccess: () => {
            props.closeModal();
            emit("updated");
        },
    });
};

const handleReject = () => {
    props.submit({
        values: {
            notes: value.value,
            status: "rejected",
            cash_advance_id: currentStep.value?.cash_advance_id,
        },
        method: "put",
        url: "approvals.reject",
        id: currentStep.value?.id,
        onSuccess: () => {
            props.closeModal();
            emit("updated");
        },
    });
};
</script>

<template>
    <!-- HEADER -->
    <div
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-gray-100"
    >
        <div class="flex items-center gap-3">
            <!-- Icon Avatar -->
            <div
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-md"
            >
                <svg
                    class="w-5 h-5 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    ></path>
                </svg>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-800 leading-tight">
                    {{ requestName }}
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Pengajuan Cash Advance
                </p>
            </div>

            <n-tag size="medium" type="info" bordered class="ml-1">
                {{ departmentName }}
            </n-tag>
        </div>

        <div class="meta" v-if="props.roleName !== 'Employee'">
            <n-tag
                :type="getTimelineType(statusMap[currentStatus?.status])"
                round
                size="large"
                class="shadow-sm"
            >
                {{ statusMap[currentStatus?.status] }}
            </n-tag>
        </div>
    </div>

    <!-- DETAIL - Simple Clean -->
    <div
        class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-xl"
    >
        <div
            v-for="item in items"
            :key="item.label"
            :class="[item.span === 2 ? 'col-span-2' : '']"
        >
            <div
                v-if="item.label"
                class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1"
            >
                {{ item.label }}
            </div>
            <div
                :class="[
                    'text-gray-800',
                    item.highlight
                        ? 'text-lg font-bold text-green-600'
                        : 'text-sm font-medium',
                ]"
            >
                {{ item.value || "-" }}
            </div>
        </div>
    </div>

    <!-- APPROVAL -->
    <n-grid :cols="2" x-gap="16" class="approval-grid">
        <!-- TIMELINE -->
        <n-gi>
            <div class="card-section">
                <h3>Alur Persetujuan</h3>

                <n-timeline>
                    <n-timeline-item
                        v-for="(step, i) in approvalSteps"
                        :key="i"
                        :type="getTimelineType(step.status)"
                        :title="step.title ?? null"
                        :time="step.date"
                    >
                        <template #default>
                            <n-space vertical size="small">
                                <!-- Status Tag dan Approved By dalam satu baris -->
                                <div class="status-row">
                                    <n-tag
                                        :type="getTimelineType(step.status)"
                                        size="small"
                                        round
                                    >
                                        {{ step.status ?? "Pengajuan" }}
                                    </n-tag>

                                    <!-- Approved By di sebelah kiri status -->
                                    <div
                                        v-if="step.approved_by"
                                        class="approved-by"
                                    >
                                        <n-icon size="14" :depth="3">
                                            <person-outline />
                                        </n-icon>
                                        <span>{{ step.approved_by }}</span>
                                    </div>
                                </div>
                            </n-space>
                        </template>
                    </n-timeline-item>
                </n-timeline>
            </div>
        </n-gi>

        <!-- REVIEW NOTES -->
        <n-gi>
            <n-collapse default-expanded-names="1" accordion>
                <n-collapse-item title="Catatan" name="1">
                    <div class="card-section notes">
                        <n-list class="notes-list">
                            <n-list-item
                                class="no-space"
                                v-for="(note, i) in approvalSteps.slice(1)"
                                :key="i"
                            >
                                <div class="note-item">
                                    <div class="note-header">
                                        <div class="note-title">
                                            {{ note.title }}
                                        </div>
                                    </div>

                                    <div class="note-status">
                                        {{ note.notes || "-" }}
                                    </div>
                                </div>
                            </n-list-item>
                        </n-list>
                    </div>
                </n-collapse-item>
            </n-collapse>
        </n-gi>
    </n-grid>
    <n-form v-if="canApproveCurrent && props.roleName !== 'Employee'">
        <!-- NOTES -->
        <n-grid :cols="1" x-gap="16" class="approval-grid">
            <n-grid-item>
                <n-form-item label="Catatan Persetujuan">
                    <n-input
                        v-model:value="value"
                        placeholder="Masukkan catatan"
                        type="textarea"
                        :rows="3"
                        :disabled="!canApproveCurrent"
                    />
                </n-form-item>
            </n-grid-item>
        </n-grid>

        <!-- BUTTON ACTIONS -->
        <n-grid :cols="2" x-gap="12" class="button-grid">
            <n-grid-item>
                <n-button
                    type="error"
                    size="large"
                    block
                    :loading="loading"
                    :disabled="loading"
                    @click="handleReject"
                >
                    <template #icon>
                        <n-icon><close-circle-outline /></n-icon>
                    </template>
                    Tolak
                </n-button>
            </n-grid-item>
            <n-grid-item>
                <n-button
                    type="success"
                    size="large"
                    block
                    :loading="loading"
                    :disabled="loading"
                    @click="handleApprove"
                >
                    <template #icon>
                        <n-icon><checkmark-circle-outline /></n-icon>
                    </template>
                    Setujui
                </n-button>
            </n-grid-item>
        </n-grid>
    </n-form>
    <!-- PESAN jika form tidak tampil -->

    <n-alert
        class="mt-5"
        v-if="
            (lastPendingStep?.status != null &&
                !canApproveCurrent &&
                currentStep?.status !== 'rejected') ||
            props.roleName === 'Employee'
        "
        :type="Number(approvalStep) === 1 ? 'info' : 'warning'"
        :show-icon="true"
    >
        <span
            v-if="
                lastPendingStep?.status == null &&
                props.roleName === 'Employee' &&
                getStatusRequest === 'disbursed'
            "
        >
            Dana sudah di serahkan <b>{{ requestName }}</b>
        </span>
        <span
            v-else-if="
                lastPendingStep?.status == null &&
                props.roleName === 'Employee' &&
                getLastStatus?.status === 'approved'
            "
        >
            Dana bisa diambil di <b>Finance</b>
        </span>
        <span
            v-if="
                lastPendingStep?.status == null &&
                props.roleName === 'Employee' &&
                getLastStatus?.status === 'rejected'
            "
        >
            Pengajuan anda ditolak.
        </span>
        <span v-else-if="lastPendingStep?.status == 'pending'">
            Menunggu persetujuan <b>{{ beforeStepPending?.title }}</b>
        </span>
    </n-alert>
</template>

<style scoped>
/* ======================
   CARD
====================== */
:deep(.n-list-item) {
    padding: 0 !important;
    margin: 0 !important;
}

.status-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.approved-by {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #666;
}

.notes-list {
    background: transparent;
}

.note-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.note-item:last-child {
    border-bottom: none;
}

/* Header (title + time) */
.note-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.note-title {
    font-weight: 500;
    font-size: 14px;
}

.note-time {
    font-size: 12px;
    color: #999;
}

/* Date */
.note-date {
    font-size: 12px;
    color: #aaa;
    margin-top: 2px;
}

/* Status */
.note-status {
    font-size: 12px;
    margin-top: 4px;
    color: #666;
}

/* ======================
   HEADER
====================== */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.user h2 {
    margin: 0 0 6px;
}

.meta {
    display: flex;
    gap: 8px;
    align-items: center;
}

/* ======================
   DETAIL
====================== */
.details {
    margin-bottom: 20px;
    padding: 16px;
    background: #f5f7fa;
    border-radius: 8px;
}

.detail-label {
    font-size: 12px;
    color: #777;
}

.detail-value {
    font-size: 14px;
    font-weight: 500;
}

.detail-value.highlight {
    color: #18a058;
    font-weight: 600;
}

/* ======================
   APPROVAL
====================== */
.approval-grid {
    align-items: flex-start;
}

.card-section {
    background: #fafafa;
    padding: 16px;
    border-radius: 10px;
    height: 100%;
}

.card-section h3 {
    margin-bottom: 12px;
}

/* Notes scroll */
.notes {
    max-height: 400px;
    overflow-y: auto;
}

/* ======================
   RESPONSIVE
====================== */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .approval-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
