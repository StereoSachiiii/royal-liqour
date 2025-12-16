/**
 * User Preferences Storage
 * Stores taste profile preferences in localStorage
 */

const STORAGE_KEY = 'royal_liquor_taste_profile';

// Default preferences
const DEFAULT_PREFERENCES = {
    sweetness: 5,
    bitterness: 5,
    strength: 5,
    smokiness: 5,
    fruitiness: 5,
    spiciness: 5,
    favoriteCategories: [],
    lastUpdated: null
};

/**
 * Get user's taste preferences
 * @returns {Object} User preferences object
 */
export function getTastePreferences() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            return { ...DEFAULT_PREFERENCES, ...JSON.parse(stored) };
        }
    } catch (e) {
        console.error('[Preferences] Error reading:', e);
    }
    return { ...DEFAULT_PREFERENCES };
}

/**
 * Save user's taste preferences
 * @param {Object} preferences - Preferences to save
 */
export function saveTastePreferences(preferences) {
    try {
        const toSave = {
            ...preferences,
            lastUpdated: new Date().toISOString()
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(toSave));
        console.log('[Preferences] Saved:', toSave);
        return true;
    } catch (e) {
        console.error('[Preferences] Error saving:', e);
        return false;
    }
}

/**
 * Check if user has set preferences
 * @returns {boolean}
 */
export function hasPreferences() {
    const prefs = getTastePreferences();
    return prefs.lastUpdated !== null;
}

/**
 * Clear user preferences
 */
export function clearPreferences() {
    localStorage.removeItem(STORAGE_KEY);
}

/**
 * Calculate match score between user preferences and a product's flavor profile
 * @param {Object} product - Product with flavor_profile field
 * @returns {number} Match percentage 0-100
 */
export function calculateMatchScore(product) {
    const prefs = getTastePreferences();

    // If no preferences set, return 0
    if (!prefs.lastUpdated) return 0;

    try {
        const flavor = typeof product.flavor_profile === 'string'
            ? JSON.parse(product.flavor_profile)
            : product.flavor_profile;

        if (!flavor) return 0;

        const attributes = ['sweetness', 'bitterness', 'strength', 'smokiness', 'fruitiness', 'spiciness'];

        // Calculate distance
        const maxDistance = Math.sqrt(600); // sqrt(6 * 10^2)
        const distance = Math.sqrt(
            attributes.reduce((sum, attr) => {
                const diff = (prefs[attr] || 5) - (flavor[attr] || 5);
                return sum + (diff * diff);
            }, 0)
        );

        // Convert to percentage
        const matchPercentage = Math.round(((maxDistance - distance) / maxDistance) * 100);
        return Math.max(0, Math.min(100, matchPercentage));
    } catch (e) {
        return 0;
    }
}

/**
 * Sort products by match score
 * @param {Array} products - Array of products
 * @returns {Array} Sorted products with matchScore property added
 */
export function sortByMatch(products) {
    return products
        .map(p => ({ ...p, matchScore: calculateMatchScore(p) }))
        .sort((a, b) => b.matchScore - a.matchScore);
}
