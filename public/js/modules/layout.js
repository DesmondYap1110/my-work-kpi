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

    /**
     * Collapsed size. "sm-hover" rather than "sm": both are a 70px icon rail,
     * but sm-hover expands back to 250px with its labels while the pointer is
     * over it (pure CSS in the theme), so a collapsed sidebar is still
     * readable. Plain "sm" leaves you guessing at six unlabelled icons.
     */
    var COLLAPSED = 'sm-hover';

    var html = document.documentElement;

    // What the sidebar should be when there is room for it. Kept separately
    // because the mobile layout overrides the attribute, and the desktop
    // choice has to survive that round trip.
    var desktopSize = html.getAttribute('data-sidebar-size') || 'lg';

    /**
     * Width below which the sidebar leaves the layout and becomes a slide-in
     * overlay.
     *
     * 768, matching the theme CSS - not 992. They disagreed, and between the
     * two the sidebar was still laid out inline while this file treated the
     * page as mobile: the hamburger toggled an overlay class that changed
     * nothing on screen, so the sidebar could not be closed at all. iPad
     * portrait is 768 wide, right in the middle of that gap.
     */
    var OVERLAY_BELOW = 768;

    function isMobile() {
        return window.innerWidth < OVERLAY_BELOW;
    }

    function toggleSidebar() {
        if (isMobile()) {
            // Below the breakpoint the sidebar is an overlay that slides in at
            // full width - the size attribute plays no part.
            document.body.classList.toggle('vertical-sidebar-enable');
            return;
        }

        desktopSize = desktopSize === COLLAPSED ? 'lg' : COLLAPSED;
        html.setAttribute('data-sidebar-size', desktopSize);
    }

    /**
     * Keeps the two layouts from bleeding into each other.
     *
     * Collapsing on a wide window and then narrowing it used to leave the
     * collapsed size in place, so the mobile overlay opened as a 70px strip of
     * unlabelled icons sitting on top of the page instead of the full-width
     * menu. The theme's own app.js resyncs on resize; this is the part of it
     * worth keeping.
     */
    function syncLayoutToWidth() {
        if (isMobile()) {
            html.setAttribute('data-sidebar-size', 'lg');
            return;
        }

        html.setAttribute('data-sidebar-size', desktopSize);
        document.body.classList.remove('vertical-sidebar-enable');
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

    syncLayoutToWidth();

    var wasMobile = isMobile();
    var resizeTimer;

    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);

        resizeTimer = window.setTimeout(function () {
            // Only the crossing matters; resizing within one layout is a no-op.
            if (isMobile() !== wasMobile) {
                wasMobile = isMobile();
                syncLayoutToWidth();
            }
        }, 150);
    });

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
