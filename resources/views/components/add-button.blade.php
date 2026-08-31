{{--
    The right-aligned "Add X" bar that sits above a list table.

    <x-add-button :href="route('staff.create')">Add Member</x-add-button>
    <x-add-button modal="addTeamModal">Add Team</x-add-button>

    @param string|null $href   link target
    @param string|null $modal  id of a modal to open instead of navigating
    @param string      $icon
--}}
@props(['href' => null, 'modal' => null, 'icon' => 'ri-add-fill'])

@php
    // Blade cannot parse @if inside a component tag's attribute list, so the
    // modal trigger attributes are built here and spread in one go.
    $triggerAttributes = $modal
        ? ['data-bs-toggle' => 'modal', 'data-bs-target' => '#'.$modal]
        : [];
@endphp

<div id="add-btn-div">
    <x-button variant="primary" :icon="$icon" :href="$href" {{ $attributes->merge($triggerAttributes) }}>
        {{ $slot }}
    </x-button>
</div>
