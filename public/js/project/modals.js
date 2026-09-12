/**
 * Modal wiring for THIS project's screens.
 *
 * Project code, not template code: every selector below names a specific
 * module (KPI tree, pending approvals, phase attachments). A new project
 * should replace this file wholesale - public/js/modules/ stays.
 */
App.module('project-modals', function () {
    'use strict';

    var $ = window.jQuery;


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
