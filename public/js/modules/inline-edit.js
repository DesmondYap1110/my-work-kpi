/**
 * Inline add/edit for AJAX datatables.
 *
 * Driven entirely by the table's data-inline-fields / data-inline-routes
 * attributes, which App\Components\Datatables\Datatables emits when a list
 * class implements inlineFields(). Nothing here knows about any particular
 * module - a list opts in from PHP and this picks it up.
 *
 * Behaviour:
 *   - a blank row sits under the last page for adding a record
 *   - the row's edit button swaps its cells for inputs
 *   - save posts to the store/update route and redraws the table
 *
 * Rows carry their raw values under _inline so inputs populate with the
 * underlying value rather than the rendered cell HTML (badges, links, etc.).
 */
App.module('inline-edit', function () {
    'use strict';

    var $ = window.jQuery;

    function parse(value, fallback) {
        try {
            return value ? JSON.parse(value) : fallback;
        } catch (e) {
            return fallback;
        }
    }

    function fieldControl(key, field, value) {
        var required = field.required ? ' required' : '';
        var placeholder = field.placeholder ? ' placeholder="' + field.placeholder + '"' : '';
        var safe = value === null || value === undefined ? '' : String(value);

        if (field.type === 'textarea') {
            return '<textarea class="form-control js-inline-input" data-field="' + key + '"'
                + placeholder + required + ' rows="2"></textarea>';
        }

        if (field.type === 'select') {
            var html = '<select class="form-control js-inline-input" data-field="' + key + '"' + required + '>';
            html += '<option value="">' + (field.placeholder || 'Select') + '</option>';
            $.each(field.options || {}, function (optValue, label) {
                html += '<option value="' + optValue + '">' + label + '</option>';
            });
            return html + '</select>';
        }

        return '<input type="' + (field.type || 'text') + '" class="form-control js-inline-input"'
            + ' data-field="' + key + '"' + placeholder + required + '>';
    }

    /**
     * Buttons reuse the table's own .tb-ac-btn styling so an inline row looks
     * like the rest of the table rather than a bolted-on form.
     *
     * The colour goes on `id`, not `class` - the theme defines these as
     * #tb-ac-btn-N, matching tbButton()/tbLink() in the PHP Datatables class.
     */
    function actionButtons(mode) {
        var confirmId = mode === 'create' ? 'tb-ac-btn-4' : 'tb-ac-btn-6';
        var confirmIcon = mode === 'create' ? 'ri-add-line' : 'ri-check-line';

        return '<button type="button" class="tb-ac-btn js-inline-save" id="' + confirmId + '" title="Save">'
            + '<i class="' + confirmIcon + '"></i></button> '
            + '<button type="button" class="tb-ac-btn js-inline-cancel" id="tb-ac-btn-2" title="Cancel">'
            + '<i class="ri-close-line"></i></button>';
    }

    function setValues($row, values) {
        $row.find('.js-inline-input').each(function () {
            var key = $(this).data('field');
            $(this).val(values && values[key] !== undefined ? values[key] : '');
        });
    }

    function buildRow(columns, fields, mode, values) {
        var cells = columns.map(function (key) {
            if (fields[key]) {
                return '<td>' + fieldControl(key, fields[key], values ? values[key] : '') + '</td>';
            }

            if (key === 'action') {
                return '<td class="text-center">' + actionButtons(mode) + '</td>';
            }

            return '<td class="text-center">-</td>';
        });

        return $('<tr class="js-inline-row"></tr>').html(cells.join(''));
    }

    function collect($row, fields) {
        var data = {};
        var valid = true;

        $row.find('.js-inline-input').each(function () {
            var key = $(this).data('field');
            var value = $(this).val();

            if (fields[key] && fields[key].required && !String(value).trim()) {
                $(this).addClass('is-invalid');
                valid = false;
            } else {
                $(this).removeClass('is-invalid');
            }

            data[key] = value;
        });

        return valid ? data : null;
    }

    function send(url, method, data, onDone, onError) {
        var body = new FormData();
        var token = document.querySelector('meta[name="csrf-token"]');

        $.each(data, function (key, value) {
            body.append(key, value === null ? '' : value);
        });

        if (method !== 'POST') {
            body.append('_method', method);
        }

        fetch(url, {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    return { ok: response.ok, payload: payload };
                });
            })
            .then(function (result) {
                if (result.ok) {
                    onDone();
                    return;
                }

                // Laravel returns 422 with {errors: {field: [messages]}}.
                var errors = result.payload && result.payload.errors;
                var first = errors ? errors[Object.keys(errors)[0]][0] : 'Could not save. Please try again.';
                onError(first);
            })
            .catch(function () {
                onError('Could not save. Please try again.');
            });
    }

    function showError($row, message) {
        $row.find('.js-inline-error').remove();
        $row.find('td').first().append(
            '<span class="js-inline-error unique-check-feedback">' + message + '</span>'
        );
    }

    function init($table, table) {
        var fields = parse($table.attr('data-inline-fields'), null);

        if (!fields) {
            return;
        }

        var routes = parse($table.attr('data-inline-routes'), {});
        var columns = String($table.data('cols')).split('|');

        function appendCreateRow() {
            if (!routes.store) {
                return;
            }

            var $row = buildRow(columns, fields, 'create', {});
            $row.addClass('js-inline-create');
            $table.find('tbody').append($row);
        }

        // The create row is re-added after every draw, because DataTables
        // rebuilds tbody on each redraw (page, sort, filter).
        table.on('draw', appendCreateRow);
        appendCreateRow();

        $table.on('click', '.js-inline-save', function () {
            var $row = $(this).closest('tr');
            var data = collect($row, fields);

            if (!data) {
                return;
            }

            var isCreate = $row.hasClass('js-inline-create');
            var url = isCreate ? routes.store : routes.update.replace('__id__', $row.data('id'));

            $row.find('.js-inline-error').remove();
            $(this).prop('disabled', true);

            send(url, isCreate ? 'POST' : 'PUT', data,
                function () { table.ajax.reload(null, false); },
                function (message) {
                    showError($row, message);
                    $row.find('.js-inline-save').prop('disabled', false);
                });
        });

        $table.on('click', '.js-inline-cancel', function () {
            var $row = $(this).closest('tr');

            if ($row.hasClass('js-inline-create')) {
                setValues($row, {});
                $row.find('.js-inline-error').remove();
                $row.find('.is-invalid').removeClass('is-invalid');
                return;
            }

            table.ajax.reload(null, false);
        });

        // Turn an existing row into inputs, keeping the row in place.
        $table.on('click', '.js-inline-editable', function () {
            var $trigger = $(this);
            var $row = $trigger.closest('tr');
            var rowData = table.row($row).data();
            var values = (rowData && rowData._inline) || {};

            var $editRow = buildRow(columns, fields, 'edit', values);
            $editRow.attr('data-id', $trigger.data('id'));
            $row.replaceWith($editRow);
            setValues($editRow, values);
            $editRow.find('.js-inline-input').first().trigger('focus');
        });
    }

    // Hand the initialiser to the datatables module, which owns table setup.
    App.datatables.inlineEdit = init;
});
