// composables/useInertiaDataTable.js
import { ref, reactive, watch, onBeforeUnmount, h, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import { NButton, NIcon, NSpace, NTag, NTooltip } from "naive-ui";
import {
    PencilOutline,
    TrashOutline,
    EyeOutline,
    CheckmarkOutline,
    SendSharp,
    ReceiptOutline,
    AttachOutline,
} from "@vicons/ionicons5";

/**
 ===================================================
 KONSTANTA & DEFAULT CONFIG
 ===================================================
 */

// Konstanta untuk konfigurasi default tabel
const DEFAULT_TABLE_CONFIG = {
    currency: "IDR",
    dateFormat: "DD-MM-YYYY",
    actionSize: "small",
    ellipsisTooltip: true,
};

// Predefined sorters untuk berbagai tipe data
const SORTERS = {
    string: (key) => (row1, row2) =>
        String(row1[key] || "").localeCompare(String(row2[key] || "")),

    number: (key) => (row1, row2) => (row1[key] || 0) - (row2[key] || 0),

    date: (key) => (row1, row2) =>
        new Date(row1[key] || 0).getTime() - new Date(row2[key] || 0).getTime(),

    currency: (key) => (row1, row2) => {
        const parse = (val) =>
            parseFloat(String(val || "0").replace(/[^0-9.-]+/g, ""));
        return parse(row1[key]) - parse(row2[key]);
    },
};

/**
 ===================================================
 MAIN COMPOSABLE
 ===================================================
 */

export function useDataTable({
    route,
    filters: initialFilters = {},
    initialPageSize = 10,
    only = [],
    debounceTime = 200,
    tableConfig = {}, // Konfigurasi tambahan untuk tabel
}) {
    // Gabungkan konfigurasi tabel
    const config = { ...DEFAULT_TABLE_CONFIG, ...tableConfig };

    /**
     ===================================================
     STATE MANAGEMENT
     ===================================================
     */

    // Loading states
    const loadingSearch = ref(false);
    const loadingTable = ref(false);
    const currentPage = ref(1);

    // Sort state
    const activeSortKey = ref(null);
    const activeSortOrder = ref(null);

    // Reactive filter lokal
    const filters = reactive({
        search: initialFilters.search || "",
        status: initialFilters.status || null,
        pageSize: initialFilters.pageSize || initialPageSize,
        sort: initialFilters.sort || null,
        order: initialFilters.order || null,
    });

    /**
     ===================================================
     RENDER FUNCTIONS (dari useTableColumns)
     ===================================================
     */

    const renderCurrency = (value, currency = config.currency) => {
        if (!value && value !== 0)
            return h("span", { class: "text-gray-400" }, "-");

        const formatter = new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });

        return h("span", { class: "font-mono" }, formatter.format(value));
    };

    const renderDate = (value, format = config.dateFormat) => {
        if (!value) return h("span", { class: "text-gray-400" }, "-");

        const date = new Date(value);
        if (isNaN(date.getTime()))
            return h("span", { class: "text-gray-400" }, "-");

        const day = date.getDate().toString().padStart(2, "0");
        const month = (date.getMonth() + 1).toString().padStart(2, "0");
        const year = date.getFullYear();

        const formatted = format
            .replace("DD", day)
            .replace("MM", month)
            .replace("YYYY", year)
            .replace("HH", date.getHours().toString().padStart(2, "0"))
            .replace("mm", date.getMinutes().toString().padStart(2, "0"))
            .replace("ss", date.getSeconds().toString().padStart(2, "0"));

        return h("span", {}, formatted);
    };

    const renderStatus = (value, statusMap = {}) => {
        const defaultMap = {
            pending: { type: "warning", label: "Pending" },
            approved: { type: "success", label: "Approved" },
            rejected: { type: "error", label: "Rejected" },
            draft: { type: "info", label: "Draft" },
            default: { type: "default", label: value },
        };

        const map = { ...defaultMap, ...statusMap };
        const status = map[value] || map.default;

        return h(
            NTag,
            {
                type: status.type,
                size: "small",
                round: true,
                bordered: false,
            },
            { default: () => status.label },
        );
    };

    const renderAttachment = (value) => {
        // Cek kosong
        if (!value || (typeof value === 'string' && value.trim() === '')) {
            return h(
                NTag,
                {
                    type: "warning",
                    size: "small",
                    round: true,
                    bordered: false,
                },
                { default: () => "Belum Uplaod" }
            );
        }

        // Sudah ada attachment
        const fileName = value.split('/').pop();

        return h(
            NTag,
            {
                type: "success",
                size: "small",
                round: true,
                bordered: false,
                onClick: () => window.open(value, '_blank'),
            },
            {
                default: () => fileName,
                icon: () => h(NIcon, { size: 18 }, { default: () => h(AttachOutline) })
            }
        );
    };

    const renderBoolean = (value, booleanMap = {}) => {
        const map = {
            true: { type: "success", label: "Yes", icon: null },
            false: { type: "error", label: "No", icon: null },
            ...booleanMap,
        };

        const boolValue = value ? "true" : "false";
        const config = map[boolValue];

        return h(
            NTag,
            {
                type: config.type,
                size: "small",
            },
            { default: () => config.label },
        );
    };

    const createActionButton = ({
        type,
        icon,
        label, // Tambahkan label
        onClick,
        props = {},
        size,
        tooltip,
        style,
    }) => {
        // Jika ada label, buat button dengan teks
        if (label) {
            const button = h(
                NButton,
                {
                    strong: true,
                    type,
                    size,
                    onClick,
                    style,
                    ...props,
                },
                {
                    default: () => label, // Teks tombol
                    ...(icon
                        ? {
                            icon: () =>
                                h(NIcon, null, { default: () => h(icon) }),
                        }
                        : {}),
                },
            );

            if (tooltip) {
                return h(
                    NTooltip,
                    { trigger: "hover", placement: "top" },
                    {
                        trigger: () => button,
                        default: () => tooltip,
                    },
                );
            }
            return button;
        }

        // Jika tidak ada label, buat button circle dengan icon saja
        const button = h(
            NButton,
            {
                strong: true,
                secondary: true,
                circle: true,
                type,
                size,
                onClick,
                style,
                ...props,
            },
            {
                icon: () => h(NIcon, null, { default: () => h(icon) }),
            },
        );

        if (tooltip) {
            return h(
                NTooltip,
                { trigger: "hover", placement: "top" },
                {
                    trigger: () => button,
                    default: () => tooltip,
                },
            );
        }

        return button;
    };

    const renderActions = (row, actionConfig = {}, actions = {}) => {
        // Ambil status dari row
        const status = row?.status?.toLowerCase?.() || "";
        const isDisbursed = status === "disbursed";

        // Gabungkan actionConfig dengan kondisi status
        const mergedActionConfig = {
            ...actionConfig,
            // Jika status disbursed, nonaktifkan edit, delete, proses
            showEdit:
                actionConfig.showEdit !== undefined
                    ? typeof actionConfig.showEdit === "function"
                        ? actionConfig.showEdit(row)
                        : !isDisbursed && actionConfig.showEdit
                    : !isDisbursed,
            showDelete:
                actionConfig.showDelete !== undefined
                    ? typeof actionConfig.showDelete === "function"
                        ? actionConfig.showDelete(row)
                        : !isDisbursed && actionConfig.showDelete
                    : !isDisbursed,
            showProses:
                actionConfig.showProses !== undefined
                    ? typeof actionConfig.showProses === "function"
                        ? actionConfig.showProses(row)
                        : actionConfig.showProses
                    : true,
            // Detail dan View tetap tampil (bisa diatur sendiri)
            showDetail:
                actionConfig.showDetail !== undefined
                    ? typeof actionConfig.showDetail === "function"
                        ? actionConfig.showDetail(row)
                        : actionConfig.showDetail
                    : true,
            showView:
                actionConfig.showView !== undefined
                    ? typeof actionConfig.showView === "function"
                        ? actionConfig.showView(row)
                        : actionConfig.showView
                    : true,
        };

        const {
            showEdit = true,
            showDelete = true,
            showView = false,
            showDetail = false,
            showProses = false,
            showCustom = false,
            customButtons = [],
            size = config.actionSize,
            placement = "center",
            editProps = {},
            deleteProps = {},
            viewProps = {},
        } = mergedActionConfig;

        const buttons = [];

        if (showView && actions.onView) {
            buttons.push(
                createActionButton({
                    type: "info",
                    icon: EyeOutline,
                    onClick: () => actions.onView(row),
                    props: viewProps,
                    size,
                    tooltip: "Lihat Detail",
                }),
            );
        }

        if (showProses && actions.onProses) {
            buttons.push(
                createActionButton({
                    // label: "Proses", // Tambahkan label
                    icon: ReceiptOutline, // Atau bisa pakai icon
                    type: "info",
                    onClick: () => actions.onProses(row.id),
                    props: editProps,
                    size,
                    tooltip: "Proses",
                }),
            );
        }

        if (showDetail && actions.onDetail) {
            buttons.push(
                createActionButton({
                    type: "success",
                    icon: EyeOutline,
                    onClick: () =>
                        actions.onDetail(row.cash_advance_id || row.id), // mengambil id cash_advance
                    props: {
                        size,
                        variant: "light",
                        // Optional: Tambahkan loading state
                        loading: row._loading_detail === true,
                    },
                    size,
                    tooltip: "Lihat Detail",
                }),
            );
        }

        if (showEdit && actions.onEdit) {
            buttons.push(
                createActionButton({
                    type: "info",
                    icon: PencilOutline,
                    onClick: () => actions.onEdit(row),
                    props: editProps,
                    size,
                    tooltip: "Edit",
                }),
            );
        }

        if (showDelete && actions.onDelete) {
            buttons.push(
                createActionButton({
                    type: "error",
                    icon: TrashOutline,
                    onClick: () => actions.onDelete(row.id || row),
                    props: deleteProps,
                    size,
                    tooltip: "Hapus",
                }),
            );
        }

        if (showCustom && customButtons.length) {
            customButtons.forEach((btn, index) => {
                buttons.push(
                    createActionButton({
                        type: btn.type || "default",
                        icon: btn.icon,
                        onClick: () => btn.onClick(row),
                        props: btn.props,
                        size,
                        tooltip: btn.tooltip,
                        style: buttons.length > 0 ? "margin-left: 6px" : "",
                    }),
                );
            });
        }

        return buttons.length
            ? h(
                NSpace,
                { align: "center", justify: placement, size: 4 },
                { default: () => buttons },
            )
            : null;
    };

    /**
     ===================================================
     CORE TABLE COLUMN FUNCTIONS
     ===================================================
     */

    const createBaseColumn = (column) => ({
        key: column.key,
        title: column.title,
        width: column.width,
        minWidth: column.minWidth,
        maxWidth: column.maxWidth,
        align: column.align || "left",
        fixed: column.fixed,
        className: column.className,
        titleClassName: column.titleClassName,
        titleAlign: column.titleAlign,
        titleColSpan: column.titleColSpan,
        colSpan: column.colSpan,
        rowSpan: column.rowSpan,
    });

    const applySorter = (baseColumn, column) => {
        if (!column.sorter) return;

        if (typeof column.sorter === "function") {
            baseColumn.sorter = column.sorter;
            return;
        }

        if (column.sorter === true) {
            const type = column.type || "string";
            baseColumn.sorter =
                SORTERS[type]?.(column.key) || SORTERS.string(column.key);
            return;
        }

        if (typeof column.sorter === "string") {
            baseColumn.sorter =
                SORTERS[column.sorter.toLowerCase()]?.(column.key) ||
                SORTERS.string(column.key);
            return;
        }

        if (typeof column.sorter === "object") {
            baseColumn.sorter = {
                multiple: column.sorter.multiple || false,
                compare: column.sorter.compare || SORTERS.string(column.key),
            };
        }
    };

    const applyEllipsis = (baseColumn, column) => {
        if (!column.ellipsis && !column.tooltip) return;

        if (column.ellipsis === true) {
            baseColumn.ellipsis = {
                tooltip: config.ellipsisTooltip,
            };
        } else if (typeof column.ellipsis === "object") {
            baseColumn.ellipsis = {
                tooltip: config.ellipsisTooltip,
                ...column.ellipsis,
            };
        } else if (column.tooltip) {
            baseColumn.ellipsis = {
                tooltip: true,
            };
        }
    };

    const applyRenderer = (baseColumn, column, actions) => {
        if (column.render) {
            baseColumn.render = column.render;
            return;
        }

        switch (column.type) {
            case "currency":
                baseColumn.align = column.align || "right";
                baseColumn.render = (row) => {
                    // Cara mengakses nested key (cash_advance.amount)
                    const value = getNestedValue(row, column.key);
                    return renderCurrency(value, column.currency);
                };
                break;

                // Helper function untuk mengakses nested object
                function getNestedValue(obj, path) {
                    console.log("aksiObject", obj);
                    if (!obj || !path) return null;

                    // Pisahkan path dengan titik: "cash_advance.amount" -> ["cash_advance", "amount"]
                    const keys = path.split(".");

                    // Akses nested value
                    return keys.reduce((current, key) => {
                        return current && current[key] !== undefined
                            ? current[key]
                            : null;
                    }, obj);
                }

                // Function untuk render currency
                function renderCurrency(value, currency = "IDR") {
                    if (value === null || value === undefined || value === "") {
                        return "Rp 0";
                    }

                    // Konversi ke number jika string
                    const numValue =
                        typeof value === "string"
                            ? parseFloat(value.replace(/[^\d.-]/g, ""))
                            : value;

                    if (isNaN(numValue)) return "Rp 0";

                    // Format dengan Intl.NumberFormat
                    return new Intl.NumberFormat("id-ID", {
                        style: "currency",
                        currency: currency,
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    }).format(numValue);
                }

            case "date":
                baseColumn.render = (row) => {
                    // Mengambil nilai dari nested object
                    const value = getNestedValue(row, column.key);
                    return renderDate(value, column.dateFormat);
                };
                break;

            case "status":
                baseColumn.render = (row) =>
                    renderStatus(row[column.key], column.statusMap);
                break;

            case "attachment":
                baseColumn.render = (row) =>
                    renderAttachment(row[column.key]);
                break;

            case "action":
                baseColumn.render = (row) =>
                    renderActions(row, column.actionConfig, actions);
                baseColumn.align = column.align || "center";
                break;

            case "boolean":
                baseColumn.render = (row) =>
                    renderBoolean(row[column.key], column.booleanMap);
                break;
        }
    };

    /**
     * CREATE COLUMNS - Fungsi utama untuk membuat konfigurasi kolom
     */
    const createColumns = (columnConfig = [], actions = {}) => {
        console.log("columnConfig", columnConfig);
        return columnConfig.map((column) => {
            const baseColumn = createBaseColumn(column);

            // Tentukan sortOrder berdasarkan state aktif
            if (activeSortKey.value === column.key) {
                baseColumn.sortOrder = activeSortOrder.value;
            } else if (column.sortOrder) {
                baseColumn.sortOrder = column.sortOrder;
            } else {
                baseColumn.sortOrder = false;
            }

            applySorter(baseColumn, column);
            applyEllipsis(baseColumn, column);
            applyRenderer(baseColumn, column, actions);

            return baseColumn;
        });
    };

    /**
     * Helper untuk mendefinisikan kolom
     */
    const defineColumns = (columns) => columns;

    /**
     * Update sort order dalam konfigurasi kolom
     */
    const updateSortOrder = (columns, sortKey, sortOrder) => {
        updateSort(sortKey, sortOrder);
        return columns.map((col) => ({
            ...col,
            sortOrder: col.key === sortKey ? sortOrder : false,
        }));
    };

    /**
     * Reset sort order dalam konfigurasi kolom
     */
    const resetSortOrderInConfig = (columnConfig) => {
        resetSort();
        return columnConfig.map((col) => ({
            ...col,
            sortOrder: false,
        }));
    };

    /**
     * Buat konfigurasi kolom dengan sort order yang aktif
     */
    const createColumnConfigWithSort = (baseConfig) => {
        return baseConfig.map((col) => ({
            ...col,
            sortOrder: getSortOrder(col.key),
        }));
    };

    /**
     ===================================================
     SORT MANAGEMENT FUNCTIONS
     ===================================================
     */

    /**
     * RESET SORT - Fungsi untuk mereset icon sorting
     */
    const resetSort = () => {
        activeSortKey.value = null;
        activeSortOrder.value = null;
    };

    /**
     * Set sort order
     */
    const setSort = (key, order) => {
        activeSortKey.value = key;
        activeSortOrder.value = order;
    };

    /**
     * Update sort dari external
     */
    const updateSort = (sortKey, sortOrder) => {
        if (
            !sortKey ||
            sortOrder === false ||
            sortOrder === null ||
            sortOrder === undefined
        ) {
            resetSort();
        } else {
            setSort(sortKey, sortOrder);
        }
    };

    /**
     * Cek apakah ada sort aktif
     */
    const hasActiveSort = () => {
        return activeSortKey.value !== null;
    };

    /**
     * Dapatkan sort order untuk kolom tertentu
     */
    const getSortOrder = (key) => {
        return activeSortKey.value === key ? activeSortOrder.value : false;
    };

    /**
     * Dapatkan informasi sort aktif
     */
    const getActiveSort = () => ({
        key: activeSortKey.value,
        order: activeSortOrder.value,
    });

    /**
     * Computed untuk hasActiveSort (untuk binding ke template)
     */
    const hasActiveSortValue = computed(() => hasActiveSort());

    /**
     ===================================================
     DATA FETCHING FUNCTIONS
     ===================================================
     */

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
                loadingTable.value = false;
            },
        });
    };

    // Debounced fetch untuk search dan status
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

    /**
     ===================================================
     PAGINATION HANDLERS
     ===================================================
     */

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

    /**
     ===================================================
     SORT HANDLERS (Terintegrasi dengan filter)
     ===================================================
     */

    const handleSortChange = (sortOptions) => {
        if (!sortOptions || !sortOptions.field) {
            // Reset sorting
            filters.sort = null;
            filters.order = null;
            resetSort();
        } else {
            // Update sort state
            const order = sortOptions.order === "asc" ? "ascend" : "descend";
            filters.sort = sortOptions.field;
            filters.order = sortOptions.order;
            updateSort(sortOptions.field, order);
        }

        // Fetch data dengan sorting baru
        loadingTable.value = true;
        debouncedFetch();

        // console.log("testing", sortOptions);
        // if (sortOptions?.field) {
        //     const order = sortOptions.order === "asc" ? "ascend" : "descend";
        //     updateSort(sortOptions.field, order);

        //     loadingTable.value = true;
        //     debouncedFetch();
        // } else {
        //     resetSort();
        // }
        // datatableHandleSortChange(sortOptions);
    };

    /**
     ===================================================
     CLEAR FILTERS HANDLER
     ===================================================
     */

    const handleClear = () => {
        loadingTable.value = true;

        // Reset semua filters
        filters.search = "";
        filters.status = null;
        filters.sort = null;
        filters.order = null;

        // Reset sort state
        resetSort();

        debouncedFetch();
    };

    const handleResetSort = async () => {
        resetSort();
        filters.sort = null;
        filters.order = null;
        loadingTable.value = true;
        debouncedFetch();
    };

    /**
     ===================================================
     RETURN VALUE - Semua fungsi dan state yang diekspose
     ===================================================
     */

    return {
        // Data fetching states
        loadingSearch,
        loadingTable,
        filters,
        currentPage,

        // Data fetching functions
        fetchData,
        debouncedFetch,
        handlePageChange,
        handlePageSizeChange,
        handleSortChange,
        handleClear,
        handleResetSort,

        // Column creation functions
        createColumns,
        defineColumns,
        updateSortOrder,
        resetSortOrderInConfig,
        createColumnConfigWithSort,

        // Sort management
        activeSortKey,
        activeSortOrder,
        resetSort,
        updateSort,
        hasActiveSort,
        hasActiveSortValue, // Computed version untuk template
        getSortOrder,
        getActiveSort,

        // Renderers (untuk penggunaan manual jika diperlukan)
        renderers: {
            currency: renderCurrency,
            date: renderDate,
            status: renderStatus,
            boolean: renderBoolean,
            actions: renderActions,
        },
    };
}
