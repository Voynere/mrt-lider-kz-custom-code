document.addEventListener('DOMContentLoaded', function() {
    const block = document.querySelector('.animals-home__stats');
    if (!block) {
        return;
    }

    const counters = block.querySelectorAll('.animals-home__stat-value[data-count]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let started = false;

    function formatCount(value, suffix) {
        return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + (suffix || '');
    }

    function animateCounter(el, target, duration, delay) {
        const suffix = el.getAttribute('data-suffix') || '';

        if (prefersReducedMotion) {
            el.textContent = formatCount(target, suffix);
            return;
        }

        window.setTimeout(function() {
            const startAt = performance.now();

            function frame(now) {
                const progress = Math.min((now - startAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatCount(target * eased, suffix);

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    el.textContent = formatCount(target, suffix);
                }
            }

            el.textContent = '0' + suffix;
            requestAnimationFrame(frame);
        }, delay);
    }

    function startAnimation() {
        if (started) {
            return;
        }
        started = true;
        block.classList.add('is-visible');

        counters.forEach(function(el, index) {
            const target = parseInt(el.getAttribute('data-count') || '0', 10);
            if (!target) {
                return;
            }
            animateCounter(el, target, 1400, index * 100);
        });
    }

    if (prefersReducedMotion) {
        block.classList.add('is-visible');
        counters.forEach(function(el) {
            const target = parseInt(el.getAttribute('data-count') || '0', 10);
            if (target) {
                el.textContent = formatCount(target, el.getAttribute('data-suffix') || '');
            }
        });
        return;
    }

    if (!('IntersectionObserver' in window)) {
        startAnimation();
        return;
    }

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                startAnimation();
                observer.disconnect();
            }
        });
    }, {
        threshold: 0.35,
        rootMargin: '0px 0px -40px 0px',
    });

    observer.observe(block);
});
