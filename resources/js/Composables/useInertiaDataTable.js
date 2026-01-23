import { ref, watch, onBeforeUnmount } from "vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";

export function useInertiaDataTable({
    route,
    initialSearch = "",
    initialPageSize = 10,
    only = [],
    debounceTime = 300,
}) {
    const search = ref(initialSearch || "");
    const pageSize = ref(Number(initialPageSize || 10));

    const doSearch = debounce((value) => {
        router.get(
            route,
            {
                search: value,
                page: 1,
                per_page: pageSize.value,
            },
            {
                preserveState: true,
                replace: true,
                only,
            },
        );
    }, debounceTime);

    const handlePageChange = (page) => {
        router.get(
            route,
            {
                page,
                search: search.value,
                per_page: pageSize.value,
            },
            {
                preserveState: true,
                preserveScroll: true,
                only,
            },
        );
    };

    const handlePageSizeChange = (size) => {
        pageSize.value = size;

        router.get(
            route,
            {
                page: 1,
                search: search.value,
                per_page: size,
            },
            {
                preserveState: true,
                only,
            },
        );
    };

    watch(search, (value) => doSearch(value));

    onBeforeUnmount(() => doSearch.cancel());

    return {
        search,
        pageSize,
        handlePageChange,
        handlePageSizeChange,
    };
}
