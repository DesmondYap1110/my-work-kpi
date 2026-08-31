{{-- Stylesheet imports. Order matters: Velzon base first, then the legacy
     structural/colour layers, then the DataTables skins on top. --}}
<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet">

<link href="{{ asset('assets/css/theme-dashboard.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/theme-colors.css') }}" rel="stylesheet">

<link href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/responsive.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/buttons.dataTables.min.css') }}" rel="stylesheet">

<link href="{{ asset('assets/css/app-custom.css') }}" rel="stylesheet">

@yield('css')
@stack('styles')
