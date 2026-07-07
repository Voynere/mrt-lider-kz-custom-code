document.addEventListener('DOMContentLoaded', function() {
    const block = document.querySelector('.about__numbers');
    if (!block) {
        return;
    }

    const counters = block.querySelectorAll('.about__numbers-numb[data-count]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let started = false;

    function formatCount(value) {
        return Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function animateCounter(el, target, duration, delay) {
        if (prefersReducedMotion) {
            el.textContent = formatCount(target);
            return;
        }

        window.setTimeout(function() {
            const startAt = performance.now();

            function frame(now) {
                const progress = Math.min((now - startAt) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatCount(target * eased);

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    el.textContent = formatCount(target);
                }
            }

            el.textContent = '0';
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
            animateCounter(el, target, 1600, index * 120);
        });
    }

    if (prefersReducedMotion) {
        block.classList.add('is-visible');
        counters.forEach(function(el) {
            const target = parseInt(el.getAttribute('data-count') || '0', 10);
            if (target) {
                el.textContent = formatCount(target);
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
