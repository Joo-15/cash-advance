// composables/useApprovalSteps.js
import { computed } from 'vue';

export function useApprovalSteps(approvals) {
    // Get approval by step ID
    const getByStepId = (stepId) => {
        if (!approvals.value || !Array.isArray(approvals.value)) return null;

        return approvals.value.find(
            (a) => Number(a.approval_step_id) === Number(stepId)
        ) || null;
    };

    // Get previous step
    // Solusi: Konversi ke number dengan cara yang aman
    const getPrevious = (currentStepId) => {
        const prevId = Number(currentStepId) - 1;

        // Coba berbagai cara konversi
        return approvals.value?.find(a => {
            // Cara 1: Number()
            if (Number(a.approval_step_id) === prevId) return true;

            // Cara 2: parseInt()
            if (parseInt(a.approval_step_id) === prevId) return true;

            // Cara 3: == (loose equality)
            if (a.approval_step_id == prevId) return true;

            return false;
        }) || null;
    };

    // Get next step
    const getNext = (currentStepId) => {
        return getByStepId(Number(currentStepId) + 1);
    };

    // Get step status with formatted data
    const getStatus = (stepId) => {
        const approval = getByStepId(stepId);
        if (!approval) return null;

        return {
            ...approval,
            statusText: approval.status === 'approved' ? 'Disetujui'
                : approval.status === 'pending' ? 'Menunggu'
                    : approval.status === 'rejected' ? 'Ditolak' : '-',
            isApproved: approval.status === 'approved',
            isPending: approval.status === 'pending',
            isRejected: approval.status === 'rejected',
            formattedDate: approval.approved_at
                ? new Date(approval.approved_at).toLocaleDateString('id-ID')
                : null
        };
    };

    // Check if step can be approved
    const canApprove = (stepId) => {
        const current = getByStepId(stepId);
        if (!current) return false;

        // If first step
        if (Number(stepId) === 1) {
            return current.status === 'pending';
        }

        // Check previous step
        const previous = getPrevious(stepId);
        return current.status === 'pending' && previous?.status === 'approved';
    };

    // Get all steps status
    const allSteps = computed(() => {
        if (!approvals.value) return [];

        return approvals.value.map(a => ({
            ...a,
            statusText: a.status === 'approved' ? 'Disetujui'
                : a.status === 'pending' ? 'Menunggu'
                    : a.status === 'rejected' ? 'Ditolak' : '-',
            canApprove: canApprove(a.approval_step_id)
        })).sort((a, b) => a.approval_step_id - b.approval_step_id);
    });

    return {
        getByStepId,
        getPrevious,
        getNext,
        getStatus,
        canApprove,
        allSteps
    };
}