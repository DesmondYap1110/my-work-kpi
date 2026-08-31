<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') | {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-sm.svg') }}">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/theme-login.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app-custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <x-loader />

    <section id="form-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <div id="logo-div">
                        <img src="{{ asset('images/logo-light.svg') }}" alt="{{ config('app.name') }}">
                    </div>
                    <div id="form-div">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/loader.js') }}"></script>
    @stack('scripts')
</body>
</html>
