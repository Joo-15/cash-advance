<script setup>
import {
    NLayout,
    NLayoutHeader,
    NLayoutContent,
    NLayoutFooter,
} from "naive-ui";
import Sidebar from "@/Components/Sidebar.vue";
</script>

<template>
    <n-layout has-sider class="min-h-screen">
        <!-- Sidebar Component -->
        <Sidebar />

        <!-- Main Content -->
        <n-layout class="min-h-screen">
            <!-- Header -->
            <n-layout-header
                bordered
                class="h-16 px-6 flex items-center justify-between bg-white dark:bg-gray-800"
            >
                <div class="flex items-center gap-4">
                    <!-- Page Title -->
                    <h2
                        class="text-lg font-semibold text-gray-800 dark:text-white"
                    >
                        <slot name="title"></slot>
                    </h2>
                </div>

                <!-- Right side header content -->
                <div class="flex items-center gap-4">
                    <slot name="header" />
                </div>
            </n-layout-header>

            <!-- Content Area -->
            <n-layout-content
                class="p-6 bg-gray-50 dark:bg-gray-900"
                :native-scrollbar="false"
            >
                <div class="mx-auto">
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
