{{--
    Favicon, touch icon and Open Graph tags, all resolved from
    config/branding.php so swapping the icons never means editing a layout.
--}}
@php
    $branding = \App\Support\Branding::class;
    $favicon = $branding::favicon();
    $appleTouchIcon = $branding::appleTouchIcon();
    $ogImage = $branding::ogImage();
    $description = $branding::get('description');
@endphp

@if ($favicon)
    <link rel="icon" href="{{ $favicon }}">
@endif

@if ($appleTouchIcon)
    <link rel="apple-touch-icon" href="{{ $appleTouchIcon }}">
@endif

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif

<meta property="og:site_name" content="{{ $branding::name() }}">
<meta property="og:title" content="@yield('title', $branding::name())">

@if ($description)
    <meta property="og:description" content="{{ $description }}">
@endif

@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif
