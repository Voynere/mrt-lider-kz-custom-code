// Слайдер рейтинг
var swiper = new Swiper(".raitingSwiper", {
    slidesPerView: 1,
    spaceBetween: 12,
    loop: true,
    navigation: {
        nextEl: ".raitingSwiper-next",
        prevEl: ".raitingSwiper-prev",
    },
});

// Слайдер врачей
var swiper = new Swiper(".specialistsSwiper", {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    navigation: {
        nextEl: ".specialistsSwiper-next",
        prevEl: ".specialistsSwiper-prev",
    },
    breakpoints: {
        867: {
            slidesPerView: 3,
            spaceBetween: 24
        },
        578: {
            slidesPerView: 2,
            spaceBetween: 24
        },
    }
});

// Слайдер статей
var swiper = new Swiper(".articlesSwiper", {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    navigation: {
        nextEl: ".articlesSwiper-next",
        prevEl: ".articlesSwiper-prev",
    },
    breakpoints: {
        867: {
            slidesPerView: 3,
            spaceBetween: 24
        },
        578: {
            slidesPerView: 2,
            spaceBetween: 24
        },
    }
});

// Слайдер акций
var swiper = new Swiper(".stockSwiper", {
    slidesPerView: 1,
    spaceBetween: 24,
    loop: true,
    centeredSlides: false,
    navigation: {
        nextEl: ".stockSwiper-next",
        prevEl: ".stockSwiper-prev",
    },
    breakpoints: {
        1200: {
            slidesPerView: 'auto',
            spaceBetween: 24
        },
        867: {
            slidesPerView: 3,
            spaceBetween: 24
        },
        578: {
            slidesPerView: 2,
            spaceBetween: 24
        },
    }
});
// Бургер меню
document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.burger');
    const menu = document.querySelector('.header__burger');
    const overlay = document.querySelector('.overlay-menu');

    // Открытие/закрытие меню 
    burger.addEventListener('click', (e) => {
        e.preventDefault();
        burger.classList.toggle('active');
        menu.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    // Закрытие меню при клике на overlay
    overlay.addEventListener('click', () => {
        menu.classList.remove('active');
        overlay.classList.remove('active');
        burger.classList.toggle('active');
    });
});

// --- Кнопка "Наверх" ---
document.addEventListener("DOMContentLoaded", function () {
    let gototop = document.querySelector(".to-top");
    let body = document.documentElement;

    window.addEventListener("scroll", check);

    function check() {
        pageYOffset >= 500 && gototop.classList.add("to-top__upview");
        pageYOffset < 500 && gototop.classList.remove("to-top__upview");
    }

    gototop.onclick = function () {
        animate({
            duration: 700,
            timing: gogototopEaseOut,
            draw: (progress) => (body.scrollTop = body.scrollTop * (1 - progress / 7))
        });
    };

    let circ = (timeFraction) =>
        1 -
        Math.sin(Math.acos(timeFraction > 1 ? (timeFraction = 1) : timeFraction));

    let makeEaseOut = (timing) => (timeFraction) => 1 - timing(1 - timeFraction);
    let gogototopEaseOut = makeEaseOut(circ);
});

function animate(options) {
    let start = performance.now();

    requestAnimationFrame(function animate(time) {
        let timeFraction = (time - start) / options.duration;
        timeFraction > 1 && (timeFraction = 1);

        let progress = options.timing(timeFraction);

        options.draw(progress);
        timeFraction < 1 && requestAnimationFrame(animate);
    });
}


// Модальное окно выбора города
// Отображение выбранного города (selected_city)
// куки
// --- Объеденные скрипты ---
document.addEventListener('DOMContentLoaded', () => {
    const choiceCityBtn = document.querySelector('.header__city-choice');
    const modalCity = document.querySelector('.modal-city');
    const closeBtn = document.querySelector('.modal-city__close');
    const overlay = document.querySelector('.overlay');
    const citySelected = document.querySelector('.header__city-selected');

    // --- Функции для работы с куки ---
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

    // --- Карта городов (должна быть идентична той, что в PHP) ---
    const cityMap = (typeof mrtCityConfig !== 'undefined' && mrtCityConfig.cityMap)
        ? mrtCityConfig.cityMap
        : {
        'almaty': 'Алматы',
        'astana': 'Астана',
        'karaganda': 'Караганда',
        'taldykorgan': 'Талдыкорган',
        'almaty_aubakirova': 'МРТ животным',
    };

    // --- Известные слаги городов (для парсинга URL) ---
    const knownCitySlugs = (typeof mrtCityConfig !== 'undefined' && mrtCityConfig.knownSlugs)
        ? mrtCityConfig.knownSlugs
        : Object.keys(cityMap);

    // --- Функции обновления отображения ---
    function updateCityDisplay() {
        const savedCity = getCookie('selected_city');
        if (savedCity && cityMap[savedCity]) {
            citySelected.textContent = cityMap[savedCity];
        } else {
            citySelected.textContent = 'Алматы'; // fallback
        }
    }

    // --- Инициализация и определение города ---
    function initializeAndSetCity() {
        // Попробуем получить город из URL
        const currentPath = window.location.pathname;
        const pathParts = currentPath.split('/').filter(part => part.length > 0);
        let cityFromUrl = null;

        if (pathParts.length > 0) {
            const urlCitySlug = pathParts[0].toLowerCase();
            if (knownCitySlugs.includes(urlCitySlug)) {
                cityFromUrl = urlCitySlug;
            }
        }

        // Получим город из cookie
        const cityFromCookie = getCookie('selected_city');

        // Логика определения итогового города
        let finalCitySlug = 'almaty'; // Значение по умолчанию

        if (cityFromUrl) {
            // Если город есть в URL - он главный
            finalCitySlug = cityFromUrl;
            // Если он отличается от cookie - обновим cookie
            if (cityFromCookie !== cityFromUrl) {
                setCookie('selected_city', cityFromUrl);
                console.log(`Город установлен из URL: ${cityFromUrl}`);
            } else {
                console.log(`Город из URL (${cityFromUrl}) совпадает с cookie.`);
            }
        } else if (cityFromCookie) {
            // Если URL не содержит города, но cookie есть - используем cookie
            if (knownCitySlugs.includes(cityFromCookie)) {
                finalCitySlug = cityFromCookie;
                console.log(`Город установлен из cookie: ${cityFromCookie}`);
            } else {
                console.warn(`Недопустимый город в cookie: ${cityFromCookie}. Используется значение по умолчанию.`);
            }
        } else {
            // Ни URL, ни cookie не содержат города - установим по умолчанию
            setCookie('selected_city', 'almaty');
            console.log(`Город установлен по умолчанию: almaty`);
        }

        // Обновим отображение в шапке
        updateCityDisplay();
        return finalCitySlug;
    }
    // --- КОНЕЦ НОВОЙ ЦЕНТРАЛИЗОВАННОЙ ФУНКЦИИ ---

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

    // --- Получение динамического базового url ---
    function getDynamicCityBaseUrl() {
        const currentCitySlug = getCookie('selected_city') || 'almaty';
        return `${window.location.origin}/${currentCitySlug}/`;
    }

    // --- Формирование url навигации ---
    function getDynamicNavUrl(page_slug, city_specific_pages_js) {
        if (city_specific_pages_js.includes(page_slug)) {
            return `${getDynamicCityBaseUrl()}${page_slug}/`;
        } else {
            return `${window.location.origin}/${page_slug}/`;
        }
    }

    // --- Инициализация при загрузке ---
    // Эта функция : определяет город, устанавливает куки, обновляет отображение
    const initialCity = initializeAndSetCity();

    // --- Обработчики модального окна ---
    if (choiceCityBtn && modalCity && closeBtn && overlay) {
        choiceCityBtn.addEventListener('click', (e) => {
            e.preventDefault();
            choiceCityBtn.classList.toggle('active');
            modalCity.classList.toggle('active');
            overlay.classList.toggle('active');
            disableBodyScroll();
        });

        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            choiceCityBtn.classList.remove('active');
            modalCity.classList.remove('active');
            overlay.classList.remove('active');
            enableBodyScroll();
        });

        overlay.addEventListener('click', () => {
            modalCity.classList.remove('active');
            overlay.classList.remove('active');
            choiceCityBtn.classList.remove('active');
            enableBodyScroll();
        });
    } else {
        console.warn('Один или несколько элементов модального окна выбора города не найдены.');
    }

    // --- Обработка выбора города из модального окна ---
    document.querySelectorAll('.modal-city__content a[data-city]').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            const newCitySlug = this.getAttribute('data-city');

            if (!knownCitySlugs.includes(newCitySlug)) {
                console.error(`Выбран недопустимый город: ${newCitySlug}`);
                return;
            }

            // Устанавливаем куки
            setCookie('selected_city', newCitySlug);

            // Формируем новый URL
            const currentPath = window.location.pathname;
            let basePath = currentPath;
            for (const slug of knownCitySlugs) {
                if (currentPath.startsWith(`/${slug}/`)) {
                    basePath = currentPath.substring(`/${slug}`.length);
                    break;
                }
            }
            if (basePath === '') basePath = '/';
            const newUrl = `/${newCitySlug}${basePath === '/' ? '' : basePath}`;

            // Делаем ПОЛНУЮ ПЕРЕЗАГРУЗКУ с новым URL
            window.location.href = newUrl;
        });
    });

    // --- Обработка навигации браузера (кнопки назад/вперед) ---
    window.addEventListener('popstate', function (event) {
        console.log('Событие popstate: перепроверка города из URL...');
        // При изменении истории перепроверяем URL и обновляем cookie/UI если нужно
        const currentPath = window.location.pathname;
        const pathParts = currentPath.split('/').filter(part => part.length > 0);
        if (pathParts.length > 0) {
            const urlCitySlug = pathParts[0].toLowerCase();
            if (knownCitySlugs.includes(urlCitySlug)) {
                const currentCookieCity = getCookie('selected_city');
                if (currentCookieCity !== urlCitySlug) {
                    setCookie('selected_city', urlCitySlug);
                    updateCityDisplay();
                    console.log(`Город в cookie обновлен с '${currentCookieCity}' на '${urlCitySlug}' из URL (popstate).`);
                }
            }
        }
    });

    // --- НОВАЯ ЛОГИКА: Обработка кликов по навигационным ссылкам ---
    // Предполагаем, что citySpecificPagesJs доступен глобально из header.php
    if (typeof citySpecificPagesJs !== 'undefined' && Array.isArray(citySpecificPagesJs)) {

        // Функция для обработки клика по ссылке
        function handleNavLinkClick(e) {
            const href = this.getAttribute('href');

            if (href && href.startsWith('/')) {
                const slugMatch = href.match(/^\/([^\/\?#]+)/);
                if (slugMatch && slugMatch[1]) {
                    const pageSlug = slugMatch[1];

                    if (citySpecificPagesJs.includes(pageSlug)) {
                        e.preventDefault();
                        const dynamicUrl = getDynamicNavUrl(pageSlug, citySpecificPagesJs);
                        window.location.href = dynamicUrl;
                    }
                }
            }
        }

        // Навешиваем обработчики на все навигационные ссылки
        // Используем делегирование события для повышения надежности
        document.addEventListener('click', function (e) {
            // Проверяем, является ли цель (или её родитель) навигационной ссылкой
            const navLink = e.target.closest('.header__nav a, .header__burger-nav a, .mobile-nav__list a, .footer__links a, .header__mobile-nav a');
            if (navLink) {
                // Проверяем, не является ли это ссылка "Город не определен" или подобная
                // которые не должны обрабатываться этой логикой
                if (!navLink.classList.contains('header__city-choice') &&
                    !navLink.closest('.modal-city__content')) {
                    handleNavLinkClick.call(navLink, e);
                }
            }
        });

        // Также можно навесить обработчики напрямую, если делегирование кажется ненадежным
        // Но делегирование обычно предпочтительнее
        /*
        document.querySelectorAll('.header__nav a, .header__burger-nav a, .mobile-nav__list a, .footer__links a, .header__mobile-nav a').forEach(link => {
            // Исключаем кнопку выбора города из этой обработки
            if (!link.classList.contains('header__city-choice')) {
                 link.addEventListener('click', handleNavLinkClick);
            }
        });
        */
    } else {
        console.error('Глобальная переменная citySpecificPagesJs не найдена или не является массивом. Проверьте header.php.');
    }
    // --- КОНЕЦ НОВОЙ ЛОГИКИ ---

});
// --- Конец ---


// --- Липкий хэдер ---
document.addEventListener('DOMContentLoaded', function () {
    const headerTop = document.querySelector('.header__top');
    const headerBottom = document.querySelector('.header__bottom');

    if (!headerTop || !headerBottom) return;

    let headerHeight = headerTop.offsetHeight;

    function updateHeaderHeight() {
        headerHeight = headerTop.offsetHeight;
    }

    function handleScroll() {
        if (window.scrollY > headerHeight) {
            headerTop.classList.add('sticky-header');
            headerBottom.style.marginTop = headerHeight + 'px';
        } else {
            headerTop.classList.remove('sticky-header');
            headerBottom.style.marginTop = '';
        }
    }

    // Инициализация
    window.addEventListener('scroll', handleScroll);
    window.addEventListener('resize', updateHeaderHeight);

    handleScroll();
});
// --- Конец хэдер ---