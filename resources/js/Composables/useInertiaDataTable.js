// composables/useInertiaDataTable.js
import { ref, reactive, watch, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";

export function useInertiaDataTable({
    route,
    filters: initialFilters = {},
    initialPageSize = 10,
    only = [],
    debounceTime = 200,
}) {
    const loadingSearch = ref(false);
    const loadingTable = ref(false);
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

        // console.log("🚀 Fetching with params:", params);

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
                // loadingReset.value = false;
                // loadingStatus.value = false;
                // loadingSort.value = false;
                loadingTable.value = false;
            },
        });
    };

    // ============ SEARCH & STATUS (Debounced) ============
    const debouncedFetch = debounce((page) => {
        currentPage.value = 1;
        fetchData(page);
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
        () => {
            loadingTable.value = true;
            debouncedFetch();
        },
    );

    // Cancel debounce saat component unmount
    onBeforeUnmount(() => {
        debouncedFetch.cancel();
    });

    // ============ PAGINATION ============
    const handlePageChange = (page) => {
        currentPage.value = page;
        loadingTable.value = true;
        debouncedFetch(page);
    };

    const handlePageSizeChange = (size) => {
        filters.pageSize = size;
        currentPage.value = 1;
        fetchData();
    };

    // ============ SORTING ============
    const handleSortChange = (sortOptions) => {


        if (!sortOptions || !sortOptions.field) {
            // Reset sorting
            filters.sort = null;
            filters.order = null;
            // console.log("📊 Sort reset - filters after:", filters);
        } else {
            // InertiaDataTable mengirim format: { field: 'tanggal', order: 'asc'/'desc' }
            filters.sort = sortOptions.field;
            filters.order = sortOptions.order; // Langsung pakai 'asc' atau 'desc'

            // console.log("📊 Sort updated:", {
            //     field: sortOptions.field,
            //     order: sortOptions.order,
            //     filters: filters
            // });
        }


    };

    // ============ CLEAR FILTERS ============
    const handleClear = () => {
        // console.log("🧹 useInertiaDataTable - Clearing all filters");
        // console.log("Before clear:", {
        //     sort: filters.sort,
        //     order: filters.order,
        //     search: filters.search,
        //     status: filters.status
        // });

        loadingTable.value = true;

        // Reset semua filters
        filters.search = "";
        filters.status = null;
        filters.sort = null;
        filters.order = null;

        // console.log("After clear:", {
        //     sort: filters.sort,
        //     order: filters.order,
        //     search: filters.search,
        //     status: filters.status
        // });

        debouncedFetch();
    };

    return {
        loadingSearch,
        loadingTable,
        filters,
        debouncedFetch,
        handlePageChange,
        handlePageSizeChange,
        handleSortChange,
        handleClear,
        fetchData,
    };
}
