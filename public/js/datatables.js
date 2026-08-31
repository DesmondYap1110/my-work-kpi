/**
 * Generic initializer for every AJAX-driven list table rendered by
 * show_datatables() (see app/Components/Datatables). One <table
 * class="ajax-datatable"> per page, columns/endpoint read straight off its
 * data-* attributes so nothing has to be duplicated here per module.
 */
(function ($) {
    'use strict';

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

    $(function () {
        $('.ajax-datatable').each(function () {
            initTable($(this));
        });

        // Destructive actions are injected as plain HTML by the list
        // classes, so the confirm prompt has to be delegated rather than
        // bound to elements that don't exist until the AJAX draw happens.
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

        $(document).on('click', '.js-edit-position', function () {
            populateEditModal('#editPositionModal', { position_name: 'name', job_scope: 'scope' }, $(this));
        });

        $(document).on('click', '.js-edit-team', function () {
            populateEditModal('#editTeamModal', { team_name: 'name' }, $(this));
        });

        $(document).on('click', '.js-edit-kpi', function () {
            populateEditModal('#editKpiModal', { kpi_title: 'title' }, $(this));
        });

        $(document).on('click', '.js-edit-objective', function () {
            var $btn = $(this);
            var $modal = $('#editObjectiveModal');

            $modal.find('[name=obj_type]').val($btn.data('type'));
            $modal.find('[name=objmk_2]').prop('checked', $btn.data('mk2') == 1);
            $modal.find('[name=objmk_1]').prop('checked', $btn.data('mk1') == 1);
            $modal.find('[name=objmk_0]').prop('checked', $btn.data('mk0') == 1);
            $modal.find('[name=objmk_n1]').prop('checked', $btn.data('mkn1') == 1);
            $modal.find('[name=objmk_n2]').prop('checked', $btn.data('mkn2') == 1);

            var url = $modal.data('url-template').replace('__id__', $btn.data('id'));
            $modal.find('form').attr('action', url);

            new bootstrap.Modal($modal[0]).show();
        });

        $(document).on('click', '.js-reject-pending', function () {
            var $btn = $(this);
            var marks = $btn.data('marks') || [];
            var $modal = $('#rejectPendingModal');
            var $select = $modal.find('[name=mark]');

            $select.empty();
            if (!marks.length) {
                $select.append('<option value="">No alternate mark available</option>');
            } else {
                marks.forEach(function (m) {
                    var label = m > 0 ? '+' + m : m;
                    $select.append('<option value="' + m + '">' + label + '</option>');
                });
            }

            var url = $modal.data('url-template').replace('__id__', $btn.data('id'));
            $modal.find('form').attr('action', url);

            new bootstrap.Modal($modal[0]).show();
        });

        $(document).on('click', '.js-show-attachments', function () {
            var $modal = $('#attachmentsModal');
            var url = $modal.data('url-template').replace('__id__', $(this).data('id'));

            $modal.find('#modal-div').html('<p class="text-muted mb-0">Loading...</p>');
            new bootstrap.Modal($modal[0]).show();

            $.get(url, function (res) {
                if (!res.files.length) {
                    $modal.find('#modal-div').html('<p class="text-muted mb-0">No attachments uploaded yet.</p>');
                    return;
                }

                var html = '<ul class="list-group">';
                res.files.forEach(function (file) {
                    html += '<li class="list-group-item d-flex justify-content-between">'
                        + '<a href="' + file.url + '" target="_blank">' + file.name + '</a>'
                        + '<span class="text-muted">' + file.uploaded_at + '</span></li>';
                });
                html += '</ul>';

                $modal.find('#modal-div').html(html);
            });
        });
    });
})(jQuery);
