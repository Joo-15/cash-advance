<script setup>
import { NButton, NIcon } from "naive-ui";
import { Add, DownloadOutline } from "@vicons/ionicons5";
import { onMounted, ref } from "vue";

// Props dari parent
const props = defineProps({
    title: { type: String, required: true },
    showDownload: { type: Boolean, default: false },
    showAdd: { type: Boolean, default: false },
    addButtonText: { type: String, default: "Tambah Data" },
    downloadButtonText: { type: String, default: "Download Excel" },
});

const isPageLoaded = ref(false);

// Emit events ke parent
const emit = defineEmits(["add", "download"]);

onMounted(() => {
    setTimeout(() => {
        isPageLoaded.value = true;
    }, 0);
});
</script>
<template>
    <div
        class="transform transition-all duration-100"
        :class="
            isPageLoaded
                ? 'translate-y-0 opacity-100'
                : 'translate-y-[-20px] opacity-0'
        "
    >
        <div
            class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between py-2"
        >
            <h1
                class="text-2xl font-bold bg-gradient-to-r from-green-600 via-green-500 to-green-500 bg-clip-text text-transparent p-2"
            >
                {{ title }}
            </h1>
            <div class="flex flex-col gap-2 w-full md:flex-row md:w-auto">
                <n-button
                    v-if="showDownload"
                    ghost
                    type="primary"
                    class="!w-full md:!w-auto"
                    @click="$emit('download')"
                >
                    <template #icon>
                        <n-icon><DownloadOutline /></n-icon>
                    </template>
                    {{ downloadButtonText }}
                </n-button>

                <n-button
                    v-if="showAdd"
                    color="#8a2be2"
                    class="!w-full md:!w-auto"
                    @click="$emit('add')"
                >
                    <template #icon>
                        <n-icon><Add /></n-icon>
                    </template>
                    {{ addButtonText }}
                </n-button>
            </div>
        </div>
    </div>
</template>
