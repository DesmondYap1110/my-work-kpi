/**
 * The ? button that shows and hides a note or example. Hidden by default.
 *
 *   <button type="button" class="kpi-help-btn" data-help-toggle
 *           aria-controls="some-id" aria-expanded="false">?</button>
 *   <div id="some-id" hidden>...</div>
 *
 * Or, for a short explanation, show it on hover instead of a click:
 *
 *   <button type="button" class="kpi-help-btn" data-help-hover="What this means.">?</button>
 *
 * Delegated, so it works for any number of them on a page. The hover text is
 * set as plain text, never HTML.
 */
App.module('help-toggle', function () {
    'use strict';

    if (window.bootstrap && bootstrap.Popover) {
        new bootstrap.Popover(document.body, {
            selector: '[data-help-hover]',
            trigger: 'hover focus',
            placement: 'right',
            html: false,
            // Same white card as the job scope hover - see text-peek.js.
            customClass: 'text-peek-popover',
            content: function () {
                return this.getAttribute('data-help-hover');
            },
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest && event.target.closest('[data-help-toggle]');
        var target = button && document.getElementById(button.getAttribute('aria-controls'));

        if (!target) {
            return;
        }

        target.hidden = !target.hidden;
        button.setAttribute('aria-expanded', String(!target.hidden));
        button.classList.toggle('is-open', !target.hidden);
    });
});
