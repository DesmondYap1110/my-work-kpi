/**
 * Offers only the project tags that fit a task's assignee.
 *
 *   <select name="assignee_id"><option value="3" data-position="2">…</select>
 *   <select name="tag_id" data-tag-position>
 *     <option value="7" data-positions="">…</option>     <- blank: every position
 *     <option value="8" data-positions="2,4">…</option>
 *
 * Tags for another position are hidden and disabled; if the chosen tag stops
 * fitting when the assignee changes, it is cleared rather than silently kept.
 * An unassigned task shows every tag. The server applies the same rule - see
 * App\Http\Requests\Concerns\ChecksTagFitsAssignee.
 */
App.module('tag-position', function () {
    'use strict';

    function apply(tagSelect) {
        var form = tagSelect.form;
        var assignee = form && form.querySelector('select[name="assignee_id"]');

        if (!assignee) {
            return;
        }

        var chosen = assignee.options[assignee.selectedIndex];
        var position = chosen ? chosen.getAttribute('data-position') || '' : '';

        Array.prototype.forEach.call(tagSelect.options, function (option) {
            if (!option.value) {
                return;
            }

            var allowed = (option.getAttribute('data-positions') || '').split(',').filter(Boolean);
            var fits = position === '' || allowed.length === 0 || allowed.indexOf(position) !== -1;

            option.hidden = !fits;
            option.disabled = !fits;
        });

        if (tagSelect.selectedOptions[0] && tagSelect.selectedOptions[0].disabled) {
            tagSelect.value = '';
        }
    }

    document.querySelectorAll('select[data-tag-position]').forEach(apply);

    document.addEventListener('change', function (event) {
        if (event.target.matches && event.target.matches('select[name="assignee_id"]') && event.target.form) {
            var tagSelect = event.target.form.querySelector('select[data-tag-position]');

            if (tagSelect) {
                apply(tagSelect);
            }
        }
    });
});
