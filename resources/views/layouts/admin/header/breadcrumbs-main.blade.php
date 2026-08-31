{{--
    $breadcrumbs is supplied by AppServiceProvider's view composer, from the
    current controller's getBreadcrumbs(). Controllers that don't implement
    BreadcrumbInterfaces fall back to the page title as the active crumb.

    The dashboard has no breadcrumb in the legacy design; #ft-section carries
    its own top padding to clear the fixed header instead.
--}}
@unless (request()->routeIs('dashboard'))
    <section id="bc-section">
        <div id="bc-div">
            <a href="{{ route('dashboard') }}"><i class="ri-dashboard-2-line"></i></a>

            @forelse ($breadcrumbs as $crumb)
                <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>

                @if ($crumb['active'])
                    <span id="bc-active">{{ $crumb['name'] }}</span>
                @elseif ($crumb['route'] !== '')
                    <a href="{{ route($crumb['route']) }}">{{ $crumb['name'] }}</a>
                @else
                    {{ $crumb['name'] }}
                @endif
            @empty
                <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                <span id="bc-active">@yield('title')</span>
            @endforelse
        </div>
    </section>
@endunless
