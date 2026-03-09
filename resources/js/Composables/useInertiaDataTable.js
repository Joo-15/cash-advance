import { ref, watch, onBeforeUnmount, reactive } from "vue";
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
    });

    // Handler fetch data
    const fetchData = (page = currentPage.value) => {
        router.get(
            route,
            {
                page,
                search: filters.search,
                status: filters.status,
                per_page: filters.pageSize,
            },
            {
                preserveState: true,
                preserveScroll: true,
                only,
                onFinish: () => (
                    loadingSearch.value = false,
                    loadingReset.value = false
                ),
            }
        );
    };

    // Debounce lodash untuk search & status
    const debouncedFetch = debounce(() => {
        currentPage.value = 1;
        fetchData();
    }, debounceTime);

    // Watch search → pakai debounce
    watch(() => filters.search, () => {
        loadingSearch.value = true;
        debouncedFetch();
    });

    // Watch status → pakai debounce
    watch(() => filters.status, () => debouncedFetch());

    // Cancel debounce saat component unmount
    onBeforeUnmount(() => {
        debouncedFetch.cancel();
    });

    // Pagination & PageSize tetap pakai fetchData langsung
    const handlePageChange = (page) => {
        currentPage.value = page;
        fetchData(page);
    };

    const handlePageSizeChange = (size) => {
        filters.pageSize = size;
        currentPage.value = 1;
        fetchData();
    };

    const handleClear = () => {
        loadingReset.value = true;
        filters.search = "";
        filters.status = null;
        currentPage.value = 1;
        fetchData();
    };

    return {
        loadingSearch,
        loadingReset,
        filters,
        handlePageChange,
        handlePageSizeChange,
        handleClear,
    };
}