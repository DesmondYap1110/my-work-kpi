{{--
    The button that removes one row of the appraisal form's setup - a part, a
    mark or a band.

    Only the button. The form it submits is rendered after the big save form
    closes (see appraisal-form/_row-deletes.blade.php) and is reached through
    the `form` attribute, because these buttons sit in table cells inside that
    form and a form cannot be nested in another.

    @param string $id  matches the form rendered for this row
--}}
@props(['id'])

<button type="submit" form="{{ $id }}" class="tb-ac-btn" id="tb-ac-btn-2" title="Remove">
    <i class="ri-delete-bin-6-line"></i>
</button>
