export const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

export const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value || 0)
}

// utils/formatters.js

/**
 * Format angka ke format ribuan dengan titik
 * @param {number|string} value - Angka yang akan diformat
 * @returns {string} Angka yang sudah diformat
 */
export const formatNumber = (value) => {
    if (value === null || value === undefined || value === "") return "";

    const num = typeof value === "string" ? parseFloat(value) : value;
    if (isNaN(num)) return "";

    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
};

/**
 * Parse string format ribuan ke number
 * @param {string} value - String yang akan diparse
 * @returns {number|null} Hasil parse
 */
export const parseNumber = (value) => {
    if (!value) return null;

    const parsed = value.replace(/\./g, "");
    return parsed ? Number(parsed) : null;
};

/**
 * Format ke Rupiah
 * @param {number|string} value - Angka yang akan diformat
 * @param {boolean} withPrefix - Sertakan prefix Rp atau tidak
 * @returns {string} Format Rupiah
 */
// export const formatRupiah = (value, withPrefix = true) => {
//     const formatted = formatNumber(value);
//     if (!formatted) return "";
//     return withPrefix ? `Rp ${formatted}` : formatted;
// };

/**
 * Parse dari format Rupiah
 * @param {string} value - String Rupiah
 * @returns {number|null}
 */
export const parseRupiah = (value) => {
    if (!value) return null;
    const cleaned = value.replace(/[^\d]/g, "");
    return cleaned ? Number(cleaned) : null;
};

/**
 * Format ke persen
 * @param {number} value - Nilai persen
 * @returns {string}
 */
export const formatPercent = (value) => {
    if (value === null || value === undefined) return "";
    return `${value}%`;
};

export const formatDate = (dateString) => {
    if (!dateString) return "";
    try {
        const date = new Date(dateString);
        // Cek apakah date valid
        if (isNaN(date.getTime())) return "";

        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return "";
    }
};

// Definisikan juga formatDateTime jika diperlukan
export const formatDateTime = (dateString) => {
    if (!dateString) return "";
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "";

        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.error('Error formatting datetime:', error);
        return "";
    }
};

export const getStatusType = (status) => {
    const types = {
        pending: 'warning',
        approved: 'success',
        rejected: 'error'
    };
    return types[status] || 'default';
};