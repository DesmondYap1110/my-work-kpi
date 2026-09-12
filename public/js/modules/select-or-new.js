/**
 * Lets a <select> create a new entry instead of only picking an existing one.
 *
 * Mark the select with data-allow-new naming the text field that carries the
 * new value. Choosing the "+ Add new" option reveals that input; the
 * controller creates the record when it arrives filled in.
 *
 *   <select name="objective_info_id" data-allow-new="objective_info_title">
 *       <option value="">Select Objective</option>
 *       ...
 *       <option value="__new__">+ Add new objective</option>
 *   </select>
 *   <input type="text" name="objective_info_title" class="form-control" hidden>
 *
 * Keeps the catalog usable without a separate admin screen: the list grows as
 * people use it, rather than being fixed at seed time.
 */
App.module('select-or-new', function () {
    'use strict';

    var $ = window.jQuery;
    var NEW = '__new__';

    function companion(select) {
        var name = select.getAttribute('data-allow-new');
        var scope = select.closest('form') || document;

        return scope.querySelector('[name="' + name + '"]');
    }

    function sync(select) {
        var input = companion(select);

        if (!input) {
            return;
        }

        var creating = select.value === NEW;

        input.hidden = !creating;
        input.required = creating;

        if (creating) {
            input.focus();
        } else {
            input.value = '';
        }
    }

    $(document).on('change', 'select[data-allow-new]', function () {
        sync(this);
    });

    // A form reset (or a modal reopened) must not leave the extra input on.
    $(document).on('reset', 'form', function () {
        var form = this;
        setTimeout(function () {
            form.querySelectorAll('select[data-allow-new]').forEach(sync);
        }, 0);
    });

    document.querySelectorAll('select[data-allow-new]').forEach(sync);
});
