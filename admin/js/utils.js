export function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function formatDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return dateString;
    }
}

export function formatOrderDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return dateString;
    }
}
/**
 * Format number with thousands separator
 * @param {number} num - Number to format
 * @returns {string} Formatted number (e.g., "1,234")
 */
export function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

/**
 * Format currency in LKR (Sri Lankan Rupees)
 * Converts from cents to rupees and formats with proper locale
 * @param {number} cents - Amount in cents
 * @returns {string} Formatted currency (e.g., "Rs 1,234.50")
 */
export function formatCurrency(cents) {
    if (cents === null || cents === undefined) return 'Rs 0.00';
    
    const rupees = cents / 100;
    return `Rs ${rupees.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
}

/**
 * Format percentage value
 * @param {number} value - Percentage value (e.g., 45 for 45%)
 * @returns {string} Formatted percentage (e.g., "45%")
 */
export function formatPercent(value) {
    if (value === null || value === undefined) return '0%';
    
    const num = Number(value);
    const rounded = Math.round(num * 10) / 10;
    
    return `${rounded.toLocaleString('en-US', {
        minimumFractionDigits: rounded % 1 === 0 ? 0 : 1,
        maximumFractionDigits: 1
    })}%`;
}

/**
 * Format time duration in hours/minutes/days
 * @param {number} hours - Duration in hours
 * @returns {string} Human-readable duration (e.g., "2d 5h")
 */
export function formatDuration(hours) {
    if (hours === null || hours === undefined) return '0h';
    
    const h = Math.floor(hours);
    const days = Math.floor(h / 24);
    const remainingHours = h % 24;
    
    if (days > 0) {
        return remainingHours > 0 ? `${days}d ${remainingHours}h` : `${days}d`;
    }
    return `${remainingHours}h`;
}

/**
 * Format large numbers with abbreviations (K, M, B)
 * @param {number} num - Number to format
 * @returns {string} Abbreviated number (e.g., "1.2K", "5.8M")
 */
export function formatCompactNumber(num) {
    if (num === null || num === undefined) return '0';
    
    const n = Math.abs(Number(num));
    
    if (n >= 1000000000) {
        return (num / 1000000000).toLocaleString('en-US', {
            maximumFractionDigits: 1
        }) + 'B';
    }
    if (n >= 1000000) {
        return (num / 1000000).toLocaleString('en-US', {
            maximumFractionDigits: 1
        }) + 'M';
    }
    if (n >= 1000) {
        return (num / 1000).toLocaleString('en-US', {
            maximumFractionDigits: 1
        }) + 'K';
    }
    
    return formatNumber(num);
}

/**
 * Format date to readable format
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date (e.g., "Jan 15, 2025")
 */
export function formatDateShort(date) {
    if (!date) return '';
    
    try {
        const d = new Date(date);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return String(date);
    }
}

/**
 * Format date with time
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date with time (e.g., "Jan 15, 2025 2:30 PM")
 */
export function formatDateTime(date) {
    if (!date) return '';
    
    try {
        const d = new Date(date);
        return d.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (error) {
        return String(date);
    }
}

/**
 * Format percentage change with indicator
 * @param {number} value - Percentage value
 * @param {boolean} isIncrease - Whether it's an increase (true) or decrease (false)
 * @returns {string} Formatted percentage with arrow (e.g., "↑ 15%" or "↓ 5%")
 */
export function formatPercentChange(value, isIncrease) {
    if (value === null || value === undefined) return '0%';
    
    const arrow = isIncrease ? '↑' : '↓';
    const num = Math.abs(Number(value));
    const rounded = Math.round(num * 10) / 10;
    
    return `${arrow} ${rounded.toLocaleString('en-US', {
        minimumFractionDigits: rounded % 1 === 0 ? 0 : 1,
        maximumFractionDigits: 1
    })}%`;
}

/**
 * Format inventory status with color indicator
 * @param {number} available - Available quantity
 * @param {number} threshold - Low stock threshold (default 20)
 * @returns {object} Status object with text and color
 */
export function getInventoryStatus(available, threshold = 20) {
    const n = Number(available);
    
    if (n === 0) {
        return { text: 'Out of Stock', color: 'danger', value: n };
    }
    if (n <= threshold) {
        return { text: 'Low Stock', color: 'warning', value: n };
    }
    return { text: 'In Stock', color: 'success', value: n };
}