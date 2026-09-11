/**
 * Live "already taken" check for any field that must be unique.
 *
 * Mark the input with the endpoint to ask, and optionally a record id the
 * check should ignore (so an edit form doesn't flag the record's own value):
 *
 *   <input name="email"
 *          data-unique-check="/staff/check-unique"
 *          data-unique-check-ignore="12"
 *          data-unique-check-message="This email is already registered.">
 *
 * The endpoint receives {field, value, ignore} by POST and must answer
 * {"taken": true|false}. `field` defaults to the input's name; override it
 * with data-unique-check-field when the column differs from the input name.
 *
 * Advisory only. Server-side validation still runs on submit - this just
 * saves the user filling in the rest of the form first.
 */
App.module('unique-check', function () {
    'use strict';

    var DEBOUNCE_MS = 400;

    function feedbackFor(input) {
        var existing = input.parentNode.querySelector('.js-unique-check-feedback');

        if (existing) {
            return existing;
        }

        // A <span>, matching how the theme already marks text red
        // (label span for the required asterisk).
        var el = document.createElement('span');
        el.className = 'js-unique-check-feedback unique-check-feedback';
        el.hidden = true;
        input.parentNode.appendChild(el);

        return el;
    }

    function submitButtons(input) {
        var form = input.closest('form');

        return form ? form.querySelectorAll('button[type="submit"]') : [];
    }

    function bind(input) {
        var endpoint = input.getAttribute('data-unique-check');
        var message = input.getAttribute('data-unique-check-message') || 'This value is already taken.';
        var feedback = feedbackFor(input);
        var timer = null;
        var token = document.querySelector('meta[name="csrf-token"]');

        function setTaken(taken) {
            feedback.hidden = !taken;
            feedback.textContent = taken ? message : '';
            input.classList.toggle('is-invalid', taken);

            // Blocking submit is what stops a known-bad value reaching a
            // server round-trip; validation still catches it if JS is off.
            submitButtons(input).forEach(function (button) {
                button.disabled = taken;
            });
        }

        function check() {
            var value = input.value.trim();

            if (!value || !input.checkValidity()) {
                setTaken(false);
                return;
            }

            var body = new FormData();
            body.append('field', input.getAttribute('data-unique-check-field') || input.name);
            body.append('value', value);

            var ignore = input.getAttribute('data-unique-check-ignore');
            if (ignore) {
                body.append('ignore', ignore);
            }

            fetch(endpoint, {
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
                    return response.ok ? response.json() : null;
                })
                .then(function (data) {
                    // A failed lookup must not block submission - the server
                    // validates anyway, so fall back to letting them through.
                    setTaken(Boolean(data && data.taken));
                })
                .catch(function () {
                    setTaken(false);
                });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(check, DEBOUNCE_MS);
        });

        input.addEventListener('blur', function () {
            clearTimeout(timer);
            check();
        });
    }

    document.querySelectorAll('[data-unique-check]').forEach(bind);
});
