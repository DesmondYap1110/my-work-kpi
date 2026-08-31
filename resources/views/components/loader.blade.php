{{--
    Full-screen loading overlay, following the MCS loading-panel pattern.

    public/js/loader.js shows it automatically when a page navigation starts
    (link click or form submit) and hides it again on bfcache restore, so
    pages don't sit blank while the next one loads. Drive it manually with
    window.appLoader.show() / .hide() when needed.
--}}
<div class="loading-panel" id="loader_master" aria-hidden="true">
    <div class="loading-content">
        <div class="loader"></div>
    </div>
</div>
