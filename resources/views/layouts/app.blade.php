<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ \App\Support\Branding::name() }}</title>
    <x-branding-head />

    <script src="{{ asset('assets/js/layout.js') }}"></script>

    @include('layouts.admin.master.master-style')
</head>
<body class="body-d">
    <x-loader />

    <div id="layout-wrapper">
        @include('layouts.admin.header.header-main')

        @include('layouts.admin.sidebar.sidebar-main')

        <div class="main-content">
            @include('layouts.admin.header.breadcrumbs-main')

            @include('layouts.admin.content.content-main')

            @include('layouts.admin.footer.footer-main')
        </div>
    </div>

    @include('layouts.admin.footer.mobile-menu')

    <x-assistant-widget />

    @include('layouts.admin.master.master-script')
</body>
</html>
