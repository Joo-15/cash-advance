import { defineStore } from "pinia";
import { ref, computed, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const STORAGE_KEY = "sidebar-collapsed";

export const useSidebarStore = defineStore("sidebar", () => {
    /* =====================
       EXTERNAL SOURCE
    ===================== */
    const page = usePage();

    /* =====================
       STATE
    ===================== */
    const collapsed = ref(false);
    const expandedKeys = ref([]);
    const isMobile = ref(false);
    const isInitialized = ref(false);

    /* =====================
       COMPUTED
    ===================== */
    const activeKey = computed(() => page.url.split("?")[0]);
    const user = computed(() => page.props.auth?.user);

    /* =====================
       HELPERS
    ===================== */
    const checkMobile = () => {
        isMobile.value = window.matchMedia("(max-width: 768px)").matches;
    };

    const getFirstSegment = (url = page.url) => {
        const clean = url.split("?")[0];
        const segments = clean.split("/").filter(Boolean);
        return segments.length ? "/" + segments[0] : "/";
    };

    /* =====================
       INIT (ANTI FLICKER)
    ===================== */
    const init = () => {
        checkMobile();

        collapsed.value = isMobile.value
            ? true
            : localStorage.getItem(STORAGE_KEY) === "true";

        expandedKeys.value = [getFirstSegment()];
        isInitialized.value = true;
    };

    /* =====================
       WATCHERS
    ===================== */

    // simpan collapsed (desktop only)
    watch(collapsed, (val) => {
        if (!isInitialized.value) return;
        if (isMobile.value) return;

        localStorage.setItem(STORAGE_KEY, String(val));
    });

    // react ke perubahan URL (Inertia navigation)
    watch(
        () => page.url,
        (url) => {
            expandedKeys.value = [getFirstSegment(url)];
        },
        { immediate: true },
    );

    /* =====================
       ACTIONS
    ===================== */
    const setCollapsed = (val) => {
        collapsed.value = val;
    };

    const setExpandedKeys = (keys) => {
        expandedKeys.value = keys;
    };

    return {
        // state
        collapsed,
        expandedKeys,
        isMobile,
        isInitialized,

        // computed
        activeKey,
        user,

        // actions
        init,
        setCollapsed,
        setExpandedKeys,
    };
});
