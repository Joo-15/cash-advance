<!-- Components/StatsCard.vue -->
<script setup>
import {
    DocumentOutline,
    CheckmarkOutline,
    TimeOutline,
    CloseOutline,
} from "@vicons/ionicons5";
import { NIcon, NProgress, NTag } from "naive-ui";

const props = defineProps({
    stat: { type: Object, required: true },
    value: { type: Object, required: true },
});
console.log("stat", props.stat);
console.log("value", props.value);
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
// Fungsi untuk menghitung persentase
const getPercentage = (disetujui, total) => {
    if (!total || total === 0) return 0;
    return Math.round((disetujui / total) * 100);
};
</script>

<template>
    <div class="flex items-center justify-between">
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
        <!-- Total Pengajuan -->
        <div v-if="stat.key === 'total_pengajuan'">
            <p class="text-3xl font-bold text-blue-700 py-2">
                {{ value.total_pengajuan }}
            </p>

            <p class="font-thin text-gray-600 text-xs py-2">
                Semua status pengajuan
            </p>
        </div>

        <!-- Pengajuan Disetujui -->
        <div v-if="stat.key === 'pengajuan_disetujui'">
            <p class="text-3xl font-bold text-green-700 py-2">
                {{ value.pengajuan_disetujui }}
            </p>
            <n-progress
                color="#10b981"
                type="line"
                :percentage="
                    getPercentage(
                        value.pengajuan_disetujui,
                        value.total_pengajuan,
                    )
                "
                indicator-placement="inside"
                processing
            />
            <p class="font-thin text-gray-600 text-xs py-2">
                {{ value.pengajuan_disetujui }} dari
                {{ value.total_pengajuan }} pengajuan
            </p>
        </div>

        <!-- Pengajuan Pending -->
        <div v-if="stat.key === 'pengajuan_pending'">
            <p class="text-3xl font-bold text-yellow-700 py-2">
                {{ value.pengajuan_pending }}
            </p>
            <n-progress
                color="#f97316"
                type="line"
                :percentage="
                    getPercentage(
                        value.pengajuan_pending,
                        value.total_pengajuan,
                    )
                "
                indicator-placement="inside"
                processing
            />
            <p class="font-thin text-gray-600 text-xs py-2">
                Menunggu persetujuan segera
            </p>
        </div>

        <!-- Pengajuan Ditolak -->
        <div v-if="stat.key === 'pengajuan_ditolak'">
            <p class="text-3xl font-bold text-red-700 py-2">
                {{ value.pengajuan_ditolak }}
            </p>
            <n-progress
                color="#ef4444"
                type="line"
                :percentage="
                    getPercentage(
                        value.pengajuan_ditolak,
                        value.total_pengajuan,
                    )
                "
                indicator-placement="inside"
                processing
            />
            <p class="font-thin text-gray-600 text-xs py-2">
                {{
                    value.pengajuan_ditolak === 0
                        ? "Tidak ada penolakan"
                        : value.pengajuan_ditolak + " Penolakan"
                }}
            </p>
        </div>
    </div>
</template>
