{{-- Pages override section-id when they need the wrapper to carry a different
     theme section (the dashboard uses #ft-section for its tile padding). --}}
<div class="page-content">
    <section id="@yield('section-id', 'general-section')">
        <div class="container-fluid">
            @include('layouts.admin.content.alerts-main')

            @yield('content')
        </div>
    </section>
</div>

@include('layouts.admin.content.confirm-modal')
