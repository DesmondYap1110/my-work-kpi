{{--
    One mark on the appraisal form.

    A dropdown while the form is being filled in, and the mark's own words once
    it is generated - a finished appraisal is something to read, not a page of
    disabled form controls.

    Blank is a real choice: an unrated item is left out of both the score and
    the maximum. See AssessmentScore::score().

    @param string $name
    @param int|null $value
    @param mixed $scale     the template's ratings, highest first
    @param bool $readOnly
--}}
@props(['name', 'value' => null, 'scale', 'readOnly' => false])

@php
    $rating = $scale->firstWhere('value', $value);
@endphp

@if ($readOnly)
    @if ($value === null)
        <span class="appraisal-mark-empty" title="Not rated">&ndash;</span>
    @else
        <span class="appraisal-mark-value" @if ($rating?->description) title="{{ $rating->description }}" @endif>
            {{ $value }}
        </span>
        <span class="kpi-item-desc">{{ $rating?->label }}</span>
    @endif
@else
    <select class="form-control appraisal-mark" name="{{ $name }}" aria-label="Mark">
        <option value="">&ndash;</option>
        @foreach ($scale as $rating)
            <option value="{{ $rating->value }}" @selected((int) $value === $rating->value)>
                {{ $rating->value }} &middot; {{ $rating->label }}
            </option>
        @endforeach
    </select>
@endif
