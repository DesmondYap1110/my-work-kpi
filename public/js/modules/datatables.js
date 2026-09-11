/**
 * Generic initialiser for every AJAX-driven list table rendered by
 * show_datatables() (see app/Components/Datatables). One
 * <table class="ajax-datatable"> per page, with columns and endpoint read
 * straight off its data-* attributes, so nothing is duplicated per module.
 *
 * Template code - reusable as-is. Anything tied to particular screens
 * (edit-modal field mappings and the like) belongs in public/js/project/.
 */
App.module('datatables', function () {
    'use strict';

    var $ = window.jQuery;

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    });

    function initTable($table) {
        var listClass = $table.data('class');
        var columnKeys = String($table.data('cols')).split('|');
        var centeredKeys = String($table.data('centered') || '').split('|');
        var pageLength = parseInt($table.data('page-length'), 10) || 10;
        var extraParams = $table.data('extra') || {};
        var $filterForm = $('.js-datatable-filter');

        var columns = columnKeys.map(function (key) {
            return {
                data: key,
                orderable: key !== 'action',
                searchable: false,
                className: centeredKeys.indexOf(key) !== -1 ? 'text-center' : '',
            };
        });

        // The shared loading overlay covers every AJAX draw (filter, sort,
        // paging, reload), so DataTables' own "Processing..." box is left off
        // rather than showing two indicators at once.
        $table.on('preXhr.dt', function () {
            if (window.appLoader) {
                window.appLoader.show();
            }
        });
        $table.on('xhr.dt', function () {
            if (window.appLoader) {
                window.appLoader.hide();
            }
        });

        var table = $table.DataTable({
            processing: false,
            serverSide: true,
            searching: false,
            pageLength: pageLength,
            ajax: {
                url: window.datatablesEndpoint,
                type: 'POST',
                data: function (d) {
                    d.class = listClass;
                    $.extend(d, extraParams);
                    if ($filterForm.length) {
                        $.each($filterForm.serializeArray(), function (_, field) {
                            d[field.name] = field.value;
                        });
                    }
                },
            },
            columns: columns,
            language: {
                emptyTable: 'No Record',
                zeroRecords: 'No Record',
            },
        });

        if ($filterForm.length) {
            $filterForm.on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });
            $filterForm.on('reset', function () {
                // Native reset happens after this handler runs, so defer
                // the reload until the fields have actually cleared.
                setTimeout(function () { table.ajax.reload(); }, 0);
            });
        }

        // Inline add/edit is opt-in per list class (see inlineFields() in
        // App\Components\Datatables\Datatables) and lives in its own module,
        // so tables without it pull in nothing extra.
        if (App.datatables.inlineEdit) {
            App.datatables.inlineEdit($table, table);
        }

        return table;
    }

    function populateEditModal(modalSelector, fieldMap, $trigger) {
        var $modal = $(modalSelector);

        $.each(fieldMap, function (field, attr) {
            $modal.find('[name="' + field + '"]').val($trigger.data(attr));
        });

        var url = $modal.data('url-template').replace('__id__', $trigger.data('id'));
        $modal.find('form').attr('action', url);

        new bootstrap.Modal($modal[0]).show();
    }

    // Exposed so project scripts can reuse the populate-and-show behaviour.
    App.datatables.populateEditModal = populateEditModal;

    $('.ajax-datatable').each(function () {
        initTable($(this));
    });

    // Destructive actions are injected as plain HTML by the list classes, so
    // the confirm prompt has to be delegated rather than bound to elements
    // that don't exist until the AJAX draw happens.
    $(document).on('submit', '.js-confirm-delete', function (e) {
        if (!confirm('Are you sure you want to delete this record?')) {
            e.preventDefault();
        }
    });

    $(document).on('submit', '.js-confirm-cancel', function (e) {
        if (!confirm('Are you sure you want to cancel this project?')) {
            e.preventDefault();
        }
    });
});
