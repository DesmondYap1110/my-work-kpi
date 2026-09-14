<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') | {{ \App\Support\Branding::name() }}</title>
    <x-branding-head />

    {{-- Before the theme sheets: the login sheet's mobile rules drop the
         background image, and that override only wins if it comes later. --}}
    <x-branding-styles background="#form-section" />

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('assets/css/theme-login.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('assets/css/app-custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <x-loader />

    <section id="form-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div id="logo-div">
                        <img src="{{ \App\Support\Branding::logo('login') }}" alt="{{ \App\Support\Branding::name() }}">
                    </div>
                    <div id="form-div">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/core.js') }}"></script>
    <script src="{{ asset('js/modules/loader.js') }}"></script>
    @stack('scripts')
</body>
</html>
