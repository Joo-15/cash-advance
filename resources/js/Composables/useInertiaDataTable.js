// composables/useInertiaDataTable.js
import { ref, reactive, watch, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";

export function useInertiaDataTable({
    route,
    filters: initialFilters = {},
    initialPageSize = 10,
    only = [],
    debounceTime = 300,
}) {
    const loadingSearch = ref(false);
    const loadingReset = ref(false);
    const currentPage = ref(1);

    // Reactive filter lokal
    const filters = reactive({
        search: initialFilters.search || "",
        status: initialFilters.status || null,
        pageSize: initialFilters.pageSize || initialPageSize,
        sort: initialFilters.sort || null,
        order: initialFilters.order || null,
    });

    // ============ FETCH DATA ============
    const fetchData = (page = currentPage.value) => {
        const params = {
            page,
            per_page: filters.pageSize,
        };

        if (filters.search) params.search = filters.search;
        if (filters.status) params.status = filters.status;
        if (filters.sort && filters.order) {
            params.sort = filters.sort;
            params.order = filters.order;
        }

        console.log("🚀 Fetching with params:", params);

        router.get(route, params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only,
            onStart: () => {
                if (filters.search !== initialFilters.search) {
                    loadingSearch.value = true;
                }
            },
            onFinish: () => {
                loadingSearch.value = false;
                loadingReset.value = false;
            },
        });
    };

    // ============ SEARCH & STATUS (Debounced) ============
    const debouncedFetch = debounce(() => {
        currentPage.value = 1;
        fetchData();
    }, debounceTime);

    // Watch search
    watch(
        () => filters.search,
        () => {
            loadingSearch.value = true;
            debouncedFetch();
        },
    );

    // Watch status
    watch(
        () => filters.status,
        () => debouncedFetch(),
    );

    // Cancel debounce saat component unmount
    onBeforeUnmount(() => {
        debouncedFetch.cancel();
    });

    // ============ PAGINATION ============
    const handlePageChange = (page) => {
        currentPage.value = page;
        fetchData(page);
    };

    const handlePageSizeChange = (size) => {
        filters.pageSize = size;
        currentPage.value = 1;
        fetchData();
    };

    // ============ SORTING ============
    const handleSortChange = (sortOptions) => {
        console.log("📊 useInertiaDataTable - Sort options:", sortOptions);

        if (!sortOptions) {
            // Reset sorting
            filters.sort = null;
            filters.order = null;
        } else {
            // Update filters dengan sorting baru
            filters.sort = sortOptions.field;
            filters.order = sortOptions.order;
        }

        // Reset ke halaman 1 dan fetch data
        currentPage.value = 1;
        fetchData();
    };

    // ============ CLEAR FILTERS ============
    const handleClear = () => {
        console.log("🧹 useInertiaDataTable - Clearing all filters");
        console.log("Before clear:", {
            sort: filters.sort,
            order: filters.order,
        });

        loadingReset.value = true;

        // Reset semua filters
        filters.search = "";
        filters.status = null;
        filters.sort = null;
        filters.order = null;

        console.log("After clear:", {
            sort: filters.sort,
            order: filters.order,
        });

        currentPage.value = 1;
        fetchData();
    };

    return {
        loadingSearch,
        loadingReset,
        filters,
        handlePageChange,
        handlePageSizeChange,
        handleSortChange,
        handleClear,
        fetchData,
    };
}
