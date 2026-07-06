// Слайдер врачей
var swiper = new Swiper(".licenseSwiper", {
    slidesPerView: 2,
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

document.addEventListener('DOMContentLoaded', function () {
    // scope — только внутри .license
    const licenseSection = document.querySelector('.license');
    if (!licenseSection) return;

    const tablist = licenseSection.querySelector('.doc-tabs');
    if (!tablist) return;

    const tabs = Array.from(tablist.querySelectorAll('.doc-tab'));
    const panels = Array.from(licenseSection.querySelectorAll('.doc-panel'));

    function activateTab(tab) {
        tabs.forEach(t => {
            const active = t === tab;
            t.classList.toggle('active', active);
            t.setAttribute('aria-selected', active ? 'true' : 'false');
            t.tabIndex = active ? 0 : -1;
        });

        const key = tab.dataset.tab;
        panels.forEach(p => {
            p.hidden = p.dataset.tab !== key;
        });
    }

    tabs.forEach((tab, idx) => {
        tab.addEventListener('click', function () {
            activateTab(tab);
        });

        tab.addEventListener('keydown', function (ev) {
            const key = ev.key;
            if (key === 'ArrowRight' || key === 'ArrowLeft') {
                ev.preventDefault();
                const dir = key === 'ArrowRight' ? 1 : -1;
                const next = (idx + dir + tabs.length) % tabs.length;
                tabs[next].focus();
            } else if (key === 'Home') {
                ev.preventDefault();
                tabs[0].focus();
            } else if (key === 'End') {
                ev.preventDefault();
                tabs[tabs.length - 1].focus();
            } else if (key === 'Enter' || key === ' ') {
                ev.preventDefault();
                activateTab(tab);
            }
        });
    });

    // инициализация: активируем первый таб или тот, что помечен .active
    const initial = tabs.find(t => t.classList.contains('active')) || tabs[0];
    if (initial) activateTab(initial);
});

document.addEventListener('DOMContentLoaded', function() {
    const tabsContainer = document.querySelector('.doc-tabs');
    const licenseContent = document.querySelector('.license__content');
    const tabs = document.querySelectorAll('.doc-tab');
    
    if (licenseContent && tabsContainer) {
        if (tabs.length === 1) {
            // Если вкладка всего одна — сразу обнуляем радиус
            licenseContent.style.borderTopLeftRadius = '0px';
        } else if (tabs.length > 1) {
            // Обработчик на контейнере табов
            tabsContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('doc-tab')) {
                    const clickedTab = e.target;
                    
                    if (clickedTab.id === 'tab-filial-1' && clickedTab.classList.contains('active')) {
                        licenseContent.style.borderTopLeftRadius = '0px';
                    } else {
                        licenseContent.style.borderTopLeftRadius = '';
                    }
                }
            });
            
            // Проверка начального состояния
            const initialActiveTab = document.querySelector('.doc-tab.active');
            if (initialActiveTab && initialActiveTab.id === 'tab-filial-1') {
                licenseContent.style.borderTopLeftRadius = '0px';
            }
        }
    }
});
