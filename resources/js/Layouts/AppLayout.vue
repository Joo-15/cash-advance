<script setup lang="ts">
import {
    NLayout,
    NLayoutHeader,
    NLayoutContent,
    NLayoutFooter,
} from "naive-ui";
import Sidebar from "@/Components/Sidebar.vue";
import { usePage } from "@inertiajs/vue3";
import { ref, provide, onMounted } from "vue";

const page = usePage();
const sidebarCollapsed = ref(false);
const isReady = ref(false);

provide("sidebarCollapsed", sidebarCollapsed);

// watch(sidebarCollapsed, (newVal, oldVal) => {
//     console.log("Sidebar collapsed berubah:");
//     console.log("Dari:", oldVal);
//     console.log("Ke:", newVal);
// });

onMounted(() => {
    sidebarCollapsed.value =
        localStorage.getItem("sidebar-collapsed") === "true";
    isReady.value = true;
});
</script>

<template>
    <n-layout v-if="isReady" has-sider class="min-h-screen">
        <!-- Sidebar Component -->
        <Sidebar />

        <!-- Main Content -->
        <n-layout class="min-h-screen">
            <!-- Header -->
            <n-layout-header
                bordered
                :class="sidebarCollapsed ? 'ml-[64px]' : 'ml-[230px]'"
                class="h-14 px-6 flex items-center justify-between bg-white transition-all duration-300 dark:bg-gray-800"
            >
                <div class="flex items-center gap-4">
                    <!-- Page Title -->
                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white"
                    >
                        <slot name="title">{{
                            page.props.pageHeader ?? "Dashboard"
                        }}</slot>
                    </h2>
                </div>

                <!-- Right side header content -->
                <div class="flex items-center gap-4">
                    <slot name="header" />
                </div>
            </n-layout-header>

            <!-- Content Area -->
            <n-layout-content
                class="min-h-screen transition-all duration-300"
                :class="sidebarCollapsed ? 'ml-[64px]' : 'ml-[230px]'"
                :native-scrollbar="false"
            >
                <div class="mx-auto p-4">
                    <!-- Page Content -->
                    <slot />
                </div>
            </n-layout-content>

            <!-- Footer -->
            <n-layout-footer
                bordered
                class="px-6 py-4 text-center text-sm text-gray-500 bg-white dark:bg-gray-800"
            >
                © 2024 Penjualan Pro. All rights reserved.
            </n-layout-footer>
        </n-layout>
    </n-layout>
</template>
