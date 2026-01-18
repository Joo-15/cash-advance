<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

// Define layout (akan di-override oleh app.js, tapi safe)
// defineOptions({
//     layout: AuthenticatedLayout,
// });
</script>

<template>
    <Head title="Dashboard" />

    <template> Dashboard </template>

    <div>
        <h1 class="text-2xl font-bold">Dashboard Content</h1>
        <p>Welcome to your dashboard!</p>
    </div>
</template>
