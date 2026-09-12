/**
 * Reopens a modal whose form came back with validation errors.
 *
 *   <div class="modal" id="addProject" data-open-on-error> ... </div>
 *
 * A modal form that posts normally takes the whole page with it. When
 * validation fails the redirect lands back on a freshly rendered page with the
 * modal closed, so the work disappears and nothing obviously went wrong - the
 * error alert is there, but it sits behind where the dialog used to be.
 *
 * The attribute is only rendered when there are errors (see the modal
 * component), so finding it is enough to know the last submit failed.
 */
App.module('modal-errors', function () {
    'use strict';

    var modal = document.querySelector('.modal[data-open-on-error]');

    if (!modal) {
        return;
    }

    new bootstrap.Modal(modal).show();
});
