{{--
    Circular avatar picker with live preview.

    <x-form.image-upload name="photo" :src="$photoUrl" />
    <x-form.image-upload name="logo" :src="$logoUrl" id="company-logo" />

    Pass :default-src to offer a remove button. It appears only when the
    current image differs from the default, and posts remove_{name}=1 so the
    controller knows to clear the stored file:

    <x-form.image-upload name="photo" :src="$photoUrl" :default-src="$defaultPhotoUrl" />

    Preview and removal are wired by public/js/modules/image-preview.js via
    data-image-preview, so several of these can sit on one page without
    clashing.

    @param string      $name        file input name
    @param string|null $src         image shown before a file is chosen
    @param string|null $defaultSrc  placeholder image; enables removal
    @param string|null $id          unique id; defaults to the field name
    @param string      $accept      accepted mime types
--}}
@props([
    'name',
    'src' => null,
    'defaultSrc' => null,
    'id' => null,
    'accept' => 'image/png,image/jpeg',
])

@php
    $inputId = $id ?: $name;
    $previewId = $inputId.'-preview';
    // Nothing to remove when the current image is already the placeholder.
    $canRemove = $defaultSrc && $src && $src !== $defaultSrc;
@endphp

<div id="profile-img-div" class="profile-user">
    <img src="{{ $src }}" alt="{{ $name }}" title="{{ $name }}"
         class="user-profile-image" id="{{ $previewId }}">

    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
        <input type="file"
               class="profile-img-file-input"
               id="{{ $inputId }}"
               name="{{ $name }}"
               accept="{{ $accept }}"
               data-image-preview="#{{ $previewId }}"
               @if ($defaultSrc)
                   data-image-default="{{ $defaultSrc }}"
                   data-image-remove-flag="remove_{{ $name }}"
               @endif
               {{ $attributes }}>

        <label for="{{ $inputId }}" class="profile-photo-edit avatar-xs">
            <span class="avatar-title rounded-circle text-body">
                <i class="ri-camera-fill"></i>
            </span>
        </label>
    </div>

    @if ($defaultSrc)
        <input type="hidden" name="remove_{{ $name }}" value="0">

        {{-- Mirrors the camera button's markup (avatar-xs + avatar-title) so
             the two match in size and shape. aria-label rather than title:
             a title would pop a native tooltip over the avatar on hover. --}}
        <button type="button"
                class="js-image-remove image-remove-btn avatar-xs p-0"
                data-for="{{ $inputId }}"
                aria-label="Remove photo"
                @unless ($canRemove) hidden @endunless>
            <span class="avatar-title rounded-circle">
                <i class="ri-close-line"></i>
            </span>
        </button>
    @endif
</div>
