// Patch for dynamic content: translate alert and button texts when needed
function t(key) {
    return translations[key] || key;
}
// Example: when showing students, use t('students') for header
// We'll leave existing code as is, but for new strings we can use t()
