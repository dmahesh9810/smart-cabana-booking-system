/**
 * Format a numeric value as a Sri Lankan Rupee (LKR) amount.
 * e.g., 12500 → "LKR 12,500.00"
 */
export function formatLKR(amount) {
    const num = parseFloat(amount);
    if (isNaN(num)) return 'LKR 0.00';
    return new Intl.NumberFormat('en-LK', {
        style: 'currency',
        currency: 'LKR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num);
}

/**
 * Format as LKR without decimals for large round numbers.
 * e.g., 12500 → "LKR 12,500"
 */
export function formatLKRShort(amount) {
    const num = parseFloat(amount);
    if (isNaN(num)) return 'LKR 0';
    return new Intl.NumberFormat('en-LK', {
        style: 'currency',
        currency: 'LKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
}

/**
 * Today's date in YYYY-MM-DD format (for min date constraints).
 */
export function todayISO() {
    return new Date().toISOString().split('T')[0];
}
