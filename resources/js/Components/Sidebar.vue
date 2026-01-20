<script setup>
import { h, inject, ref, computed, onMounted, onUnmounted, watch } from "vue";
import { usePage, router, useRemember } from "@inertiajs/vue3";
import { NMenu, NLayoutSider, NIcon, NDropdown, NAvatar } from "naive-ui";

import {
    HomeOutline,
    CubeOutline,
    PeopleOutline,
    SettingsOutline,
    LogOutOutline,
    PersonCircleOutline,
    StorefrontOutline,
    BookOutline,
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

/* =====================
   HELPERS
===================== */
const renderIcon = (icon) => () =>
    h(NIcon, { size: 20, class: "flex-shrink-0" }, () => h(icon));

/* =====================
   MENU CONFIG
===================== */
const menuOptions = [
    {
        label: "Dashboard",
        key: "/dashboard",
        icon: renderIcon(HomeOutline),
    },
    {
        label: "Produk",
        key: "/produk", // ✅ HARUS ADA SLASH
        icon: renderIcon(CubeOutline),
        children: [
            {
                type: "divider",
                key: "divider-1",
            },
            {
                label: "Daftar Produk",
                key: "/produk",
                icon: renderIcon(BookOutline),
            },
            { label: "Kategori Produk", key: "/produk/kategori" },
            { label: "Stok Produk", key: "/produk/stok" },
        ],
    },

    {
        label: "Pengguna",
        key: "/pengguna",
        icon: renderIcon(PeopleOutline),
    },
    {
        label: "Pengaturan",
        key: "/pengaturan",
        icon: renderIcon(SettingsOutline),
    },
];

const userDropdownOptions = [
    {
        label: "Profile",
        key: "profile",
        icon: () => h(NIcon, { size: 18 }, () => h(PersonCircleOutline)),
    },
    { type: "divider" },
    {
        label: "Logout",
        key: "logout",
        icon: () => h(NIcon, { size: 18 }, () => h(LogOutOutline)),
        props: { style: { color: "#f87171" } },
    },
];

/* =====================
   HANDLERS
===================== */
const handleMenuSelect = (key) => {
    if (!key.startsWith("/")) return;

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
/* =====================
   INIT (ANTI FLICKER)
===================== */
onMounted(() => {
    checkMobile();
    window.addEventListener("resize", checkMobile);

    if (isMobile.value) {
        // ✅ MOBILE: paksa collapsed
        collapsed.value = true;
    } else {
        // ✅ DESKTOP: ambil dari localStorage
        collapsed.value = localStorage.getItem(STORAGE_KEY) === "true";
    }

    // Ambil segment pertama dari URL
    const firstSegment = "/" + page.url.split("/")[1];

    expandedKeys.value = [firstSegment]; // ⬅️ submenu aktif saat refresh

    isInitialized.value = true;
});

onUnmounted(() => {
    window.removeEventListener("resize", checkMobile);
});

/* =====================
   PERSIST STATE
===================== */
watch(collapsed, (val) => {
    // ⬅️ simpan state (hanya desktop & setelah init)
    if (!isInitialized.value) return;
    if (isMobile.value) return;

    localStorage.setItem(STORAGE_KEY, String(val));
});

// watch(
//     () => page.url,
//     (url) => {
//         const firstSegment = "/" + url.split("/")[1];
//         expandedKeys.value = [firstSegment];
//     },
//     { immediate: true },
// );
</script>
<template>
    <!-- v-if adalah KUNCI agar tidak ada animasi nutup saat refresh -->
    <NLayoutSider
        v-if="isInitialized"
        v-model:collapsed="collapsed"
        collapse-mode="width"
        :collapsed-width="64"
        :width="260"
        show-trigger="arrow-circle"
        bordered
        :native-scrollbar="false"
        class="bg-white dark:bg-gray-900 fixed left-0 top-0 h-screen z-40"
    >
        <!-- LOGO -->
        <div
            class="h-14 flex items-left justify-center border-b border-gray-100 dark:border-gray-800 bg-primary-50 dark:bg-primary-900/20"
        >
            <div class="flex items-center justify-center w-full px-2">
                <div class="flex items-center justify-center">
                    <div
                        :class="[
                            'rounded-lg flex items-center justify-center transition-all duration-300',
                            collapsed ? 'w-10 h-10' : 'w-9 h-9',
                        ]"
                        :style="{
                            backgroundColor: collapsed
                                ? 'transparent'
                                : '#4f46e5',
                        }"
                    >
                        <Transition name="logo-fade" mode="out-in">
                            <NIcon
                                v-if="collapsed"
                                :size="24"
                                :component="StorefrontOutline"
                                class="text-primary-600 dark:text-primary-300"
                            />
                            <span v-else class="text-white font-bold text-sm">
                                P
                            </span>
                        </Transition>
                    </div>
                </div>

                <Transition name="fade" mode="out-in">
                    <div v-if="!collapsed" class="ml-3">
                        <h1
                            class="font-bold text-lg text-primary-600 dark:text-primary-300 whitespace-nowrap"
                        >
                            Logo
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

        <!-- MENU -->
        <div class="flex flex-col h-[calc(100vh-4rem)]">
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

            <!-- USER -->
            <div class="border-t border-gray-200 dark:border-gray-800">
                <NDropdown
                    trigger="click"
                    placement="top-start"
                    :options="userDropdownOptions"
                    @select="handleUserDropdownSelect"
                >
                    <div
                        class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        :class="{ 'justify-center': collapsed }"
                    >
                        <NAvatar
                            round
                            :size="collapsed ? 25 : 40"
                            :src="user?.avatar"
                            :fallback-src="`https://ui-avatars.com/api/?name=${
                                user?.name || 'User'
                            }&background=4f46e5&color=fff`"
                        />

                        <Transition name="slide-fade" mode="out-in">
                            <div v-if="!collapsed" class="min-w-0">
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
