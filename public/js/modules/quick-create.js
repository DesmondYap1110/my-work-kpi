/**
 * Creates a missing lookup record from inside the form that needs it.
 *
 *   <select name="team_id" data-quick-create="#quickCreateTeam"> ... </select>
 *
 *   <div class="modal" id="quickCreateTeam"
 *        data-quick-create-target="[name=team_id]">
 *       <form action="{{ route('teams.store') }}" method="POST"> ... </form>
 *   </div>
 *
 * The member form asks for a team, and on a fresh install there are none -
 * leaving the form to go and make one loses everything typed so far. The
 * modal posts on its own (Accept: application/json), and the endpoint returns
 * {id, label}, which becomes the selected option. Nothing is reloaded, so the
 * half-filled form underneath is untouched.
 *
 * Validation errors (422) are shown inside the modal against their field, so
 * a duplicate name doesn't close it and lose what was typed.
 */
App.module('quick-create', function () {
    'use strict';

    var $ = window.jQuery;

    function showErrors($form, errors) {
        Object.keys(errors).forEach(function (field) {
            var $input = $form.find('[name="' + field + '"]');
            $input.addClass('is-invalid');
            $input.siblings('.invalid-feedback').remove();
            $input.after('<span class="invalid-feedback d-block">' + errors[field][0] + '</span>');
        });
    }

    function clearErrors($form) {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').remove();
        $form.find('.js-quick-create-error').remove();
    }

    /**
     * For a failure that belongs to no particular field.
     */
    function showFormError($form, message) {
        $form.find('[name]').first().closest('div')
            .before('<p class="js-quick-create-error invalid-feedback d-block mb-2">' + message + '</p>');
    }

    $(document).on('click', '[data-quick-create-open]', function (e) {
        e.preventDefault();

        var $modal = $($(this).data('quick-create-open'));

        if ($modal.length) {
            clearErrors($modal.find('form'));
            new bootstrap.Modal($modal[0]).show();
        }
    });

    $(document).on('submit', '[data-quick-create-target] form', function (e) {
        e.preventDefault();

        var form = this;
        var $form = $(form);
        var $modal = $form.closest('[data-quick-create-target]');
        var $select = $($modal.data('quick-create-target'));
        var $submit = $form.find('[type=submit]');

        clearErrors($form);
        $submit.prop('disabled', true);

        $.ajax({
            url: form.action,
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: { Accept: 'application/json' },
        }).done(function (res) {
            $select
                .append($('<option>', { value: res.id, text: res.label }))
                .val(res.id)
                .trigger('change');

            form.reset();
            bootstrap.Modal.getInstance($modal[0]).hide();
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                showErrors($form, xhr.responseJSON.errors);
                return;
            }

            showFormError($form, 'Could not save. Please try again.');
        }).always(function () {
            $submit.prop('disabled', false);
        });
    });
});
