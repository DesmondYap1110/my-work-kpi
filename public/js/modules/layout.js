/**
 * Layout chrome: the sidebar toggle and the topbar fullscreen button.
 *
 * The theme ships both inside assets/js/app.js, alongside a lot of
 * demo-page-only code (search, notifications, layout customizer) that assumes
 * markup this app doesn't render. Any null reference in there aborts the rest
 * of the file - which is how the hamburger silently stopped working - so only
 * the pieces actually used are reproduced here.
 *
 * Touches only what the theme's CSS keys off: data-sidebar-size on <html>,
 * .vertical-sidebar-enable and .fullscreen-enable on <body>.
 */
App.module('layout', function () {
    'use strict';

    function isMobile() {
        return window.innerWidth < 992;
    }

    function toggleSidebar() {
        if (isMobile()) {
            document.body.classList.toggle('vertical-sidebar-enable');
            return;
        }

        var html = document.documentElement;
        var current = html.getAttribute('data-sidebar-size') || 'lg';

        html.setAttribute('data-sidebar-size', current === 'sm' ? 'lg' : 'sm');
    }

    function isFullscreen() {
        return document.fullscreenElement || document.webkitFullscreenElement;
    }

    function toggleFullscreen() {
        document.body.classList.toggle('fullscreen-enable');

        if (isFullscreen()) {
            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            return;
        }

        var element = document.documentElement;
        (element.requestFullscreen || element.webkitRequestFullscreen).call(element);
    }

    // Leaving fullscreen with Esc never reaches the button's click handler,
    // so the body class has to be resynced from the browser's own event.
    function syncFullscreenClass() {
        if (!isFullscreen()) {
            document.body.classList.remove('fullscreen-enable');
        }
    }

    var hamburger = document.getElementById('topnav-hamburger-icon');
    if (hamburger) {
        hamburger.addEventListener('click', toggleSidebar);
    }

    var fullscreenBtn = document.querySelector('[data-toggle="fullscreen"]');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function (e) {
            e.preventDefault();
            toggleFullscreen();
        });
    }

    document.addEventListener('fullscreenchange', syncFullscreenClass);
    document.addEventListener('webkitfullscreenchange', syncFullscreenClass);

    var overlay = document.querySelector('.vertical-overlay');
    if (overlay) {
        overlay.addEventListener('click', function () {
            document.body.classList.remove('vertical-sidebar-enable');
        });
    }

    // Closing the mobile sidebar after picking a nav link keeps the overlay
    // from being left open behind the next page.
    document.querySelectorAll('#navbar-nav a.nav-link:not([data-bs-toggle])').forEach(function (link) {
        link.addEventListener('click', function () {
            document.body.classList.remove('vertical-sidebar-enable');
        });
    });
});
