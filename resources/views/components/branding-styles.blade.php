{{--
    Emits the branding tokens from config/branding.php as CSS custom
    properties on :root, plus the configured web font.

    The stylesheets in public/assets/css read these as
    var(--brand-token, #fallback), so this block is what makes an .env
    colour change show up in the UI - with no build step.

    @param string|null $background  Selector to paint the configured
                                    background onto (the guest layout passes
                                    "#form-section"). Omit on pages that have
                                    no branded background.

    IMPORTANT: when $background is used, include this component *before* the
    theme stylesheets. The login sheet unsets the background inside its
    mobile media query, and that override only wins if it comes later in
    source order. Custom properties resolve regardless of order, so the
    tokens themselves are unaffected by the placement.
--}}
@props(['background' => null])

@php
    $fontUrl = \App\Support\Branding::googleFontsUrl();
    $variables = \App\Support\Branding::cssVariables();
    $backgroundStyles = $background ? \App\Support\Branding::backgroundStyles() : [];
@endphp

@if ($fontUrl)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $fontUrl }}" rel="stylesheet">
@endif

<style>
    :root {
        {!! \App\Support\Branding::cssDeclarations($variables) !!}
    }

    body {
        font-family: var(--brand-font-body);
    }

    @if ($backgroundStyles)
    {{ $background }} {
        {!! \App\Support\Branding::cssDeclarations($backgroundStyles) !!}
    }
    @endif
</style>
