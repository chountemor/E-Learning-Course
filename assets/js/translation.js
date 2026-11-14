/* =========================================================
   eLEARNING COLLEGE — FRONT-END TRANSLATION
   Add new text to the dictionaries below.
========================================================= */
const ELC_TRANSLATIONS = {
    en: {
        "Notifications":"Notifications",
        "Dark / Light mode":"Dark / Light mode",
        "Free Online Learning":"Free Online Learning",
        "Learn anytime, anywhere":"Learn anytime, anywhere",
        "Start Learning":"Start Learning",
        "Explore Courses":"Explore Courses"
    },
    kh: {
        "Notifications":"ការជូនដំណឹង",
        "Dark / Light mode":"របៀបងងឹត / ភ្លឺ",
        "Free Online Learning":"ការសិក្សាតាមអនឡាញដោយឥតគិតថ្លៃ",
        "Learn anytime, anywhere":"រៀនគ្រប់ពេលវេលា គ្រប់ទីកន្លែង",
        "Start Learning":"ចាប់ផ្តើមសិក្សា",
        "Explore Courses":"ស្វែងរកវគ្គសិក្សា"
    }
};

function setClientLanguage(language) {
    const lang = ELC_TRANSLATIONS[language] ? language : 'kh';
    localStorage.setItem('elearning-language', lang);
    document.documentElement.lang = lang === 'kh' ? 'km' : 'en';

    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.dataset.i18n;
        if (ELC_TRANSLATIONS[lang][key]) el.textContent = ELC_TRANSLATIONS[lang][key];
    });

    document.querySelectorAll('[data-lang]').forEach(el => {
        el.classList.toggle('active', el.dataset.lang === lang);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const current = document.documentElement.lang === 'km' ? 'kh' : 'en';
    setClientLanguage(current);
});
