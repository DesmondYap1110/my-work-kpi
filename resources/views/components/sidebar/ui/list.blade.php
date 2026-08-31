{{--
    A single top-level sidebar link.

    $route  - route name to link to
    $icon   - RemixIcon class
    $label  - visible text
    $active - route pattern(s) that light this item up; defaults to $route
--}}
@props(['route', 'icon', 'label', 'active' => null])

@php
    $patterns = (array) ($active ?? $route);
@endphp

<li class="nav-item">
    <a @if(request()->routeIs(...$patterns)) id="sb-active" @endif
       class="nav-link menu-link sb-menu-d" href="{{ route($route) }}">
        <i class="{{ $icon }}"></i><span>{{ $label }}</span>
    </a>
</li>
