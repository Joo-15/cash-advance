<script setup>
import { h, inject, ref, computed, onMounted, onUnmounted, watch } from "vue";
import { usePage, router, useRemember } from "@inertiajs/vue3";
import { NMenu, NLayoutSider, NIcon, NDropdown, NAvatar } from "naive-ui";

import {
    HomeOutline,
    CubeOutline,
    PeopleOutline,
    SettingsOutline,
    BookOutline,
    CashOutline,
    DocumentTextOutline,
} from "@vicons/ionicons5";

/* =====================
   CONSTANT
===================== */
const STORAGE_KEY = "sidebar-collapsed";

/* =====================
   STATE
===================== */
const collapsed = inject("sidebarCollapsed");
const isInitialized = ref(false);
const page = usePage();
const expandedKeys = useRemember([], "sidebar-expanded-keys");
const isMobile = ref(false);

/* =====================
   COMPUTED
===================== */
const activeKey = computed(() => page.url.split("?")[0]);
const user = computed(() => page.props.auth?.user);
const userRole = computed(
    () => user.value?.role?.name || user.value?.role || "employee",
);
const department = computed(() => page.props.auth?.user?.department?.name);
console.log("page", userRole.value);

// const department = computed(() => page.)

/* =====================
   HELPERS
===================== */
const renderIcon = (icon) => () =>
    h(NIcon, { size: 20, class: "flex-shrink-0" }, () => h(icon));

/* =====================
   ROLE-BASED MENU ACCESS
===================== */

// Definisi role dan menu apa saja yang bisa diakses
const rolePermissions = {
    // Super Admin - akses semua menu
    "Super Admin": {
        canAccess: () => true,
        menus: [
            "dashboard",
            "pengajuan",
            "approvals",
            "disbursement",
            "penggunaan",
            "master",
            "users",
            "settings",
        ],
    },
    // Admin - akses hampir semua kecuali manajemen user
    Admin: {
        canAccess: (menuKey) => {
            const restrictedMenus = ["users"]; // Menu yang tidak bisa diakses Admin
            return !restrictedMenus.includes(menuKey);
        },
        menus: [
            "dashboard",
            "pengajuan",
            "approvals",
            "disbursement",
            "penggunaan",
            "master",
            "settings",
        ],
    },
    // Finance - akses pencairan dan penggunaan dana
    Finance: {
        canAccess: (menuKey) => {
            const allowedMenus = ["dashboard", "disbursement", "penggunaan"];
            return allowedMenus.includes(menuKey);
        },
        menus: ["dashboard", "disbursement", "penggunaan"],
    },
    // Approver / Manager - akses persetujuan
    Approver: {
        canAccess: (menuKey) => {
            const allowedMenus = ["dashboard", "approvals"];
            return allowedMenus.includes(menuKey);
        },
        menus: ["dashboard", "approvals"],
    },
    // Employee / Staff - akses pengajuan saja
    Employee: {
        canAccess: (menuKey) => {
            const allowedMenus = ["dashboard", "pengajuan"];
            return allowedMenus.includes(menuKey);
        },
        menus: ["dashboard", "pengajuan"],
    },
    // Default role
    default: {
        canAccess: (menuKey) => {
            const allowedMenus = ["dashboard", "pengajuan"];
            return allowedMenus.includes(menuKey);
        },
        menus: ["dashboard", "pengajuan"],
    },
};

// Cek apakah user memiliki akses ke menu tertentu
const hasMenuAccess = (menuKey) => {
    const role = userRole.value;
    const permission = rolePermissions[role] || rolePermissions["default"];
    return permission.canAccess(menuKey);
};

// Filter menu berdasarkan role
const filterMenuByRole = (menuOptions) => {
    return menuOptions.filter((menu) => {
        // Jika menu memiliki children, filter children-nya juga
        if (menu.children) {
            const filteredChildren = menu.children.filter((child) => {
                // Skip divider
                if (child.type === "divider") return true;
                // Cek akses untuk child menu
                const childKey =
                    child.key.replace("/", "").split("/")[0] ||
                    child.key.replace("/", "");
                return hasMenuAccess(childKey);
            });

            // Jika setelah filter masih ada children (bukan hanya divider), tampilkan menu parent
            const hasNonDividerChildren = filteredChildren.some(
                (child) => child.type !== "divider",
            );
            if (hasNonDividerChildren) {
                return { ...menu, children: filteredChildren };
            }
            return false;
        }

        // Untuk menu tanpa children
        const menuKey =
            menu.key.replace("/", "").split("/")[0] ||
            menu.key.replace("/", "");
        return hasMenuAccess(menuKey);
    });
};

/* =====================
   MENU CONFIG
===================== */
const allMenuOptions = [
    {
        label: "Dashboard",
        key: "/dashboard",
        icon: renderIcon(HomeOutline),
        roleAccess: [
            "Super Admin",
            "Admin",
            "Supervisor",
            "Finance",
            "Approver",
            "Employee",
            "General Manager",
            "Manager Accounting",
        ],
    },
    {
        label: "Pengajuan Baru",
        key: "/pengajuan-pinjaman",
        icon: renderIcon(CubeOutline),
        roleAccess: ["Super Admin", "Admin", "Employee"],
    },
    {
        label: "Persetujuan",
        key: "/approvals",
        icon: renderIcon(PeopleOutline),
        roleAccess: [
            "Super Admin",
            "Admin",
            "Supervisor",
            "Chief",
            "Manager",
            "General Manager",
            "Manager Accounting",
            "Finance",
        ],
    },
    {
        label: "Pencairan Dana",
        key: "/pencairan-dana",
        icon: renderIcon(CashOutline),
        roleAccess: ["Super Admin", "Admin", "Finance"],
    },
    {
        label: "Penggunaan Dana",
        key: "/penggunaan-dana",
        icon: renderIcon(DocumentTextOutline),
        roleAccess: ["Super Admin", "Admin", "Employee"],
    },
    {
        label: "Data Master",
        key: "/data-master",
        icon: renderIcon(CubeOutline),
        roleAccess: ["Super Admin", "Admin"],
        children: [
            {
                type: "divider",
                key: "divider-1",
            },
            {
                label: "Data Departemen",
                key: "/data-master/departemen",
                roleAccess: ["Super Admin", "Admin"],
            },
            {
                label: "Pengaturan Persetujuan",
                key: "/data-master/approval-steps",
                roleAccess: ["Super Admin", "Admin"],
            },
        ],
    },
    {
        label: "Manajemen User",
        key: "/users",
        icon: renderIcon(SettingsOutline),
        roleAccess: ["Super Admin"], // Hanya Super Admin
    },
    {
        label: "Pengaturan",
        key: "/settings",
        icon: renderIcon(SettingsOutline),
        roleAccess: ["Super Admin", "Admin"],
        children: [
            {
                type: "divider",
                key: "divider-1",
            },
            {
                label: "Level Persetujuan",
                key: "/approval/approval-step-roles",
                roleAccess: ["Super Admin", "Admin"],
            },
            {
                label: "Pengaturan Level",
                key: "/approval/approval-steps",
                roleAccess: ["Super Admin", "Admin"],
            },
        ],
    },
];

// Computed menu options berdasarkan role
const menuOptions = computed(() => {
    const role = userRole.value;

    // Filter menu berdasarkan role
    return allMenuOptions.filter((menu) => {
        // Cek apakah role memiliki akses ke menu ini
        const hasRoleAccess =
            menu.roleAccess?.includes(role) ||
            menu.roleAccess?.includes("all") ||
            role === "Super Admin"; // Super Admin akses semua

        if (!hasRoleAccess) return false;

        // Jika menu memiliki children, filter children berdasarkan role
        if (menu.children) {
            const filteredChildren = menu.children.filter((child) => {
                if (child.type === "divider") return true;
                return (
                    child.roleAccess?.includes(role) ||
                    child.roleAccess?.includes("all") ||
                    role === "Super Admin"
                );
            });

            // Jika setelah filter masih ada children (bukan hanya divider), tampilkan menu
            const hasNonDividerChildren = filteredChildren.some(
                (child) => child.type !== "divider",
            );
            if (hasNonDividerChildren) {
                return { ...menu, children: filteredChildren };
            }
            return false;
        }

        return true;
    });
});

/* =====================
   HANDLERS
===================== */
const handleMenuSelect = (key) => {
    if (!key || !key.startsWith("/")) return;

    const parentKey = "/" + key.split("/")[1];
    expandedKeys.value = [parentKey];

    router.get(key, {}, { preserveState: true });
};

const handleUserDropdownSelect = (key) => {
    if (key === "profile") router.get("/profile");
    if (key === "logout") router.post("/logout");
};

const handleExpandedKeysUpdate = (keys) => {
    expandedKeys.value = keys;
};

const checkMobile = () => {
    isMobile.value = window.matchMedia("(max-width: 768px)").matches;
};

const getFirstSegment = () => {
    const cleanUrl = page.url.split("?")[0];
    const segments = cleanUrl.split("/").filter(Boolean);
    return segments.length ? "/" + segments[0] : "/";
};

/* =====================
   INIT (ANTI FLICKER)
===================== */
onMounted(() => {
    checkMobile();
    window.addEventListener("resize", checkMobile);

    if (isMobile.value) {
        collapsed.value = true;
    } else {
        collapsed.value = localStorage.getItem(STORAGE_KEY) === "true";
    }

    expandedKeys.value = [getFirstSegment()];
    isInitialized.value = true;
});

onUnmounted(() => {
    window.removeEventListener("resize", checkMobile);
});

/* =====================
   PERSIST STATE
===================== */
watch(collapsed, (val) => {
    if (!isInitialized.value) return;
    if (isMobile.value) return;
    localStorage.setItem(STORAGE_KEY, String(val));
});

watch(
    () => page.url,
    () => {
        expandedKeys.value = [getFirstSegment()];
    },
    { immediate: true },
);
</script>

<template>
    <NLayoutSider
        v-if="isInitialized"
        v-model:collapsed="collapsed"
        collapse-mode="width"
        :collapsed-width="64"
        :width="230"
        show-trigger="arrow-circle"
        bordered
        :native-scrollbar="false"
        class="bg-slate-100 dark:bg-gray-900 fixed left-0 z-40 min-h-screen"
    >
        <!-- LOGO AREA -->
        <div class="py-4 px-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-center">
                <div
                    :class="[
                        'rounded-lg bg-primary-600 flex items-center justify-center transition-all duration-300',
                        collapsed ? 'w-10 h-10' : 'w-10 h-10',
                    ]"
                >
                    <span class="text-indigo-700 font-bold text-4xl">CA</span>
                </div>
                <Transition name="fade" class="ml-4">
                    <div v-if="!collapsed">
                        <h1
                            class="font-bold text-lg text-gray-800 dark:text-white whitespace-nowrap"
                        >
                            {{
                                userRole !== "Super Admin"
                                    ? department
                                    : userRole
                            }}
                        </h1>
                        <p
                            class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap"
                        >
                            {{ userRole !== "Super Admin" ? userRole : "" }}
                        </p>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- MENU -->
        <div class="flex flex-col h-[calc(100vh-5rem)]">
            <div class="flex-1 overflow-y-auto py-3">
                <NMenu
                    :options="menuOptions"
                    :value="activeKey"
                    :collapsed="collapsed"
                    :collapsed-width="64"
                    :collapsed-icon-size="22"
                    :icon-size="20"
                    :indent="24"
                    :expanded-keys="expandedKeys"
                    @update:expanded-keys="handleExpandedKeysUpdate"
                    @update:value="handleMenuSelect"
                    :theme-overrides="{
                        itemPadding: collapsed ? '12px 20px' : '12px 16px',
                        itemHeight: '44px',
                        itemIconSize: collapsed ? '22px' : '20px',
                        itemIconMargin: collapsed ? '0' : '0 12px 0 0',
                    }"
                />
            </div>
        </div>
    </NLayoutSider>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
