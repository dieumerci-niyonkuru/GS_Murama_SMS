// Language support
let currentLang = localStorage.getItem('lang') || 'en';
let translations = {};

async function loadLanguage(lang) {
    const response = await fetch(`/lang/${lang}.json`);
    translations = await response.json();
    currentLang = lang;
    localStorage.setItem('lang', lang);
    translatePage();
}

function translatePage() {
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[key]) {
            if (el.tagName === 'INPUT' && el.placeholder) {
                el.placeholder = translations[key];
            } else {
                el.innerHTML = translations[key];
            }
        }
    });
    // Update toggle button text
    const toggleBtn = document.getElementById('langToggle');
    if (toggleBtn) toggleBtn.innerHTML = currentLang === 'en' ? 'Kinyarwanda' : 'English';
}

function toggleLanguage() {
    const newLang = currentLang === 'en' ? 'rw' : 'en';
    loadLanguage(newLang);
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadLanguage(currentLang);
});
