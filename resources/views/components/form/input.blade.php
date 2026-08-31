{{--
    A labelled field in the theme's .input-group shape.

    <x-form.input name="team_name" label="Team Name" required />
    <x-form.input name="email" type="email" label="Email" :value="$staff->email" />

    @param string      $name
    @param string|null $label
    @param string      $type
    @param mixed       $value     falls back to old() so validation redisplays
    @param bool        $required  renders the theme's red asterisk
--}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false])

<div class="input-group">
    @if ($label)
        <label for="{{ $name }}">{{ $label }}@if ($required)<span>*</span>@endif</label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge(['class' => 'form-control']) }}
    >
</div>
