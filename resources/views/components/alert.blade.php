{{--
    Flash / validation alert.

    <x-alert type="success">Saved.</x-alert>

    @param string $type  success|danger|warning|info
--}}
@props(['type' => 'success'])

<div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }} role="alert">
    {{ $slot }}
</div>
