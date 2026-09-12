/**
 * Reveals an inline form instead of opening a dialog.
 *
 *   <button data-inline-form="#add-objective-3">Add</button>
 *   <div class="js-inline-form" id="add-objective-3" hidden> ... </div>
 *
 * Used by the KPI tree so adding a category, objective or item happens in
 * place, in the row it belongs to, rather than in a modal that hides the
 * structure you are editing.
 *
 * A toggle also hides any sibling form that is already open, so only one
 * inline editor is live at a time within the same block.
 */
App.module('inline-form', function () {
    'use strict';

    var $ = window.jQuery;

    function close($form) {
        $form.prop('hidden', true);
        // Reset so a cancelled edit doesn't leave its values behind when the
        // form is reopened.
        var el = $form[0];
        if (el && el.tagName === 'FORM') {
            el.reset();
        } else {
            $form.find('form').each(function () { this.reset(); });
        }
    }

    function open($form) {
        // The form may sit inside a section that is folded shut; revealing it
        // there would show nothing.
        if (App.collapse) {
            App.collapse.reveal($form[0]);
        }

        $form.prop('hidden', false);
        $form.find('input:not([type=hidden]), textarea, select').first().trigger('focus');
    }

    $(document).on('click', '[data-inline-form]', function (e) {
        e.preventDefault();

        var $form = $($(this).data('inline-form'));

        if (!$form.length) {
            return;
        }

        // Whatever this toggle replaces - the display row, or another open
        // form in the same group - steps aside first.
        var hide = $(this).data('inline-hide');
        if (hide) {
            $(hide).prop('hidden', true);
        }

        if ($form.prop('hidden')) {
            open($form);
        } else {
            close($form);
        }
    });

    $(document).on('click', '.js-inline-form-cancel', function (e) {
        e.preventDefault();

        var $form = $(this).closest('.js-inline-form');
        close($form);

        // Bring the display row back when this was an edit.
        var restore = $(this).data('inline-restore');
        if (restore) {
            $(restore).prop('hidden', false);
        }
    });
});
