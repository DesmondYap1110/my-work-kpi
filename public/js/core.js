/**
 * Tiny module registry - the entry point every other script registers with.
 *
 * There is no bundler in this template (see README), so scripts are plain
 * <script> tags. This gives them one predictable shape and one boot point
 * instead of each file inventing its own DOMContentLoaded wrapper:
 *
 *   App.module('thing', function () { ... });   // runs once, on DOM ready
 *
 * Modules registered after boot run immediately, so load order doesn't
 * matter and a late-injected script still initialises.
 */
window.App = (function () {
    'use strict';

    var booted = false;
    var modules = [];

    function run(module) {
        try {
            module.fn();
        } catch (error) {
            // One broken module must not stop the rest from initialising.
            // This is exactly how the theme's own app.js took down the
            // sidebar toggle: a null reference early in one IIFE aborted
            // everything after it.
            console.error('[App] module "' + module.name + '" failed:', error);
        }
    }

    function boot() {
        booted = true;
        modules.forEach(run);
    }

    document.addEventListener('DOMContentLoaded', boot);

    return {
        /**
         * Registers a module to run on DOM ready.
         */
        module: function (name, fn) {
            var module = { name: name, fn: fn };

            modules.push(module);

            if (booted) {
                run(module);
            }
        },

        /**
         * Shared namespace for modules that expose helpers to each other
         * (e.g. App.datatables.populateEditModal).
         */
        datatables: {},
    };
})();
