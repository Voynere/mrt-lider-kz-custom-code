/**
 * UTM persistence + Yandex Metrika reachGoal for MRT Lider KZ.
 */
(function () {
    'use strict';

    var UTM_KEY = 'mrt_utm';
    var UTM_PARAMS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    function reachGoal(goal, params) {
        if (!goal || typeof window.ym !== 'function' || !window.mrtMetrikaId) {
            return;
        }
        try {
            window.ym(window.mrtMetrikaId, 'reachGoal', goal, params || {});
        } catch (e) {
            /* noop */
        }
    }

    function captureUtm() {
        var search = new URLSearchParams(window.location.search);
        var stored = {};
        var hasUtm = false;

        UTM_PARAMS.forEach(function (key) {
            var value = search.get(key);
            if (value) {
                stored[key] = value;
                hasUtm = true;
            }
        });

        if (hasUtm) {
            try {
                sessionStorage.setItem(UTM_KEY, JSON.stringify(stored));
            } catch (e) {
                /* noop */
            }
        }
    }

    function getStoredUtm() {
        try {
            var raw = sessionStorage.getItem(UTM_KEY);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            return {};
        }
    }

    function appendUtmToFormData(data) {
        var utm = getStoredUtm();
        Object.keys(utm).forEach(function (key) {
            if (utm[key]) {
                data.append(key, utm[key]);
            }
        });
    }

    function bindPhoneGoals() {
        document.querySelectorAll('a[href^="tel:"]').forEach(function (link) {
            if (link.dataset.mrtGoalBound) {
                return;
            }
            link.dataset.mrtGoalBound = '1';
            link.addEventListener('click', function () {
                if (document.body.classList.contains('mrt-animals-branch')) {
                    reachGoal('animals_phone_click');
                }
            });
        });
    }

    function bindWhatsappGoals() {
        document.querySelectorAll('.animals-btn--outline[href*="wa.me"], .animals-btn--outline[href*="whatsapp"], a[href*="wa.me"], a[href*="whatsapp"]').forEach(function (link) {
            if (link.dataset.mrtWaGoalBound) {
                return;
            }
            link.dataset.mrtWaGoalBound = '1';
            link.addEventListener('click', function () {
                if (document.body.classList.contains('mrt-animals-branch')) {
                    reachGoal('animals_whatsapp_click');
                }
            });
        });
    }

    function bindPriceGoals() {
        document.querySelectorAll('.animals-prices a, .animals-pricelist').forEach(function (el) {
            if (el.dataset.mrtPriceGoalBound) {
                return;
            }
            el.dataset.mrtPriceGoalBound = '1';
            el.addEventListener('click', function () {
                reachGoal('animals_price_click');
            });
        });
    }

    function bindBookingOpenGoals() {
        document.querySelectorAll('.booking-btn').forEach(function (btn) {
            if (btn.dataset.mrtBookingGoalBound) {
                return;
            }
            btn.dataset.mrtBookingGoalBound = '1';
            btn.addEventListener('click', function () {
                if (document.body.classList.contains('mrt-animals-branch')) {
                    reachGoal('animals_booking_open');
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        captureUtm();
        bindPhoneGoals();
        bindWhatsappGoals();
        bindPriceGoals();
        bindBookingOpenGoals();
    });

    window.mrtReachGoal = reachGoal;
    window.mrtAppendUtmToFormData = appendUtmToFormData;
    window.mrtGetStoredUtm = getStoredUtm;
})();
