{{--
    A labelled <textarea> in the theme's .input-group shape.

    @param string $name
    @param int    $rows
--}}
@props(['name', 'label' => null, 'value' => null, 'rows' => 3, 'required' => false])

<div class="input-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required)<span>*</span>@endif</label>
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
              @if ($required) required @endif
              {{ $attributes->merge(['class' => 'form-control']) }}>{{ old($name, $value) }}</textarea>
</div>
