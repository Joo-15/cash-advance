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
    useMessage,
} from "naive-ui";
import Sidebar from "@/Components/Sidebar.vue";
import { router, usePage } from "@inertiajs/vue3";
import { ref, provide, onMounted, h, watchEffect } from "vue";
import {
    LogOutOutline,
    MedicalSharp,
    Person,
    PersonCircleOutline,
} from "@vicons/ionicons5";
import { computed } from "vue";

/* =====================
   STATE
===================== */

const page = usePage();
const message = useMessage();
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

watchEffect(() => {
    const flash = page.props.flash;

    if (flash.success) {
        message.success(flash.success);
        // Clear setelah ditampilkan (opsional)
        flash.success = null;
    }

    if (flash.error) {
        message.error(flash.error);
        flash.error = null;
    }

    if (flash.warning) {
        message.warning(flash.warning);
        flash.warning = null;
    }
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
            class="h-14 px-6 flex items-center justify-between bg-gradient-to-r from-green-500 via-green-400 to-green-500 text-white fixed top-0 left-0 right-0 z-50 shadow-md"
        >
            <h2 class="text-lg font-semibold">CASH ADVANCE</h2>
            <div class="dark:border-gray-800">
                <NDropdown
                    width="150"
                    size="small"
                    trigger="click"
                    placement="bottom-end"
                    :options="userDropdownOptions"
                    @select="handleUserDropdownSelect"
                >
                    <div
                        class="flex items-center gap-2 rounded-lg cursor-pointer dark:hover:bg-gray-800"
                        :class="{ 'justify-center': sidebarCollapsed }"
                    >
                        <!-- <n-tooltip v-if="sidebarCollapsed" placement="right">
                            <template #trigger>
                                <n-avatar
                                    round
                                    :size="32"
                                    :src="user?.avatar"
                                    :fallback-src="avatarFallback"
                                />
                            </template>
                            {{ user?.name || "User" }}
                        </n-tooltip> -->

                        <n-avatar size="small" round class="bg-blue-400">
                            <n-icon size="18">
                                <Person />
                            </n-icon>
                        </n-avatar>

                        <Transition name="slide-fade" mode="out-in">
                            <div class="min-w-0">
                                <p class="font-medium text-sm truncate">
                                    {{ user?.name || "User" }}
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
                    <div class="mx-auto pt-12 px-5 bg-slate-50">
                        <slot />
                    </div>
                </n-layout-content>

                <n-layout-footer
                    bordered
                    class="px-6 py-4 text-right text-sm text-gray-500 bg-white dark:bg-gray-800"
                >
                    © 2024 Cash Advance
                </n-layout-footer>
            </n-layout>
        </n-layout>
    </n-layout>
</template>
