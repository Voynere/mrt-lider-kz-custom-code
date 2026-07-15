(function () {
    'use strict';

    function activateFrame(frame, activator) {
        frame.classList.add('is-click-active');
        if (!activator) {
            return;
        }
        activator.setAttribute('aria-hidden', 'true');
        activator.tabIndex = -1;
    }

    function deactivateFrame(frame, activator) {
        frame.classList.remove('is-click-active');
        if (!activator) {
            return;
        }
        activator.removeAttribute('aria-hidden');
        activator.tabIndex = 0;
    }

    function enableIframePointerEvents(iframe) {
        if (!iframe) {
            return;
        }
        iframe.style.pointerEvents = 'auto';
        iframe.removeAttribute('tabindex');
    }

    function loadLazyIframe(frame) {
        var iframe = frame.querySelector('iframe[data-src]');
        if (!iframe) {
            return null;
        }
        var src = iframe.getAttribute('data-src');
        if (src && !iframe.getAttribute('src')) {
            iframe.setAttribute('src', src);
        }
        iframe.hidden = false;
        iframe.removeAttribute('hidden');
        iframe.style.display = '';
        enableIframePointerEvents(iframe);
        return iframe;
    }

    function initInteractFrame(frame) {
        var activator = frame.querySelector('[data-click-activator]');
        if (!activator) {
            return;
        }

        var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

        activator.addEventListener('click', function (event) {
            event.preventDefault();
            activateFrame(frame, activator);
            frame.querySelectorAll('iframe').forEach(enableIframePointerEvents);
        });

        frame.addEventListener('mouseleave', function () {
            if (finePointer.matches && frame.classList.contains('is-click-active')) {
                deactivateFrame(frame, activator);
                frame.querySelectorAll('iframe').forEach(function (iframe) {
                    iframe.style.pointerEvents = 'none';
                });
            }
        });
    }

    function initLazyFrame(frame) {
        var activator = frame.querySelector('[data-click-activator]');
        if (!activator || frame.dataset.clickBound === '1') {
            return;
        }
        frame.dataset.clickBound = '1';

        activator.addEventListener('click', function (event) {
            event.preventDefault();
            loadLazyIframe(frame);
            activateFrame(frame, activator);
            var poster = frame.querySelector('.tour__poster, img.tour__poster');
            if (poster) {
                poster.style.display = 'none';
            }
        });
    }

    function initClickFrames() {
        document.querySelectorAll('[data-click-frame]').forEach(function (frame) {
            var mode = frame.getAttribute('data-click-mode') || 'interact';
            if (mode === 'lazy') {
                initLazyFrame(frame);
            } else {
                initInteractFrame(frame);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClickFrames);
    } else {
        initClickFrames();
    }
})();
