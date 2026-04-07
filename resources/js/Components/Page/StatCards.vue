<script setup>
import { onMounted, ref, watch } from "vue";
import StatsCard from "../Templates/StatsCard.vue";

// Props dari parent
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
        class="transform transition-all duration-300"
        :class="
            animatedCards
                ? 'translate-y-0 opacity-100 scale-100'
                : 'translate-y-10 opacity-0 scale-95'
        "
    >
        <div
            v-if="stats.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4"
        >
            <div
                v-for="stat in stats"
                :key="stat.key"
                class="bg-white rounded-xl py-2 px-5 shadow-sm border border-gray-100 hover:shadow-md transition hover:scale-[1.02]"
            >
                <StatsCard :stat="stat" :value="statData" />
            </div>
        </div>
    </div>
</template>
