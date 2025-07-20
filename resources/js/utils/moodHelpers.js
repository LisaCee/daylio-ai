/**
 * Get the color class for a given mood level
 * @param {number} moodLevel - The mood level (0-4)
 * @returns {string} The color class name
 */
export function getColor(moodLevel) {
    switch (moodLevel) {
        case 0: return 'accent-gray';
        case 1: return 'accent-blue';
        case 2: return 'accent-purple';
        case 3: return 'accent-green';
        case 4: return 'accent-orange';
        default: return '';
    }
}

/**
 * Get the label for a given mood level
 * @param {number} moodLevel - The mood level (0-4)
 * @returns {string} The mood label
 */
export function getLabel(moodLevel) {
    switch (moodLevel) {
        case 0: return 'Bad';
        case 1: return 'Poor';
        case 2: return 'Meh';
        case 3: return 'Good';
        case 4: return 'Great';
        default: return '';
    }
}

/**
 * Get the full mood data object for a given mood level
 * @param {number} moodLevel - The mood level (0-4)
 * @returns {object|null} The mood object with id, name, color, and icon
 */
export function getMoodData(moodLevel) {
    const moods = [
        { id: 0, name: 'Bad', color: 'accent-gray', icon: 'fa-regular fa-face-tired' },
        { id: 1, name: 'Poor', color: 'accent-blue', icon: 'fa-regular fa-face-frown' },
        { id: 2, name: 'Meh', color: 'accent-purple', icon: 'fa-regular fa-face-meh' },
        { id: 3, name: 'Good', color: 'accent-green', icon: 'fa-regular fa-face-smile' },
        { id: 4, name: 'Great', color: 'accent-orange', icon: 'fa-regular fa-face-laugh-beam' }
    ];
    
    return moods.find(mood => mood.id === moodLevel) || null;
}

/**
 * Get the CSS variable name for a mood color
 * @param {number} moodLevel - The mood level (0-4)
 * @returns {string} The CSS variable name (e.g., 'var(--accent-gray)')
 */
export function getColorVariable(moodLevel) {
    const color = getColor(moodLevel);
    return color ? `var(--${color})` : '';
}

/**
 * Get all available moods
 * @returns {Array} Array of all mood objects
 */
export function getAllMoods() {
    return [
        { id: 0, name: 'Bad', color: 'accent-gray', icon: 'fa-regular fa-face-tired' },
        { id: 1, name: 'Poor', color: 'accent-blue', icon: 'fa-regular fa-face-frown' },
        { id: 2, name: 'Meh', color: 'accent-purple', icon: 'fa-regular fa-face-meh' },
        { id: 3, name: 'Good', color: 'accent-green', icon: 'fa-regular fa-face-smile' },
        { id: 4, name: 'Great', color: 'accent-orange', icon: 'fa-regular fa-face-laugh-beam' }
    ];
} 