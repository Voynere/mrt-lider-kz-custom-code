/**
 * Site-wide search: overlay + AJAX (services, articles, FAQ).
 */
(function () {
    'use strict';

    var DEBOUNCE_MS = 300;
    var debounceTimer = null;
    var lastQuery = '';

    function ajaxUrl() {
        return window.MRT_AJAX_URL || '/wp-admin/admin-ajax.php';
    }

    function citySlug() {
        return window.CURRENT_CITY_SLUG || 'almaty';
    }

    function openOverlay(prefill) {
        var overlay = document.getElementById('mrt-search-overlay');
        if (!overlay) return;
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mrt-search-open');
        var input = overlay.querySelector('.mrt-search-overlay__input');
        if (input) {
            if (prefill) input.value = prefill;
            input.focus();
            if (input.value.length >= 2) {
                runSearch(input.value);
            }
        }
    }

    function closeOverlay() {
        var overlay = document.getElementById('mrt-search-overlay');
        if (!overlay) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mrt-search-open');
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function renderResults(data) {
        var body = document.querySelector('.mrt-search-overlay__body');
        if (!body) return;

        var html = '';
        var groups = [
            { key: 'services', title: 'Услуги' },
            { key: 'articles', title: 'Статьи' },
            { key: 'faq', title: 'Вопросы и ответы' },
        ];

        var hasAny = false;
        groups.forEach(function (g) {
            var items = data[g.key] || [];
            if (!items.length) return;
            hasAny = true;
            html += '<div class="mrt-search-group"><h3 class="mrt-search-group__title">' + escapeHtml(g.title) + '</h3><ul class="mrt-search-group__list">';
            items.forEach(function (item) {
                html += '<li><a href="' + escapeHtml(item.url) + '" class="mrt-search-group__link">' +
                    escapeHtml(item.title) + '</a></li>';
            });
            html += '</ul></div>';
        });

        if (!hasAny) {
            html = '<p class="mrt-search-overlay__empty">Ничего не найдено. Попробуйте другой запрос.</p>';
        }
        body.innerHTML = html;
    }

    function runSearch(q) {
        q = (q || '').trim();
        lastQuery = q;
        var body = document.querySelector('.mrt-search-overlay__body');
        if (!body) return;

        if (q.length < 2) {
            body.innerHTML = '<p class="mrt-search-overlay__hint">Введите минимум 2 символа</p>';
            return;
        }

        body.innerHTML = '<p class="mrt-search-overlay__hint">Поиск…</p>';

        var params = new URLSearchParams({
            action: 'mrt_site_search',
            q: q,
            city: citySlug(),
        });

        fetch(ajaxUrl() + '?' + params.toString(), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (lastQuery !== q) return;
                if (data.success && data.data) {
                    renderResults(data.data);
                } else {
                    body.innerHTML = '<p class="mrt-search-overlay__empty">Ошибка поиска</p>';
                }
            })
            .catch(function () {
                if (lastQuery === q) {
                    body.innerHTML = '<p class="mrt-search-overlay__empty">Ошибка соединения</p>';
                }
            });

        if (typeof window.mrtTrack === 'function') {
            window.mrtTrack('search_submit', { query: q });
        }
    }

    function onSearchInput(e) {
        var q = e.target.value;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () { runSearch(q); }, DEBOUNCE_MS);
    }

    /** Client-side filter for homepage popular cards */
    function filterFindCards(q) {
        var cards = document.querySelectorAll('.find__card');
        if (!cards.length) return;
        q = (q || '').trim().toLowerCase();
        cards.forEach(function (card) {
            var title = (card.querySelector('.find__card-title') || {}).textContent || '';
            var match = !q || title.toLowerCase().indexOf(q) !== -1;
            card.style.display = match ? '' : 'none';
        });
    }

    /** Client-side filter for FAQ page */
    function filterFaqItems(q) {
        var items = document.querySelectorAll('.answers__tabs-item');
        if (!items.length) return;
        q = (q || '').trim().toLowerCase();
        items.forEach(function (item) {
            var title = item.querySelector('.answers__tabs-title');
            var text = item.querySelector('.answers__tabs-text');
            var hay = ((title && title.textContent) || '') + ' ' + ((text && text.textContent) || '');
            var match = !q || hay.toLowerCase().indexOf(q) !== -1;
            item.style.display = match ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.mrt-search-open, .header__search-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openOverlay('');
            });
        });

        var overlay = document.getElementById('mrt-search-overlay');
        if (overlay) {
            overlay.querySelectorAll('[data-mrt-search-close]').forEach(function (el) {
                el.addEventListener('click', closeOverlay);
            });
            var input = overlay.querySelector('.mrt-search-overlay__input');
            if (input) {
                input.addEventListener('input', onSearchInput);
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeOverlay();
                });
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeOverlay();
        });

        var findSearch = document.getElementById('find-search');
        if (findSearch) {
            findSearch.addEventListener('input', function () {
                filterFindCards(findSearch.value);
            });
            findSearch.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    openOverlay(findSearch.value);
                }
            });
        }

        var faqInput = document.querySelector('.answers__search-inp');
        if (faqInput) {
            faqInput.addEventListener('input', function () {
                filterFaqItems(faqInput.value);
            });
            var faqBtn = document.querySelector('.answers__search-btn');
            if (faqBtn) {
                faqBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    filterFaqItems(faqInput.value);
                });
            }
        }
    });
})();
