{{--
    A labelled <select> in the theme's .input-group shape.

    <x-form.select name="team_id" label="Team" :options="$teams" placeholder="Select Team" required />

    @param string      $name
    @param array       $options      value => label
    @param string|null $placeholder  disabled first option
--}}
@props(['name', 'label' => null, 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false])

@php $selected = old($name, $value); @endphp

<div class="input-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required)<span>*</span>@endif</label>
    @endif

    <select id="{{ $name }}" name="{{ $name }}" @if ($required) required @endif
            {{ $attributes->merge(['class' => 'form-control']) }}>
        @if ($placeholder)
            <option value="" @if (blank($selected)) selected @endif disabled>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
