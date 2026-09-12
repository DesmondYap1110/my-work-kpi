/**
 * Changes a row's status where it stands.
 *
 *   <select class="js-task-status"
 *           data-url="/project-tasks/12/status"
 *           data-row="#task-12"> ... </select>
 *
 * Moving a task is the single most common action on a board, and a full page
 * reload for each one would throw away the reader's scroll position and every
 * open section. This PATCHes the new value and repaints the row instead.
 *
 * The server decides what the change means - the completed_at stamp and the
 * progress figure come back from it rather than being guessed here.
 */
App.module('status-select', function () {
    'use strict';

    var $ = window.jQuery;

    function token() {
        return $('meta[name=csrf-token]').attr('content') || $('input[name=_token]').first().val();
    }

    /**
     * Flashes the row so a change made by a dropdown is still noticeable.
     */
    function acknowledge($row) {
        if (!$row.length) {
            return;
        }

        $row.addClass('is-status-changed');
        window.setTimeout(function () { $row.removeClass('is-status-changed'); }, 900);
    }

    $(document).on('change', '.js-task-status', function () {
        var $select = $(this);
        var $row = $($select.data('row'));
        var previous = $select.data('previous');

        $select.prop('disabled', true);

        $.ajax({
            url: $select.data('url'),
            type: 'POST',
            data: { _method: 'PATCH', _token: token(), status: $select.val() },
            dataType: 'json',
        }).done(function (res) {
            acknowledge($row);

            // A task that just became done is finished work; one that was
            // reopened is not. The row's own "done" styling follows.
            $row.toggleClass('is-done', res.completed_at !== null);
            $select.data('previous', $select.val());
        }).fail(function () {
            // Put the control back to what the server still believes, rather
            // than leaving it showing a change that did not happen.
            if (previous !== undefined) {
                $select.val(previous);
            }

            window.alert('Could not change the status. Please try again.');
        }).always(function () {
            $select.prop('disabled', false);
        });
    });

    // Remember the starting value, so a failed change has something to go back
    // to. Delegated as well as run at boot, because the task list draws its
    // rows over AJAX - those selects do not exist yet when this runs.
    $(document).on('focus', '.js-task-status', function () {
        $(this).data('previous', $(this).val());
    });

    $('.js-task-status').each(function () {
        $(this).data('previous', $(this).val());
    });
});
