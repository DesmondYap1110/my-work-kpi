/**
 * Shows the first few rows of a short plain table and folds the rest behind a
 * "Show all" button.
 *
 *   <tbody data-show-more="5" data-show-more-label="tags"> ... </tbody>
 *
 * Nothing happens when the table has no more rows than the limit. Rows carrying
 * data-search-empty (see table-search.js) are left alone.
 */
App.module('show-more', function () {
    'use strict';

    document.querySelectorAll('tbody[data-show-more]').forEach(function (tbody) {
        var limit = parseInt(tbody.getAttribute('data-show-more'), 10) || 5;
        var label = tbody.getAttribute('data-show-more-label') || 'rows';
        var rows = Array.prototype.filter.call(tbody.rows, function (row) {
            return !row.hasAttribute('data-search-empty');
        });

        if (rows.length <= limit) {
            return;
        }

        var expanded = false;
        var wrap = document.createElement('div');
        var button = document.createElement('button');

        wrap.className = 'show-more-wrap';
        button.type = 'button';
        button.id = 'general-btn';
        button.className = 'btn2 show-more-btn';
        wrap.appendChild(button);

        var table = tbody.closest('table');
        var anchor = table.closest('#table-div') || table;
        anchor.parentNode.insertBefore(wrap, anchor.nextSibling);

        function paint() {
            rows.forEach(function (row, i) {
                row.hidden = !expanded && i >= limit;
            });
            button.innerHTML = expanded
                ? '<i class="ri-arrow-up-s-line"></i>Show less'
                : '<i class="ri-arrow-down-s-line"></i>Show all ' + rows.length + ' ' + label;
            button.setAttribute('aria-expanded', String(expanded));
        }

        button.addEventListener('click', function () {
            expanded = !expanded;
            paint();
        });

        paint();
    });
});
