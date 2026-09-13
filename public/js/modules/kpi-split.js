/**
 * The Project KPI split on a position's KPI page - see
 * resources/views/kpi/objectives/_project-kpi.blade.php.
 *
 * The KPI score's 100 points are split between projects and KPI objectives.
 * Dragging the coloured bar (a styled range input) sets the split; the numbers,
 * the read-only points on each card and the worked example follow it.
 */
App.module('kpi-split', function () {
    'use strict';

    function fmt(n) {
        return String(Math.round(n * 10) / 10);
    }

    document.querySelectorAll('[data-kpi-split]').forEach(function (form) {
        var range = form.querySelector('[data-split-range]');
        var weight = form.querySelector('[data-split-weight]');
        var target = form.querySelector('[data-split-target]');
        var inputs = form.querySelectorAll('[data-split-input]');

        function setAll(selector, text) {
            form.querySelectorAll(selector).forEach(function (el) { el.textContent = text; });
        }

        function paint() {
            var project = parseInt(range.value, 10) || 0;
            var objectives = 100 - project;
            var marks = parseFloat(target.value);

            setAll('[data-split-project]', project);
            setAll('[data-split-objectives]', objectives);

            inputs.forEach(function (input) {
                input.value = input.getAttribute('data-split-input') === 'project' ? project : objectives;
            });

            // The bar is the range input; its two colours meet at the split.
            range.style.setProperty('--split', project + '%');

            setAll('[data-split-example-project]', fmt(project * 0.8));
            setAll('[data-split-example-objectives]', fmt(objectives * 0.8));
            setAll('[data-split-example-project-text]', marks > 0
                ? 'Earns ' + fmt(marks * 0.8) + ' of ' + fmt(marks) + ' marks'
                : 'Completes 80% of the task marks given');

            weight.value = project;
        }

        range.addEventListener('input', paint);

        target.addEventListener('input', paint);

        paint();
    });
});
