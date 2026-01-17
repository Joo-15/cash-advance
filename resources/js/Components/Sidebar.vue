<script setup>
import { h, ref, computed, onMounted, watch, nextTick } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { NMenu, NLayoutSider, NIcon, NDropdown, NAvatar } from "naive-ui";
import {
    HomeOutline,
    CubeOutline,
    PeopleOutline,
    SettingsOutline,
    LogOutOutline,
    PersonCircleOutline,
} from "@vicons/ionicons5";

// State - default false (terbuka)
const collapsed = ref(false);
const page = usePage();
const isInitialized = ref(false);

// Computed
const activeKey = computed(() => {
    const url = page.url;
    return url.split("?")[0];
});

const user = computed(() => page.props.auth?.user);

// Helper untuk render icon dengan fixed size
const renderIcon = (icon) => {
    return h(
        NIcon,
        {
            size: 20,
            class: "flex-shrink-0",
        },
        {
            default: () => h(icon),
        },
    );
};

// Menu options - menggunakan function untuk dynamic icon
const getMenuOptions = () => [
    {
        label: "Dashboard",
        key: "/dashboard",
        icon: () => renderIcon(HomeOutline),
    },
    {
        label: "Produk",
        key: "/produk",
        icon: () => renderIcon(CubeOutline),
    },
    {
        label: "Pengguna",
        key: "/pengguna",
        icon: () => renderIcon(PeopleOutline),
    },
    {
        label: "Pengaturan",
        key: "/pengaturan",
        icon: () => renderIcon(SettingsOutline),
    },
];

// Dropdown options
const userDropdownOptions = [
    {
        label: "Profile",
        key: "profile",
        icon: () =>
            h(NIcon, { size: 18 }, { default: () => h(PersonCircleOutline) }),
    },
    // {
    //     label: "Pengaturan",
    //     key: "settings",
    //     icon: () =>
    //         h(NIcon, { size: 18 }, { default: () => h(SettingsOutline) }),
    // },
    {
        type: "divider",
        key: "divider-1",
    },
    {
        label: "Logout",
        key: "logout",
        icon: () => h(NIcon, { size: 18 }, { default: () => h(LogOutOutline) }),
        props: {
            style: { color: "#f87171" },
        },
    },
];

// Handlers
const handleMenuSelect = (key) => {
    router.get(
        key,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handleUserDropdownSelect = (key) => {
    switch (key) {
        case "profile":
            router.get("/profile");
            break;
        // case "settings":
        //     router.get("/settings");
        //     break;
        case "logout":
            router.post("/logout");
            break;
    }
};

// Load collapsed state dari localStorage
const loadCollapsedState = () => {
    const saved = localStorage.getItem("sidebar-collapsed");
    console.log("Loading sidebar state from localStorage:", saved);

    if (saved !== null) {
        // Parse dan set state
        collapsed.value = saved === "true";
    } else {
        // Default: false (terbuka)
        collapsed.value = false;
        localStorage.setItem("sidebar-collapsed", "false");
    }

    isInitialized.value = true;
};

const saveCollapsedState = () => {
    if (!isInitialized.value) return;

    console.log("Saving sidebar state:", collapsed.value);
    localStorage.setItem("sidebar-collapsed", collapsed.value.toString());
};

// Lifecycle
onMounted(() => {
    loadCollapsedState();

    // Force re-render setelah mounted
    nextTick(() => {
        collapsed.value = collapsed.value;
    });
});

watch(collapsed, (newValue) => {
    if (isInitialized.value) {
        saveCollapsedState();
    }
});

// Debug: Log state changes
watch(collapsed, (newVal) => {
    console.log("Sidebar collapsed state changed to:", newVal);
});
</script>

<template>
    <NLayoutSider
        :collapsed="collapsed"
        @update:collapsed="
            (value) => {
                if (isInitialized) {
                    collapsed = value;
                }
            }
        "
        collapse-mode="width"
        :collapsed-width="64"
        :width="260"
        show-trigger="bar"
        bordered
        :native-scrollbar="false"
        class="bg-white dark:bg-gray-900"
        :key="isInitialized ? 'initialized' : 'loading'"
    >
        <!-- Logo Section -->
        <div
            class="h-16 flex items-center justify-center border-b border-gray-200 dark:border-gray-800 bg-primary-50 dark:bg-primary-900/20"
        >
            <div class="flex items-center justify-center w-full px-2">
                <div
                    class="w-9 h-9 rounded-lg bg-primary-500 flex items-center justify-center"
                >
                    <span class="text-white font-bold text-sm">P</span>
                </div>
                <Transition name="fade" mode="out-in">
                    <div v-if="!collapsed && isInitialized" class="ml-3">
                        <h1
                            class="font-bold text-lg text-primary-600 dark:text-primary-300 whitespace-nowrap"
                        >
                            Penjualan Pro
                        </h1>
                        <p
                            class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap"
                        >
                            Admin Panel
                        </p>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Menu Section -->
        <div class="flex flex-col h-[calc(100vh-4rem)]">
            <!-- Menu -->
            <div class="flex-1 overflow-y-auto py-3">
                <NMenu
                    :options="getMenuOptions()"
                    :value="activeKey"
                    @update:value="handleMenuSelect"
                    :collapsed="collapsed && isInitialized"
                    :collapsed-width="64"
                    :collapsed-icon-size="22"
                    :icon-size="20"
                    :indent="24"
                    :theme-overrides="{
                        itemPadding:
                            collapsed && isInitialized
                                ? '12px 20px'
                                : '12px 16px',
                        itemHeight: '44px',
                        itemIconSize:
                            collapsed && isInitialized ? '22px' : '20px',
                        itemIconMargin:
                            collapsed && isInitialized
                                ? '0 0 0 0'
                                : '0 12px 0 0',
                    }"
                />
            </div>

            <!-- User Profile Section -->
            <div class="border-t border-gray-200 dark:border-gray-800 p-3">
                <NDropdown
                    :options="userDropdownOptions"
                    @select="handleUserDropdownSelect"
                    placement="top-start"
                    trigger="click"
                    :show-arrow="true"
                >
                    <div
                        class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        :class="{
                            'justify-center': collapsed && isInitialized,
                        }"
                    >
                        <NAvatar
                            round
                            :size="collapsed && isInitialized ? 36 : 40"
                            :src="user?.avatar"
                            :fallback-src="`https://ui-avatars.com/api/?name=${user?.name || 'User'}&background=4f46e5&color=fff`"
                        />

                        <Transition name="slide-fade" mode="out-in">
                            <div
                                v-if="!collapsed && isInitialized"
                                class="flex-1 min-w-0"
                            >
                                <p class="font-medium text-sm truncate">
                                    {{ user?.name || "User" }}
                                </p>
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                >
                                    {{ user?.email || "user@example.com" }}
                                </p>
                            </div>
                        </Transition>
                    </div>
                </NDropdown>
            </div>
        </div>
    </NLayoutSider>
</template>

<style scoped>
/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active {
    transition: all 0.2s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.15s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(-8px);
    opacity: 0;
}

/* Custom styling untuk NMenu */
:deep(.n-menu) {
    --n-item-icon-size: 20px;
    --n-item-icon-size-collapsed: 22px;
}

:deep(.n-menu-item-content) {
    display: flex;
    align-items: center;
}

:deep(.n-menu-item .n-icon) {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Fix icon alignment */
:deep(.n-menu-item-content__icon) {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
}

/* Saat collapsed, center the icon */
:deep(.n-menu--collapsed .n-menu-item-content__icon) {
    margin-right: 0;
    justify-content: center;
}

/* Ensure proper icon size */
:deep(.n-icon) {
    line-height: 1;
}
</style>
