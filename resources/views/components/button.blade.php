{{--
    Theme button.

    Renders the theme's own #general-btn / .btnN markup, so it inherits the
    branding tokens automatically - .btn1 is painted from --brand-button,
    .btn4 from --brand-danger, and so on. Nothing here hardcodes a colour.

    <x-button variant="primary" icon="ri-add-fill">Add Team</x-button>
    <x-button variant="secondary" :href="route('teams.index')">Cancel</x-button>
    <x-button variant="secondary" dismiss>Cancel</x-button>

    @param string      $variant  primary|secondary|success|danger|dark|muted
    @param string|null $icon     RemixIcon class rendered before the label
    @param string|null $href     renders an <a> instead of a <button>
    @param string      $type     button type when not a link
    @param bool        $dismiss  closes the surrounding modal (renders an <a>)
--}}
@props([
    'variant' => 'primary',
    'icon' => null,
    'href' => null,
    'type' => 'submit',
    'dismiss' => false,
])

@php
    $variants = [
        'primary' => 'btn1',
        'secondary' => 'btn2',
        'success' => 'btn3',
        'danger' => 'btn4',
        'dark' => 'btn5',
        'muted' => 'btn6',
    ];

    $class = $variants[$variant] ?? $variants['primary'];
    $tag = ($href || $dismiss) ? 'a' : 'button';
@endphp

<{{ $tag }}
    id="general-btn"
    @if ($tag === 'a') href="{{ $href ?? 'javascript:void(0);' }}" @else type="{{ $type }}" @endif
    @if ($dismiss) data-bs-dismiss="modal" @endif
    {{ $attributes->merge(['class' => $class]) }}
>@if ($icon)<i class="{{ $icon }}"></i>@endif{{ $slot }}</{{ $tag }}>
