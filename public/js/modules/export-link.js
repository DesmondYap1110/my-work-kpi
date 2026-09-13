/**
 * An export link that carries a datatable's current filters.
 *
 *   <a href="/appraisals/export" data-export-filters="AppraisalList">Export CSV</a>
 *
 * On click, the values in that list's filter form (.js-datatable-filter with the
 * same data-for) are added to the link's query string, so the download matches
 * the rows being looked at - including filters typed but not yet applied.
 */
App.module('export-link', function () {
    'use strict';

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[data-export-filters]');

        if (!link) {
            return;
        }

        var form = document.querySelector('.js-datatable-filter[data-for="' + link.getAttribute('data-export-filters') + '"]');

        if (!form) {
            return;
        }

        event.preventDefault();

        var url = new URL(link.href, window.location.href);

        new FormData(form).forEach(function (value, key) {
            if (value !== '') {
                url.searchParams.set(key, value);
            }
        });

        window.location.href = url.toString();
    });
});
