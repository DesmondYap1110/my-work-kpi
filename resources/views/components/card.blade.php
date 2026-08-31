{{--
    The theme's white surface panel (.general-box), painted from
    --brand-surface.

    <x-card>...</x-card>                     plain panel
    <x-card padded>...</x-card>              with the theme's inner padding
    <x-card title="Filter">...</x-card>      with a dashed-underline heading

    @param string|null $title
    @param bool        $padded
--}}
@props(['title' => null, 'padded' => false])

<div {{ $attributes->merge(['class' => 'general-box', 'id' => 'tb-box']) }}>
    @if ($title)
        <div id="tb-border-line"><p id="tb-title">{{ $title }}</p></div>
    @endif

    @if ($padded)
        <div id="table-padding">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</div>
