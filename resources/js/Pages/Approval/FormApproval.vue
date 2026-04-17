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
    NScrollbar,
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

const emit = defineEmits(["update:showModal", "updated"]);
const value = ref("");

// Data
const approvals = props.dataDetail;
const requestName = approvals[0]?.cash_advance?.user?.name || "Guest";
const departmentName =
    approvals[0]?.cash_advance?.user?.department?.name || "Guest";
const getLastStatus = approvals[approvals.length - 1];
const getStatusRequest = approvals[0]?.cash_advance?.status || "Guest";

// Status mapping
const statusMap = {
    approved: "Disetujui",
    pending: "Menunggu",
    rejected: "Ditolak",
};

const getTimelineType = (status) => {
    switch (status) {
        case "Disetujui":
            return "success";
        case "Menunggu":
            return "warning";
        case "Ditolak":
            return "error";
        default:
            return "info";
    }
};

// Approval steps composable
const { getByStepId, getPrevious, getNext, getStatus, canApprove } =
    useApprovalSteps(computed(() => approvals));

// Computed properties
const currentStep = computed(() => getByStepId(props.approvalStep));
const currentStatus = computed(() => getStatus(props.approvalStep));
const canApproveCurrent = computed(() => canApprove(props.approvalStep));

const approvalSteps = computed(() => {
    if (!approvals || !Array.isArray(approvals)) return [];

    const mappedSteps = approvals.map((approval, index) => {
        let status = "-";
        if (approval.status === "approved") status = "Disetujui";
        else if (approval.status === "pending") status = "Menunggu";
        else if (approval.status === "rejected") status = "Ditolak";

        let roleNames = [];
        if (approval.approval_step?.approval_step_roles) {
            roleNames = approval.approval_step.approval_step_roles
                .map((roleItem) => roleItem.role?.name)
                .filter((name) => name);
        }

        let title =
            roleNames.length > 0
                ? roleNames.join(" / ")
                : `Step ${approval.approval_step?.step_order || index + 1}`;

        return {
            id: approval.id,
            title: title,
            step_order: approval.approval_step?.step_order || index + 1,
            status: status,
            date: approval.approved_at ? formatDate(approval.approved_at) : "",
            approved_by: approval.user?.name || null,
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

    const manualFirstStep = {
        id: "manual-start",
        title: "Pengajuan Diterima",
        step_order: 0,
        status: "Pengajuan",
        date: formatDate(approvals[0]?.created_at),
        approved_by: approvals[0]?.cash_advance?.user?.name || "System",
        approved_by_role: approvals[0]?.cash_advance?.user?.name,
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

// Pending steps
const pendingSteps = computed(
    () => approvals?.filter((a) => a.status === "pending") || [],
);

const lastPendingStep = computed(() => {
    const pending = pendingSteps.value;
    if (pending.length === 0) return null;

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

// Detail items
const items = [
    {
        label: "Tujuan",
        value: props.dataDetail[0]?.cash_advance?.purpose,
        span: 2,
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

// Handlers
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
    <div class="approval-detail">
        <!-- HEADER -->
        <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-gray-100"
        >
            <div class="flex items-center gap-3">
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
                        />
                    </svg>
                </div>

                <div>
                    <h3
                        class="text-lg font-semibold text-gray-800 leading-tight"
                    >
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

            <div v-if="props.roleName !== 'Employee'" class="meta">
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

        <!-- CONTENT SCROLLABLE -->
        <n-scrollbar style="max-height: 350px" class="my-4">
            <!-- DETAIL -->
            <div
                class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 p-4 bg-slate-50 rounded-xl"
            >
                <div
                    v-for="item in items"
                    :key="item.label"
                    :class="[item.span === 2 ? 'col-span-2' : '']"
                >
                    <div
                        v-if="item.label"
                        class="text-[10px] font-medium text-gray-400 uppercase"
                    >
                        {{ item.label }}
                    </div>
                    <div
                        :class="[
                            'text-gray-800',
                            item.highlight
                                ? 'font-semibold text-green-600'
                                : 'font-medium',
                        ]"
                    >
                        {{ item.value || "-" }}
                    </div>
                </div>
            </div>

            <!-- APPROVAL GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- TIMELINE -->
                <div class="rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-400 mb-3">
                        Alur Persetujuan
                    </h3>

                    <div class="pr-2">
                        <n-timeline>
                            <n-timeline-item
                                v-for="(step, i) in approvalSteps"
                                :key="i"
                                :type="getTimelineType(step.status)"
                                :title="step.title ?? null"
                                :time="step.date"
                            >
                                <template #default>
                                    <div class="flex flex-col gap-1">
                                        <div
                                            class="flex items-center justify-between flex-wrap gap-2"
                                        >
                                            <n-tag
                                                :type="
                                                    getTimelineType(step.status)
                                                "
                                                size="small"
                                                round
                                            >
                                                {{ step.status ?? "Pengajuan" }}
                                            </n-tag>
                                            <div
                                                v-if="step.approved_by"
                                                class="flex items-center gap-1 text-xs text-gray-500"
                                            >
                                                <n-icon size="12"
                                                    ><person-outline
                                                /></n-icon>
                                                <span>{{
                                                    step.approved_by
                                                }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </n-timeline-item>
                        </n-timeline>
                    </div>
                </div>

                <!-- REVIEW NOTES -->
                <div class="border-l pl-2">
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-400 mb-3">
                            Catatan
                        </h3>
                        <div class="notes-container max-h-[400px]">
                            <div
                                v-for="(note, i) in approvalSteps.slice(1)"
                                :key="i"
                                class="py-3 border-b border-gray-100 last:border-0"
                            >
                                <div class="font-medium text-sm text-gray-700">
                                    {{ note.title }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ note.notes || "-" }}
                                </div>
                            </div>
                            <div
                                v-if="approvalSteps.slice(1).length === 0"
                                class="text-center text-gray-400 text-sm py-4"
                            >
                                Belum ada catatan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </n-scrollbar>

        <!-- FORM APPROVAL -->
        <div
            v-if="canApproveCurrent && props.roleName !== 'Employee'"
            class="mt-4 pt-4"
        >
            <n-form>
                <n-form-item label="Catatan Persetujuan">
                    <n-input
                        v-model:value="value"
                        placeholder="Masukkan catatan"
                        type="textarea"
                        :rows="3"
                        :disabled="!canApproveCurrent"
                    />
                </n-form-item>

                <div class="grid grid-cols-2 gap-3 mt-4">
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
                </div>
            </n-form>
        </div>

        <!-- ALERT MESSAGE -->
        <n-alert
            v-if="
                (lastPendingStep?.status != null &&
                    !canApproveCurrent &&
                    currentStep?.status !== 'rejected') ||
                props.roleName === 'Employee'
            "
            :type="Number(approvalStep) === 1 ? 'info' : 'warning'"
            :show-icon="true"
            class="mt-4"
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
    </div>
</template>

<style scoped></style>
