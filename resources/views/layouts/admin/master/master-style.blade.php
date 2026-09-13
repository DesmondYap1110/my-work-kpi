{{-- Stylesheet imports. Order matters: Velzon base first, then the legacy
     structural/colour layers, then the DataTables skins on top. --}}
<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet">

<link href="{{ \App\Support\Asset::url('assets/css/theme-dashboard.css') }}" rel="stylesheet">
<link href="{{ \App\Support\Asset::url('assets/css/theme-colors.css') }}" rel="stylesheet">

<link href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/responsive.bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/buttons.dataTables.min.css') }}" rel="stylesheet">

{{-- Select2: searchable multi-selects for long lists (e.g. a tag's positions). --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css" rel="stylesheet">

<link href="{{ \App\Support\Asset::url('assets/css/app-custom.css') }}" rel="stylesheet">

{{-- Branding tokens (config/branding.php) - last, so they win. --}}
<x-branding-styles />

@yield('css')
@stack('styles')
