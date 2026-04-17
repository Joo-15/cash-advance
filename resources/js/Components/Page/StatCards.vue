<script setup>
import { onMounted, ref, watch } from "vue";
import StatsCard from "../Templates/StatsCard.vue";

const props = defineProps({
    stats: { type: Array, default: () => [] },
    statData: { type: Object, default: () => ({}) },
});

const animatedCards = ref(false);
const emit = defineEmits(["update:status"]);

watch(
    () => props.statData,
    () => {
        animatedCards.value = false;
        setTimeout(() => {
            animatedCards.value = true;
        }, 50);
    },
);

onMounted(() => {
    setTimeout(() => {
        animatedCards.value = true;
    }, 0);
});
</script>

<template>
    <div
        class="transform transition-all duration-500 ease-out"
        :class="
            animatedCards
                ? 'translate-y-0 opacity-100'
                : 'translate-y-6 opacity-0'
        "
    >
        <div
            v-if="stats.length > 0"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6"
        >
            <div
                v-for="(stat, index) in stats"
                :key="stat.key"
                class="group relative bg-white rounded-2xl px-3 py-2 border border-gray-100 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300 ease-out overflow-hidden"
                :style="{ transitionDelay: `${index * 60}ms` }"
                :class="
                    animatedCards
                        ? 'translate-y-0 opacity-100'
                        : 'translate-y-4 opacity-0'
                "
            >
                <!-- Accent bar top -->
                <div
                    class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-400 via-violet-400 to-sky-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                />

                <!-- Subtle background glow on hover -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-indigo-50/0 to-violet-50/0 group-hover:from-indigo-50/50 group-hover:to-violet-50/30 transition-all duration-300 rounded-2xl pointer-events-none"
                />

                <div class="relative">
                    <StatsCard :stat="stat" :value="statData" />
                </div>
            </div>
        </div>
    </div>
</template>
