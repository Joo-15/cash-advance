// composables/useTableColumns.js
import { h, computed } from "vue";
import { NButton, NIcon, NSpace, NTag, NTooltip } from "naive-ui";
import { PencilOutline, TrashOutline, EyeOutline } from "@vicons/ionicons5";

// Konstanta untuk konfigurasi default
const DEFAULT_CONFIG = {
    currency: "IDR",
    dateFormat: "DD-MM-YYYY",
    actionSize: "small",
    ellipsisTooltip: true,
};

// Predefined sorters
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

export function useTableColumns(customConfig = {}) {
    const config = { ...DEFAULT_CONFIG, ...customConfig };

    /**
     * Create columns dengan berbagai fitur
     */
    const createColumns = (columnConfig = [], actions = {}) => {
        return columnConfig.map((column) => {
            const baseColumn = createBaseColumn(column);
            applySorter(baseColumn, column);
            applyEllipsis(baseColumn, column);
            applyRenderer(baseColumn, column, actions);
            return baseColumn;
        });
    };

    /**
     * Buat base column
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

    /**
     * Apply sorter dengan berbagai mode
     */
    const applySorter = (baseColumn, column) => {
        if (!column.sorter) return;

        // Mode 1: Custom function
        if (typeof column.sorter === "function") {
            baseColumn.sorter = column.sorter;
            return;
        }

        // Mode 2: Boolean true - auto detect
        if (column.sorter === true) {
            const type = column.type || "string";
            baseColumn.sorter =
                SORTERS[type]?.(column.key) || SORTERS.string(column.key);
            return;
        }

        // Mode 3: String type
        if (typeof column.sorter === "string") {
            baseColumn.sorter =
                SORTERS[column.sorter.toLowerCase()]?.(column.key) ||
                SORTERS.string(column.key);
            return;
        }

        // Mode 4: Object config
        if (typeof column.sorter === "object") {
            baseColumn.sorter = {
                multiple: column.sorter.multiple || false,
                compare: column.sorter.compare || SORTERS.string(column.key),
            };
        }

        // Set sort order
        if (column.sortOrder) {
            baseColumn.sortOrder = column.sortOrder;
        }
    };

    /**
     * Apply ellipsis dengan tooltip
     */
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

    /**
     * Apply renderer berdasarkan tipe
     */
    const applyRenderer = (baseColumn, column, actions) => {
        // Custom render function
        if (column.render) {
            baseColumn.render = column.render;
            return;
        }

        // Render by type
        switch (column.type) {
            case "currency":
                baseColumn.align = column.align || "right";
                baseColumn.render = (row) =>
                    renderCurrency(row[column.key], column.currency);
                break;

            case "date":
                baseColumn.render = (row) =>
                    renderDate(row[column.key], column.dateFormat);
                break;

            case "status":
                baseColumn.render = (row) =>
                    renderStatus(row[column.key], column.statusMap);
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

            case "image":
            case "avatar":
                baseColumn.render = (row) =>
                    renderImage(row[column.key], column.imageConfig);
                break;
        }
    };

    /**
     * Render currency
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

    /**
     * Render date
     */
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

    /**
     * Render status dengan NTag
     */
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

    /**
     * Render boolean dengan icon atau badge
     */
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

    /**
     * Render actions buttons
     */
    const renderActions = (row, actionConfig = {}, actions = {}) => {
        const {
            showEdit = true,
            showDelete = true,
            showView = false,
            showCustom = false,
            customButtons = [],
            size = config.actionSize,
            placement = "center",
            editProps = {},
            deleteProps = {},
            viewProps = {},
        } = actionConfig;

        const buttons = [];

        // View button
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

        // Edit button
        if (showEdit && actions.onEdit) {
            buttons.push(
                createActionButton({
                    type: "primary",
                    icon: PencilOutline,
                    onClick: () => actions.onEdit(row),
                    props: editProps,
                    size,
                    tooltip: "Edit",
                }),
            );
        }

        // Delete button
        if (showDelete && actions.onDelete) {
            buttons.push(
                createActionButton({
                    type: "error",
                    icon: TrashOutline,
                    onClick: () => actions.onDelete(row.id || row),
                    props: deleteProps,
                    size,
                    tooltip: "Hapus",
                    style: buttons.length > 0 ? "margin-left: 6px" : "",
                }),
            );
        }

        // Custom buttons
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
     * Helper create action button
     */
    const createActionButton = ({
        type,
        icon,
        onClick,
        props = {},
        size,
        tooltip,
        style,
    }) => {
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

    /**
     * Helper untuk membuat konfigurasi kolom
     */
    const defineColumns = (columns) => columns;

    /**
     * Helper untuk update sort order
     */
    const updateSortOrder = (columns, sortKey, sortOrder) => {
        return columns.map((col) => ({
            ...col,
            sortOrder: col.key === sortKey ? sortOrder : false,
        }));
    };

    return {
        createColumns,
        defineColumns,
        updateSortOrder,
        // Expose renderers untuk penggunaan di luar
        renderers: {
            currency: renderCurrency,
            date: renderDate,
            status: renderStatus,
            boolean: renderBoolean,
        },
    };
}
