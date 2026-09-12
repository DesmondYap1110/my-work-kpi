/**
 * Confirmation dialog for destructive actions.
 *
 *   <form class="js-confirm-delete"> ... </form>
 *   <form data-confirm="Cancel this project?" data-confirm-label="Cancel Project">
 *
 * Replaces window.confirm(), which can't be styled, announces the site's
 * hostname instead of the record, and puts OK on the left where Delete is the
 * easier of the two to hit by accident. The shell lives in
 * resources/views/layouts/admin/content/confirm-modal.blade.php and is on
 * every page, so a form opts in with one class and nothing else.
 *
 * Handlers are delegated: the datatable list classes inject their delete forms
 * as HTML on every AJAX draw, so the elements do not exist at boot.
 */
App.module('confirm', function () {
    'use strict';

    var $ = window.jQuery;
    var $modal = $('#confirmModal');

    if (!$modal.length) {
        return;
    }

    var modal = new bootstrap.Modal($modal[0]);
    var $title = $modal.find('#confirm-modal-title');
    var $message = $modal.find('#confirm-modal-message');
    var $accept = $modal.find('.js-confirm-accept');

    // The form waiting on an answer.
    var pending = null;

    var DEFAULTS = {
        'js-confirm-delete': {
            title: 'Delete',
            message: 'Are you sure you want to delete this record?',
            label: 'Delete',
            icon: 'ri-delete-bin-6-line',
        },
        'js-confirm-cancel': {
            title: 'Cancel project',
            message: 'Are you sure you want to cancel this project?',
            label: 'Cancel Project',
            icon: 'ri-close-circle-line',
        },
    };

    function settingsFor($form) {
        var base = $form.hasClass('js-confirm-cancel')
            ? DEFAULTS['js-confirm-cancel']
            : DEFAULTS['js-confirm-delete'];

        return {
            title: $form.data('confirm-title') || base.title,
            // data-confirm carries the question itself, so a form can name
            // what is about to go: data-confirm="Delete 'Soft Skill' and its
            // 4 objectives?"
            message: $form.data('confirm') || base.message,
            label: $form.data('confirm-label') || base.label,
            icon: $form.data('confirm-icon') || base.icon,
        };
    }

    $(document).on('submit', '.js-confirm-delete, .js-confirm-cancel, [data-confirm]', function (e) {
        var $form = $(this);

        // Second pass, after the dialog was accepted - let it through.
        if ($form.data('confirmed')) {
            $form.removeData('confirmed');
            return;
        }

        e.preventDefault();

        var settings = settingsFor($form);

        $title.text(settings.title);
        $message.text(settings.message);
        $accept.html('<i class="' + settings.icon + '"></i>' + settings.label);

        pending = $form;
        modal.show();
    });

    $accept.on('click', function () {
        if (!pending) {
            return;
        }

        var $form = pending;
        pending = null;

        modal.hide();

        // Flag it, then go back through jQuery's submit so any other module
        // listening for this form still sees it.
        $form.data('confirmed', true).trigger('submit');
    });

    // An abandoned dialog must not leave a form primed to submit later.
    $modal.on('hidden.bs.modal', function () {
        pending = null;
    });
});
