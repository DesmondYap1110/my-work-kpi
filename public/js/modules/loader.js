/**
 * Drives the <x-loader /> overlay (resources/views/components/loader.blade.php).
 *
 * Shows it as soon as a full page navigation starts, so a slow response
 * doesn't leave the user looking at an unchanged page with no feedback, and
 * hides it again when the browser restores the page from the back/forward
 * cache - otherwise going "back" would land on a page stuck behind the veil.
 */
App.module('loader', function () {
    'use strict';

    var panel = document.getElementById('loader_master');

    if (!panel) {
        return;
    }

    var loader = {
        show: function () {
            panel.classList.add('is-visible');
            panel.setAttribute('aria-hidden', 'false');
        },
        hide: function () {
            panel.classList.remove('is-visible');
            panel.setAttribute('aria-hidden', 'true');
        },
    };

    window.appLoader = loader;

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[href]');

        if (!link) {
            return;
        }

        var href = link.getAttribute('href');

        // Anchors, JS links and Bootstrap triggers (collapse, modal, dropdown)
        // stay on the page, and a modified click opens a new tab, so none of
        // them are a navigation this overlay should cover.
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) {
            return;
        }
        if (link.target === '_blank' || link.hasAttribute('data-bs-toggle')) {
            return;
        }
        // A file download (CSV export) never leaves the page, so the overlay
        // would stay up with nothing to take it down again.
        if (link.hasAttribute('download')) {
            return;
        }
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
            return;
        }

        loader.show();
    });

    document.addEventListener('submit', function (event) {
        // A delete confirm that the user cancels, and the datatable filter
        // form, both preventDefault() in their own handlers - which may run
        // after this one - so decide once the event has finished bubbling.
        setTimeout(function () {
            if (!event.defaultPrevented) {
                loader.show();
            }
        }, 0);
    });

    window.addEventListener('pageshow', loader.hide);
});
