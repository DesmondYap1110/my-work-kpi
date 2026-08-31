{{--
    A collapsible sidebar group. Children go in the slot as
    <x-sidebar.ui.dropdown-list> entries.

    $id     - collapse target id (e.g. "sb-project")
    $icon   - RemixIcon class
    $label  - visible text
    $active - route pattern(s) that keep the group expanded
--}}
@props(['id', 'icon', 'label', 'active' => []])

@php
    $isActive = request()->routeIs(...(array) $active);
@endphp

<li class="nav-item">
    <a class="nav-link menu-link sb-menu-d" href="#{{ $id }}" data-bs-toggle="collapse"
       role="button" aria-expanded="{{ $isActive ? 'true' : 'false' }}" aria-controls="{{ $id }}">
        <i class="{{ $icon }}"></i><span>{{ $label }}</span>
    </a>
    <div class="collapse menu-dropdown sb-menu-d @if($isActive) show @endif" id="{{ $id }}">
        <ul class="nav nav-sm flex-column">
            {{ $slot }}
        </ul>
    </div>
</li>
