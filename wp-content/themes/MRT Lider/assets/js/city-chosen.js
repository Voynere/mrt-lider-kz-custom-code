document.addEventListener('DOMContentLoaded', function() {
    const cityChosenBanner = document.getElementById('cityChosenBanner');
    const cityChosenNameSpan = document.getElementById('cityChosenName');
    const cityChosenYesBtn = document.getElementById('cityChosenYes');
    const cityChosenNoBtn = document.getElementById('cityChosenNo');
    const choiceCityBtn = document.querySelector('.header__city-choice');
    const modalCity = document.querySelector('.modal-city');
    const overlay = document.querySelector('.overlay');

    // --- Имена Cookies ---
    const CITY_CHOSEN_COOKIE_NAME = 'city_chosen_confirmed';
    const SELECTED_CITY_COOKIE_NAME = 'selected_city'; 

    // --- Карта городов должна быть такой же как и в main.js ---
    const cityMapChosenJs = (typeof mrtCityConfig !== 'undefined' && mrtCityConfig.cityMap)
        ? mrtCityConfig.cityMap
        : {
        'almaty': 'Алматы',
        'astana': 'Астана',
        'karaganda': 'Караганда',
        'taldykorgan': 'Талдыкорган',
        'almaty_aubakirova': 'МРТ животным «MRI Animal»',
    };

    // --- Функции для работы с cookies ---
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

    // --- Функции для работы с UI ---
    function disableBodyScroll() {
        document.body.classList.add('no-scroll');
    }

    function enableBodyScroll() {
        const scrollY = document.body.style.top;
        document.body.classList.remove('no-scroll');
        document.body.style.top = '';
        window.scrollTo(0, parseInt(scrollY || '0') * -1);
    }

    // --- Обновление отображения названия города в баннере ---
    function updateCityChosenNameDisplay() {
        if (cityChosenNameSpan) {
            const currentCitySlug = getCookie(SELECTED_CITY_COOKIE_NAME);
            const cityName = cityMapChosenJs[currentCitySlug] || 'Хабаровск'; 
            cityChosenNameSpan.textContent = cityName;
        }
    }

    // --- Функции логики баннера ---
    function isCityChosenConfirmed() {
        return getCookie(CITY_CHOSEN_COOKIE_NAME) === 'true';
    }

    function showCityChosenBanner() {
        if (cityChosenBanner) {
            // Перед показом обновляем название города
            updateCityChosenNameDisplay();
            cityChosenBanner.classList.add('city-chosen--visible');
        }
    }

    function hideCityChosenBanner() {
        if (cityChosenBanner) {
            cityChosenBanner.classList.remove('city-chosen--visible');
        }
    }

    // --- Инициализация ---
    // Проверка: было ли уже подтверждено согласие с городом
    if (!isCityChosenConfirmed()) {
        // Если согласие не было дано => показываем баннер
        // (он сам обновит название города перед показом)
        showCityChosenBanner();
    }

    // --- Обработчики событий ---
    if (cityChosenYesBtn) {
        cityChosenYesBtn.addEventListener('click', function() {
            setCookie(CITY_CHOSEN_COOKIE_NAME, 'true', 365);
            hideCityChosenBanner();
        });
    }

    if (cityChosenNoBtn) {
        cityChosenNoBtn.addEventListener('click', function() {
            hideCityChosenBanner();
            if (choiceCityBtn && modalCity && overlay) {
                choiceCityBtn.classList.add('active');
                modalCity.classList.add('active');
                overlay.classList.add('active');
                disableBodyScroll();
                setCookie(CITY_CHOSEN_COOKIE_NAME, 'true', 365);
            }
        });
    }

    if (choiceCityBtn) {
        choiceCityBtn.addEventListener('click', function(e) {
            setCookie(CITY_CHOSEN_COOKIE_NAME, 'true', 365);
            hideCityChosenBanner();
        });
    }
});