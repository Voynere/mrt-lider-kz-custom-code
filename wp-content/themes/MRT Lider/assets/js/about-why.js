document.addEventListener('DOMContentLoaded', function() {
    const section = document.querySelector('.about__why');
    const middleWrapper = document.querySelector('.about__middle-wrapper');
    const items = section ? section.querySelectorAll('.about__why-item') : [];
    if (!section || items.length === 0) {
        return;
    }

    const DESKTOP_CLOCKWISE = [0, 1, 3, 2];
    const MOBILE_CLOCKWISE = [0, 1, 2, 3];
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let ticking = false;

    function getClockwiseOrder() {
        return window.matchMedia('(max-width: 768px)').matches ? MOBILE_CLOCKWISE : DESKTOP_CLOCKWISE;
    }

    function getStepIndex(itemIndex, order) {
        return order.indexOf(itemIndex);
    }

    function applyState(activeStep, progress, order) {
        items.forEach(function(item, itemIndex) {
            const stepIndex = getStepIndex(itemIndex, order);
            if (stepIndex < 0) {
                return;
            }

            item.classList.toggle('is-step-active', stepIndex === activeStep);
            item.classList.toggle('is-step-done', stepIndex < activeStep);
        });

        section.style.setProperty('--why-progress', String(progress));
        section.classList.add('is-animated');

        if (middleWrapper && !prefersReducedMotion) {
            const rotation = progress * 360;
            middleWrapper.style.transform = 'translateY(30px) rotate(' + rotation + 'deg)';
        }
    }

    function update() {
        if (prefersReducedMotion) {
            applyState(items.length - 1, 1, getClockwiseOrder());
            items.forEach(function(item) {
                item.classList.add('is-step-done');
                item.classList.remove('is-step-active');
            });
            return;
        }

        const rect = section.getBoundingClientRect();
        const viewport = window.innerHeight;
        if (rect.bottom < viewport * 0.1 || rect.top > viewport * 0.95) {
            return;
        }

        const order = getClockwiseOrder();
        const travel = Math.max(rect.height, viewport * 0.55);
        const passed = Math.min(Math.max(viewport * 0.72 - rect.top, 0), travel);
        const progress = passed / travel;
        const activeStep = Math.min(Math.floor(progress * order.length), order.length - 1);

        applyState(activeStep, progress, order);
    }

    function onScroll() {
        if (ticking) {
            return;
        }
        ticking = true;
        requestAnimationFrame(function() {
            update();
            ticking = false;
        });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    update();
});
