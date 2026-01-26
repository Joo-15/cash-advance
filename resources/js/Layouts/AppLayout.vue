<script setup>
import {
    NLayout,
    NLayoutHeader,
    NLayoutContent,
    NLayoutFooter,
    NDropdown,
    NTooltip,
    NIcon,
    NAvatar,
} from "naive-ui";
import Sidebar from "@/Components/Sidebar.vue";
import { router, usePage } from "@inertiajs/vue3";
import { ref, provide, onMounted, h } from "vue";
import { LogOutOutline, PersonCircleOutline } from "@vicons/ionicons5";
import { computed } from "vue";

/* =====================
   STATE
===================== */
const page = usePage();
const sidebarCollapsed = ref(false);
const isReady = ref(false);

provide("sidebarCollapsed", sidebarCollapsed);

/* =====================
   MENU CONFIG
===================== */
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
const handleUserDropdownSelect = (key) => {
    if (key === "profile") router.get("/profile");
    if (key === "logout") router.post("/logout");
};
/* =====================
   COMPUTED
===================== */
const user = computed(() => page.props.auth?.user ?? null);

const avatarFallback = computed(() => {
    const name = user.value?.name || "User";
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(
        name,
    )}&background=4f46e5&color=fff`;
});

const avatarSrc = computed(() => {
    return user.value?.avatar || undefined;
});

onMounted(() => {
    sidebarCollapsed.value =
        localStorage.getItem("sidebar-collapsed") === "true";
    isReady.value = true;
});
</script>

<template>
    <n-layout v-if="isReady" class="min-h-screen">
        <!-- HEADER FIXED -->
        <n-layout-header
            bordered
            class="px-2 h-12 flex items-center justify-between bg-indigo-500 text-white dark:bg-gray-800 fixed top-0 left-0 right-0 z-50"
        >
            <h2 class="text-lg font-semibold">
                {{ page.props.pageHeader ?? "Dashboard" }}
            </h2>
            <div class="dark:border-gray-800">
                <NDropdown
                    trigger="click"
                    placement="bottom-end"
                    :options="userDropdownOptions"
                    @select="handleUserDropdownSelect"
                >
                    <div
                        class="flex items-center gap-2 rounded-lg cursor-pointer dark:hover:bg-gray-800"
                        :class="{ 'justify-center': sidebarCollapsed }"
                    >
                        <n-tooltip v-if="sidebarCollapsed" placement="right">
                            <template #trigger>
                                <n-avatar
                                    round
                                    :size="32"
                                    :src="user?.avatar"
                                    :fallback-src="avatarFallback"
                                />
                            </template>
                            {{ user?.name || "User" }}
                        </n-tooltip>

                        <n-avatar
                            v-else
                            round
                            :size="28"
                            :src="user?.avatar"
                            :fallback-src="avatarFallback"
                        />

                        <Transition name="slide-fade" mode="out-in">
                            <div v-if="!sidebarCollapsed" class="min-w-0">
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
            <slot name="header" />
        </n-layout-header>
        <!-- BODY -->
        <n-layout has-sider class="min-h-screen">
            <Sidebar class="pt-12" />

            <n-layout>
                <n-layout-content
                    class="min-h-screen transition-all duration-300"
                    :class="sidebarCollapsed ? 'ml-[64px]' : 'ml-[230px]'"
                    :native-scrollbar="false"
                >
                    <div class="mx-auto pt-16 px-4">
                        <slot />
                    </div>
                </n-layout-content>

                <n-layout-footer
                    bordered
                    class="px-6 py-4 text-center text-sm text-gray-500 bg-white dark:bg-gray-800"
                >
                    © 2024 Penjualan Pro. All rights reserved.
                </n-layout-footer>
            </n-layout>
        </n-layout>
    </n-layout>
</template>
