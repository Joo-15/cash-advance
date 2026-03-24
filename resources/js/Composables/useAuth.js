// composables/useAuth.js
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAuth() {
    const page = usePage();

    // User data
    const user = computed(() => page.props.auth?.user);
    const isLoggedIn = computed(() => !!user.value);

    // Basic user info
    const userId = computed(() => user.value?.id);
    const userName = computed(() => user.value?.name || 'Guest');
    const userEmail = computed(() => user.value?.email);
    const userAvatar = computed(() => user.value?.avatar);

    // Department data
    const department = computed(() => user.value?.department);
    const departmentId = computed(() => department.value?.id);
    const departmentName = computed(() => department.value?.name || '-');
    const hasDepartment = computed(() => !!department.value);

    // Role data
    const role = computed(() => user.value?.role);
    const roleId = computed(() => role.value?.id);
    const roleName = computed(() => role.value?.name || 'Guest');

    // Role checks
    const isAdmin = computed(() => roleName.value === 'Admin' || roleName.value === 'Super Admin');
    const isSupervisor = computed(() => roleName.value === 'Supervisor');
    const isManager = computed(() => roleName.value === 'Manager');
    const isEmployee = computed(() => roleName.value === 'Employee');
    const isFinance = computed(() => roleName.value === 'Finance');

    // Helper untuk cek role
    const hasRole = (allowedRoles) => {
        if (!roleName.value) return false;
        if (typeof allowedRoles === 'string') {
            return roleName.value === allowedRoles;
        }
        return allowedRoles.includes(roleName.value);
    };

    // Helper untuk cek department
    const inDepartment = (deptId) => {
        if (!departmentId.value) return false;
        return departmentId.value === deptId;
    };

    // Helper untuk cek department name
    const inDepartmentName = (deptName) => {
        if (!departmentName.value) return false;
        return departmentName.value === deptName;
    };

    // Helper untuk mendapatkan greeting
    const greeting = computed(() => {
        const hour = new Date().getHours();
        if (hour < 12) return 'Selamat Pagi';
        if (hour < 18) return 'Selamat Siang';
        return 'Selamat Malam';
    });

    // Full name with title
    const fullNameWithTitle = computed(() => {
        if (roleName.value && roleName.value !== 'Guest') {
            return `${roleName.value} ${userName.value}`;
        }
        return userName.value;
    });

    return {
        // User data
        user,
        isLoggedIn,
        userId,
        userName,
        userEmail,
        userAvatar,

        // Department
        department,
        departmentId,
        departmentName,
        hasDepartment,

        // Role
        role,
        roleId,
        roleName,

        // Role checks
        isAdmin,
        isSupervisor,
        isManager,
        isEmployee,
        isFinance,

        // Helpers
        hasRole,
        inDepartment,
        inDepartmentName,

        // Additional
        greeting,
        fullNameWithTitle,
    };
}