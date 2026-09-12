/**
 * Editable list of allowed mark values for a KPI objective.
 *
 * The set of marks is not fixed - an objective can allow any values its owner
 * decides on, so this renders one numeric input per mark with add/remove
 * controls rather than a hardcoded row of checkboxes.
 *
 *   <div class="js-mark-list" data-name="allowed_marks"></div>
 *
 * setMarks()/getMarks() are exposed on App.markList so other scripts (the
 * edit-modal wiring) can populate and read a list.
 */
App.module('mark-list', function () {
    'use strict';

    var $ = window.jQuery;

    function rowHtml(name, value) {
        return '<div class="mark-row js-mark-row">'
            + '<input type="number" step="1" class="form-control js-mark-value"'
            + ' name="' + name + '[]" value="' + (value === '' ? '' : value) + '" placeholder="e.g. 2">'
            + '<button type="button" class="tb-ac-btn js-mark-remove" id="tb-ac-btn-2" title="Remove">'
            + '<i class="ri-close-line"></i></button>'
            + '</div>';
    }

    function addRow($list, value) {
        $list.find('.js-mark-rows').append(rowHtml($list.data('name'), value === undefined ? '' : value));
    }

    function setMarks($list, marks) {
        $list.find('.js-mark-rows').empty();

        if (!marks || !marks.length) {
            addRow($list);
            return;
        }

        marks.forEach(function (mark) { addRow($list, mark); });
    }

    function getMarks($list) {
        return $list.find('.js-mark-value')
            .map(function () { return $(this).val(); })
            .get()
            .filter(function (v) { return String(v).trim() !== ''; })
            .map(Number);
    }

    function build($list) {
        $list.html(
            '<div class="js-mark-rows"></div>'
            + '<button type="button" class="js-mark-add general-btn btn2">'
            + '<i class="ri-add-line"></i>Add Mark</button>'
        );

        var initial = $list.data('marks');
        setMarks($list, Array.isArray(initial) ? initial : []);
    }

    // Delegated, so rows added later are covered too.
    $(document).on('click', '.js-mark-add', function () {
        addRow($(this).closest('.js-mark-list'));
    });

    $(document).on('click', '.js-mark-remove', function () {
        var $list = $(this).closest('.js-mark-list');
        $(this).closest('.js-mark-row').remove();

        // Never leave the list empty - there would be no way to add one back
        // other than the Add button, and an empty list reads as broken.
        if (!$list.find('.js-mark-row').length) {
            addRow($list);
        }
    });

    $('.js-mark-list').each(function () { build($(this)); });

    App.markList = {
        set: function (selector, marks) { setMarks($(selector), marks); },
        get: function (selector) { return getMarks($(selector)); },
    };
});
