(function () {
    'use strict';

    function pluralMinutes(value) {
        var mod10 = value % 10;
        var mod100 = value % 100;
        if (mod10 === 1 && mod100 !== 11) {
            return 'минута';
        }
        if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) {
            return 'минуты';
        }
        return 'минут';
    }

    function getCityNowMinutes(timezone) {
        try {
            var parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            }).formatToParts(new Date());

            var hour = 0;
            var minute = 0;
            parts.forEach(function (part) {
                if (part.type === 'hour') {
                    hour = parseInt(part.value, 10);
                }
                if (part.type === 'minute') {
                    minute = parseInt(part.value, 10);
                }
            });

            return hour * 60 + minute;
        } catch (error) {
            var now = new Date();
            return now.getHours() * 60 + now.getMinutes();
        }
    }

    function minutesUntilClose(nowMin, openMin, closeMin, overnight) {
        if (overnight) {
            if (nowMin >= openMin) {
                return (24 * 60 - nowMin) + closeMin;
            }
            if (nowMin < closeMin) {
                return closeMin - nowMin;
            }
            return null;
        }

        if (nowMin < openMin || nowMin >= closeMin) {
            return null;
        }

        return closeMin - nowMin;
    }

    function minutesUntilOpen(nowMin, openMin, closeMin, overnight) {
        if (overnight) {
            if (nowMin >= openMin || nowMin < closeMin) {
                return null;
            }
            return openMin - nowMin;
        }

        if (nowMin < openMin) {
            return openMin - nowMin;
        }

        return null;
    }

    function updateCountdownBanner(element, minutesUntilFn, minutesClass) {
        var openMin = parseInt(element.getAttribute('data-open'), 10);
        var closeMin = parseInt(element.getAttribute('data-close'), 10);
        var overnight = element.getAttribute('data-overnight') === '1';
        var timezone = element.getAttribute('data-tz') || 'Asia/Yekaterinburg';
        var minutesNode = element.querySelector('.' + minutesClass);

        if (!minutesNode || Number.isNaN(openMin) || Number.isNaN(closeMin)) {
            element.classList.remove('is-visible');
            return;
        }

        var nowMin = getCityNowMinutes(timezone);
        var left = minutesUntilFn(nowMin, openMin, closeMin, overnight);

        if (left === null || left <= 0 || left > 60) {
            element.classList.remove('is-visible');
            return;
        }

        minutesNode.textContent = left + '\u00a0' + pluralMinutes(left);
        element.classList.add('is-visible');
    }

    function updateClosingBanner(element) {
        updateCountdownBanner(element, minutesUntilClose, 'header__schedule-closing-min');
    }

    function updateOpeningBanner(element) {
        updateCountdownBanner(element, minutesUntilOpen, 'header__schedule-opening-min');
    }

    function initClosingCountdown() {
        var closingBanners = document.querySelectorAll('.header__schedule-closing[data-open]');
        var openingBanners = document.querySelectorAll('.header__schedule-opening[data-open]');
        if (!closingBanners.length && !openingBanners.length) {
            return;
        }

        var tick = function () {
            closingBanners.forEach(updateClosingBanner);
            openingBanners.forEach(updateOpeningBanner);
        };

        tick();
        window.setInterval(tick, 30000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClosingCountdown);
    } else {
        initClosingCountdown();
    }
})();
