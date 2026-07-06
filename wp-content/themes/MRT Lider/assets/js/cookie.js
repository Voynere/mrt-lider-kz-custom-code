document.addEventListener('DOMContentLoaded', function() {
    const cookieBanner = document.getElementById('cookieBanner');
    const acceptBtn = document.getElementById('acceptCookies');
    const declineBtn = document.getElementById('declineCookies');
    const customizeBtn = document.getElementById('customizeCookies');

    // Проверка существования элементов
    if (!cookieBanner) {
        console.warn('Cookie банер не найден');
        return;
    }

    // Название cookie для отслеживания согласия
    const COOKIE_CONSENT_NAME = 'cookie_consent';
    const COOKIE_CONSENT_DURATION = 365; // дней

    // Проверяем, было ли уже дано согласие
    function hasCookieConsent() {
        return getCookie(COOKIE_CONSENT_NAME) !== null;
    }

    // Функции для работы с cookies
    function setCookie(name, value, days = 30) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/;SameSite=Lax`;
    }

    function getCookie(name) {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [cookieName, cookieValue] = cookie.trim().split('=');
            if (cookieName === name) return cookieValue;
        }
        return null;
    }

    // Показываем баннер, если согласие не было дано
    if (!hasCookieConsent()) {
        cookieBanner.classList.add('cookie--visible');
    }

    // Функция для установки согласия
    function setCookieConsent(value) {
        setCookie(COOKIE_CONSENT_NAME, value, COOKIE_CONSENT_DURATION);
        cookieBanner.classList.remove('cookie--visible');
    }

    // Обработчики событий для кнопок
    if (acceptBtn) {
        acceptBtn.addEventListener('click', function() {
            setCookieConsent('accepted');
        });
    }

    if (declineBtn) {
        declineBtn.addEventListener('click', function() {
            setCookieConsent('declined');
        });
    }

    if (customizeBtn) {
        customizeBtn.addEventListener('click', function() {
            setCookieConsent('custom');
            alert('Функция настройки cookie пока не реализована.');
        });
    }
});