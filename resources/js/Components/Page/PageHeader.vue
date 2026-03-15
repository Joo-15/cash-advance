<script setup>
import { NButton, NSpace, NIcon } from "naive-ui";
import { Add, DownloadOutline } from "@vicons/ionicons5";

// Props dari parent
const props = defineProps({
    title: { type: String, required: true },
    showDownload: { type: Boolean, default: false },
    showAdd: { type: Boolean, default: false },
    addButtonText: { type: String, default: "Tambah Data" },
    downloadButtonText: { type: String, default: "Download Excel" },
});

// Emit events ke parent
const emit = defineEmits(["add", "download"]);
</script>
<template>
    <div
        class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between py-2"
    >
        <h1 class="text-2xl font-bold text-gray-800 p-2">
            {{ title }}
        </h1>

        <n-space wrap>
            <n-button
                v-if="showDownload"
                ghost
                type="primary"
                @click="$emit('download')"
            >
                <template #icon>
                    <n-icon><DownloadOutline /></n-icon>
                </template>
                {{ downloadButtonText }}
            </n-button>

            <n-button v-if="showAdd" color="#8a2be2" @click="$emit('add')">
                <template #icon>
                    <n-icon><Add /></n-icon>
                </template>
                {{ addButtonText }}
            </n-button>
        </n-space>
    </div>
</template>
