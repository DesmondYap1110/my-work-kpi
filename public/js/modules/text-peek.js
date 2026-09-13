/**
 * Shows the whole of a truncated table cell on hover.
 *
 *   <span class="text-peek" tabindex="0" data-full-text="The full job scope...">The full...</span>
 *
 * Rendered by Datatables::tbTruncated(), which only adds data-full-text when
 * something was actually cut off.
 *
 * A popover rather than a modal: it opens on hover, and a dialog taking over
 * the screen every time the pointer crosses a row would make the table
 * unusable. Also opens on keyboard focus, for anyone not using a mouse.
 *
 * Delegated through Bootstrap's `selector` option, because the list tables
 * draw their rows over AJAX - the cells do not exist when this runs. The text
 * is set as plain text (html: false), so a job scope containing markup is shown,
 * never run.
 */
App.module('text-peek', function () {
    'use strict';

    if (!window.bootstrap || !bootstrap.Popover) {
        return;
    }

    new bootstrap.Popover(document.body, {
        selector: '.text-peek[data-full-text]',
        trigger: 'hover focus',
        placement: 'top',
        html: false,
        customClass: 'text-peek-popover',
        content: function () {
            return this.getAttribute('data-full-text');
        },
    });
});
