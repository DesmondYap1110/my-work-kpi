{{--
    One mark on the appraisal form.

    A dropdown while the form is being filled in, and the mark itself once it is
    generated - a finished appraisal is something to read, not a page of
    disabled form controls.

    The choices are the item's own allowed marks, as set for it on the
    position's KPI Setting page, so an item marked 1-3 offers 1-3.

    Blank is a real choice: an unrated item is left out of both the score and
    the maximum. See AssessmentScore::score().

    @param string    $name
    @param int|null  $value
    @param array     $marks     the marks this item may be given, highest first
    @param bool      $readOnly
--}}
@props(['name', 'value' => null, 'marks' => [], 'readOnly' => false])

@if ($readOnly)
    @if ($value === null)
        <span class="appraisal-mark-empty" title="Not rated">&ndash;</span>
    @else
        <span class="appraisal-mark-value">{{ $value }}</span>
        @if ($marks)
            <span class="kpi-item-desc">of {{ max($marks) }}</span>
        @endif
    @endif
@else
    <select class="form-control appraisal-mark" name="{{ $name }}" aria-label="Mark">
        <option value="">&ndash;</option>
        @foreach ($marks as $mark)
            <option value="{{ $mark }}" @selected($value !== null && (int) $value === $mark)>{{ $mark }}</option>
        @endforeach
    </select>
@endif
