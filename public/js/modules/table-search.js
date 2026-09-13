/**
 * Filters a plain (non-DataTables) table as the user types.
 *
 *   <input type="search" data-table-search="#my-table">
 *   <table id="my-table">
 *     <tr data-search-level="1">Category</tr>      <- heading rows (optional)
 *     <tr data-search-level="2">Objective</tr>
 *     <tr>Item</tr>                                <- everything else is a row to match
 *     <tr data-search-empty hidden>No match</tr>   <- shown when nothing matches
 *
 * An item matches on its own text or on the text of the headings above it, so
 * searching a category name shows everything under it. A heading stays visible
 * while anything beneath it does, so a match is never shown without its context.
 */
App.module('table-search', function () {
    'use strict';

    function normalise(text) {
        return (text || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }

    function filter(input) {
        var table = document.querySelector(input.getAttribute('data-table-search'));

        if (!table || !table.tBodies[0]) {
            return;
        }

        var terms = normalise(input.value).split(' ').filter(Boolean);
        var rows = Array.prototype.slice.call(table.tBodies[0].rows);
        var empty = null;
        var headings = [];      // current heading row at each level
        var anyShown = false;

        rows.forEach(function (row) {
            if (row.hasAttribute('data-search-empty')) {
                empty = row;
                return;
            }

            var level = parseInt(row.getAttribute('data-search-level'), 10);

            if (level) {
                headings = headings.slice(0, level - 1);
                headings[level - 1] = row;
                row.hidden = terms.length > 0;   // revealed below if a child matches
                return;
            }

            var haystack = normalise(
                headings.map(function (h) { return h ? h.textContent : ''; }).join(' ') + ' ' + row.textContent
            );
            var match = terms.every(function (term) { return haystack.indexOf(term) !== -1; });

            row.hidden = !match;

            if (match) {
                anyShown = true;
                headings.forEach(function (h) { if (h) { h.hidden = false; } });
            }
        });

        if (empty) {
            empty.hidden = anyShown || terms.length === 0;
        }
    }

    document.addEventListener('input', function (event) {
        if (event.target.matches && event.target.matches('[data-table-search]')) {
            filter(event.target);
        }
    });

    // A search kept by the browser on Back must still be applied.
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
        if (input.value) {
            filter(input);
        }
    });
});
