{{--
    A child link inside <x-sidebar.ui.dropdown>.

    Pass $route + $label for a normal link, or omit $route and use the slot
    for a non-link entry (e.g. the logout form).
--}}
@props(['route' => null, 'label' => null, 'active' => null])

<li class="nav-item">
    @if ($route)
        <a @if(request()->routeIs(...(array) ($active ?? $route))) id="sb-sub-active" @endif
           class="nav-link" href="{{ route($route) }}">{{ $label }}</a>
    @else
        {{ $slot }}
    @endif
</li>
