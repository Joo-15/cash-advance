<!-- Components/StatsCard.vue -->
<script setup>
import {
    DocumentOutline,
    CheckmarkOutline,
    TimeOutline,
    CloseOutline,
} from "@vicons/ionicons5";
import { NIcon, NTag } from "naive-ui";

const props = defineProps({
    stat: { type: Object, required: true },
    value: { type: Number, default: 0 },
});

const iconMap = {
    document: DocumentOutline,
    check: CheckmarkOutline,
    clock: TimeOutline,
    close: CloseOutline,
};

const colorClasses = {
    blue: { icon: "text-blue-600", bg: "bg-blue-50" },
    green: { icon: "text-green-600", bg: "bg-green-50" },
    orange: { icon: "text-orange-600", bg: "bg-orange-50" },
    red: { icon: "text-red-600", bg: "bg-red-50" },
};

const colors = colorClasses[props.stat.color] || colorClasses.blue;
</script>

<template>
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm font-medium text-gray-600">
            {{ stat.label }}
        </p>
        <div
            :class="[
                'w-10 h-10 rounded-lg flex items-center justify-center',
                colors.bg,
            ]"
        >
            <n-icon
                :component="iconMap[stat.icon]"
                :class="colors.icon"
                size="20"
            />
        </div>
    </div>
    <div>
        <p class="text-3xl font-bold text-gray-900 py-2">
            {{ value }}
        </p>
        <!-- <n-tag :type="stat.tagType" :bordered="false">
            {{ stat.subLabel }}
        </n-tag> -->
    </div>
</template>
