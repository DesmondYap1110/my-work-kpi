/**
 * Fills in New Appraisal's Next Assessment date from the member's review cycle.
 *
 *   <select name="staff_id" data-next-date-source>
 *     <option value="3" data-cycle="1w" data-cycle-label="1 week">…</option>
 *   <input name="period_to">
 *   <input name="next_assessment_date" data-next-date>
 *   <span data-next-date-note></span>
 *
 * Next Assessment = Review Period To + the cycle, the same rule as
 * App\Enums\AppraisalCycle::after() - months never spill over, so a month
 * after 31 January is 28/29 February. A Manual member leaves it blank.
 *
 * Once the appraiser types their own date it is left alone, until they pick
 * another member.
 */
App.module('appraisal-next-date', function () {
    'use strict';

    var MONTHS = { '1m': 1, '2m': 2, '3m': 3, '4m': 4, '6m': 6, '1y': 12 };

    function pad(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function after(iso, cycle) {
        var parts = (iso || '').split('-').map(Number);

        if (parts.length !== 3 || !parts[0]) {
            return '';
        }

        var date = new Date(Date.UTC(parts[0], parts[1] - 1, parts[2]));

        if (cycle === '1w') {
            date.setUTCDate(date.getUTCDate() + 7);
        } else if (MONTHS[cycle]) {
            var day = date.getUTCDate();
            date.setUTCDate(1);
            date.setUTCMonth(date.getUTCMonth() + MONTHS[cycle]);
            var lastDay = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + 1, 0)).getUTCDate();
            date.setUTCDate(Math.min(day, lastDay));
        } else {
            return '';
        }

        return date.getUTCFullYear() + '-' + pad(date.getUTCMonth() + 1) + '-' + pad(date.getUTCDate());
    }

    function fmt(iso) {
        var d = iso.split('-');
        var names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return d[2] + ' ' + names[Number(d[1]) - 1] + ' ' + d[0];
    }

    document.querySelectorAll('select[data-next-date-source]').forEach(function (member) {
        var form = member.form;
        var periodTo = form && form.querySelector('[name="period_to"]');
        var next = form && form.querySelector('[data-next-date]');
        var note = form && form.querySelector('[data-next-date-note]');
        // Optional "Review Every" picker: follows the member, and can be changed
        // to put them on a schedule from here.
        var cyclePicker = form && form.querySelector('select[data-next-date-cycle]');

        if (!periodTo || !next) {
            return;
        }

        // Typed by hand: keep it. A value already there on load (a failed
        // submit coming back) counts as typed.
        var touched = next.value !== '';

        function fill() {
            var option = member.options[member.selectedIndex];
            var cycle = option ? option.getAttribute('data-cycle') : '';
            var label = option ? option.getAttribute('data-cycle-label') : '';

            if (cyclePicker) {
                cycle = cyclePicker.value;
                label = cyclePicker.options[cyclePicker.selectedIndex].text.toLowerCase();
            }

            if (!member.value) {
                if (note) note.textContent = 'Pick a member to fill this in from their review cycle.';
                return;
            }

            if (!cycle || cycle === 'manual') {
                if (!touched) next.value = '';
                if (note) note.textContent = cyclePicker
                    ? 'No schedule. Pick how often under Review Every, or set a date.'
                    : 'This member is reviewed manually - set a date if you want one.';
                return;
            }

            var date = after(periodTo.value, cycle);

            if (!touched) {
                next.value = date;
            }

            if (note) {
                note.textContent = touched && next.value !== date
                    ? 'Set by hand. Their review cycle (every ' + label + ') would give ' + (date ? fmt(date) : '-') + '.'
                    : 'Review Period To + every ' + label + ', from the member\'s review cycle.';
            }
        }

        member.addEventListener('change', function () {
            touched = false;
            // A different member brings their own cycle with them.
            var option = member.options[member.selectedIndex];
            if (cyclePicker && option && option.getAttribute('data-cycle')) {
                cyclePicker.value = option.getAttribute('data-cycle');
            }
            fill();
        });
        if (cyclePicker) {
            cyclePicker.addEventListener('change', function () {
                touched = false;
                fill();
            });
        }
        periodTo.addEventListener('change', fill);
        next.addEventListener('input', function () {
            touched = next.value !== '';
            fill();
        });

        // On load, a picker with no old() value follows the chosen member.
        var initial = member.options[member.selectedIndex];
        if (cyclePicker && initial && initial.getAttribute('data-cycle') && !cyclePicker.querySelector('option[selected]')) {
            cyclePicker.value = initial.getAttribute('data-cycle');
        }

        fill();
    });
});
