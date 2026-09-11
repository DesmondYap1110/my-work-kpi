/**
 * Live preview for image file inputs.
 *
 * Mark up any number of inputs on a page with data-image-preview, pointing at
 * the <img> to update:
 *
 *   <input type="file" data-image-preview="#avatar">
 *   <img id="avatar" src="...">
 *
 * Scoped to the input's form when the selector matches several elements, so
 * repeated widgets (e.g. a row per gallery item) each update their own image.
 *
 * The theme ships an equivalent binding inside
 * assets/js/pages/profile-setting.init.js, but that file is mostly unrelated
 * demo code and is hardcoded to one pair of selectors.
 */
App.module('image-preview', function () {
    'use strict';

    function resolveTarget(input) {
        var selector = input.getAttribute('data-image-preview');

        if (!selector) {
            return null;
        }

        // Prefer a match inside the same form so multiple widgets on one page
        // don't all write to the first image in the document.
        var scope = input.closest('form') || document;

        return scope.querySelector(selector) || document.querySelector(selector);
    }

    function bind(input) {
        var target = resolveTarget(input);

        if (!target) {
            return;
        }

        // Lets a cancelled picker put the original image back, so the preview
        // never shows a file that won't be submitted.
        var originalSrc = target.getAttribute('src');

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (!file || !file.type.startsWith('image/')) {
                if (file) {
                    input.value = '';
                }

                target.src = originalSrc;
                return;
            }

            var reader = new FileReader();

            reader.addEventListener('load', function () {
                target.src = reader.result;
            });

            reader.readAsDataURL(file);
        });
    }

    document.querySelectorAll('input[type="file"][data-image-preview]').forEach(bind);
});
