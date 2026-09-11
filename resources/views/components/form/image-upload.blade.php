{{--
    Circular avatar picker with live preview.

    <x-form.image-upload name="photo" :src="$photoUrl" />
    <x-form.image-upload name="logo" :src="$logoUrl" id="company-logo" />

    Preview is wired by public/js/modules/image-preview.js via data-image-preview, so
    several of these can sit on one page without clashing.

    @param string      $name    file input name
    @param string|null $src     image shown before a file is chosen
    @param string|null $id      unique id; defaults to the field name
    @param string      $accept  accepted mime types
--}}
@props([
    'name',
    'src' => null,
    'id' => null,
])

@php
    $inputId = $id ?: $name;
    $previewId = $inputId.'-preview';
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
               {{ $attributes }}>

        <label for="{{ $inputId }}" class="profile-photo-edit avatar-xs">
            <span class="avatar-title rounded-circle text-body">
                <i class="ri-camera-fill"></i>
            </span>
        </label>
    </div>
</div>
