<script setup>
import StatsCard from "../Templates/StatsCard.vue";

// Props dari parent
const props = defineProps({
    stats: { type: Array, default: () => [] },
    statData: { type: Object, default: () => ({}) },
});

// Emit events ke parent
const emit = defineEmits(["update:status"]);
</script>
<template>
    <div
        v-if="stats.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
    >
        <div
            v-for="stat in stats"
            :key="stat.key"
            class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition"
        >
            <StatsCard :stat="stat" :value="statData?.[stat.key] ?? 0" />
        </div>
    </div>
</template>
