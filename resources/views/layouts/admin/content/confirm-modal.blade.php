{{--
    The confirmation dialog, rendered once per page.

    Replaces the browser's confirm() box, which can't be styled and names the
    site rather than the thing being deleted. Driven entirely from JS - see
    public/js/modules/confirm.js - so no page has to include or wire anything:
    a form marked js-confirm-delete (or carrying data-confirm) gets this.
--}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirm-modal-title">
    <div class="modal-dialog modal-dialog-centered" id="md-dialog">
        <div class="modal-content general-box" id="md-content">
            <div class="modal-header">
                <p id="modal-title"><span id="confirm-modal-title">Please confirm</span></p>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="modal-div">
                <p class="confirm-modal-message mb-0" id="confirm-modal-message">
                    Are you sure you want to delete this record?
                </p>
            </div>

            <div id="modal-btn-div">
                <x-button variant="secondary" icon="ri-close-fill" dismiss>Cancel</x-button>
                {{-- A class, not an id: the theme's button markup already
                     carries id="general-btn". --}}
                <x-button variant="danger" icon="ri-delete-bin-6-line" type="button"
                          class="js-confirm-accept">Delete</x-button>
            </div>
        </div>
    </div>
</div>
