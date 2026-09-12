/**
 * Show / hide for a block of markup.
 *
 *   <button data-collapse="#body-3"><i class="ri-arrow-down-s-line"></i></button>
 *   <div id="body-3" class="js-collapse" hidden> ... </div>
 *
 * Sections are written closed in the markup, so a long page (the KPI tree can
 * run to fifty rows) opens as a short list of headings and the reader chooses
 * what to look inside.
 *
 * Which sections are open is remembered per page in localStorage: saving a row
 * reloads the page, and folding everything shut on every save would make the
 * screen unusable. Storage is per browser and best-effort - if it is blocked,
 * the toggles still work, they just don't survive the reload.
 */
App.module('collapse', function () {
    'use strict';

    var $ = window.jQuery;

    var OPEN_ICON = 'ri-arrow-up-s-line';
    var SHUT_ICON = 'ri-arrow-down-s-line';
    var STORE_KEY = 'collapse:' + window.location.pathname;

    function readOpen() {
        try {
            return JSON.parse(window.localStorage.getItem(STORE_KEY)) || [];
        } catch (error) {
            return [];
        }
    }

    function writeOpen(ids) {
        try {
            window.localStorage.setItem(STORE_KEY, JSON.stringify(ids));
        } catch (error) {
            // Private window, or storage disabled - nothing to do.
        }
    }

    function remember(id, isOpen) {
        if (!id) {
            return;
        }

        var ids = readOpen().filter(function (stored) { return stored !== id; });

        if (isOpen) {
            ids.push(id);
        }

        writeOpen(ids);
    }

    /**
     * Points every toggle aimed at this section the right way up.
     */
    function syncToggles($section, isOpen) {
        $('[data-collapse="#' + $section.attr('id') + '"]').each(function () {
            $(this)
                .attr('aria-expanded', isOpen ? 'true' : 'false')
                .attr('title', isOpen ? 'Hide' : 'Show')
                .find('i')
                .toggleClass(OPEN_ICON, isOpen)
                .toggleClass(SHUT_ICON, !isOpen);
        });
    }

    function set($section, isOpen, store) {
        $section.prop('hidden', !isOpen);
        syncToggles($section, isOpen);

        if (store !== false) {
            remember($section.attr('id'), isOpen);
        }
    }

    $(document).on('click', '[data-collapse]', function (e) {
        e.preventDefault();

        var $section = $($(this).data('collapse'));

        if ($section.length) {
            set($section, $section.prop('hidden'));
        }
    });

    // Restore whatever was open before the last reload.
    var open = readOpen();

    $('.js-collapse').each(function () {
        var $section = $(this);
        var isOpen = open.indexOf($section.attr('id')) !== -1;

        // false: restoring is not a change worth writing back.
        set($section, isOpen, false);
    });

    /**
     * Opens every collapsed section around an element.
     *
     * Used by inline-form when a form to reveal sits inside a closed section -
     * revealing it without this would do nothing visible.
     */
    App.collapse = {
        reveal: function (element) {
            $(element).parents('.js-collapse').each(function () {
                var $section = $(this);

                if ($section.prop('hidden')) {
                    set($section, true);
                }
            });
        },
    };
});
