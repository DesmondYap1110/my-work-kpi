@extends('layouts.app')

@section('title', $readOnly ? 'My Appraisal' : 'Review Form')

@section('content')
    @php
        $scale = $ratings->sortByDesc('value');
        $max = (int) ($ratings->max('value') ?: 5);
    @endphp

    {{-- Who, for what period, and where it stands. --}}
    <div id="form-box" class="general-box appraisal-head">
        <div class="appraisal-head-main">
            <p id="form-sub-title" class="mb-1">{{ $appraisal->staff->staff_name ?? 'Unknown member' }}</p>
            <p id="footer-p" class="mb-0">
                {{ $appraisal->position->position_name ?? 'No position' }}
                &middot; {{ $appraisal->periodLabel() }}
                @if ($appraisal->reviewer)
                    &middot; Appraiser: {{ $appraisal->reviewer->staff_name }}
                @endif
            </p>
        </div>
        <div class="appraisal-head-score">
            <span class="tb-status" id="tb-status-{{ $appraisal->status->colourId() }}">{{ $appraisal->status->label() }}</span>
            <p class="appraisal-total mb-0">
                {{ $summary['percentage'] === null ? '-' : $summary['percentage'].'%' }}
            </p>
            @if ($summary['band'])
                <p id="footer-p" class="mb-0">
                    {{ $summary['band']->label }}@if ($summary['band']->outcome) &middot; {{ $summary['band']->outcome }} @endif
                </p>
            @endif
        </div>
    </div>

    {{-- What each mark means. Rating anybody without it is guesswork. --}}
    <div id="form-box" class="general-box">
        <p id="form-sub-title">Rating Scale</p>
        <div class="appraisal-scale">
            @foreach ($scale as $rating)
                <div class="appraisal-scale-item">
                    <span class="appraisal-scale-value">{{ $rating->value }}</span>
                    <div>
                        <strong>{{ $rating->label }}</strong>
                        @if ($rating->description)
                            <span class="kpi-item-desc">{{ $rating->description }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Before the ratings, as on the paper form: what was said during the
         period comes before the judgement at the end of it. --}}
    @include('appraisals._checkins', ['appraisal' => $appraisal, 'readOnly' => $readOnly])

    {{-- Opened only when there is something to submit. A member reading a
         finished appraisal has no business being inside a form that posts to a
         route they are refused anyway. --}}
    @unless ($readOnly)
        <form action="{{ route('appraisals.update', $appraisal->id) }}" method="POST">
            @csrf @method('PUT')
    @endunless

        {{-- The period is the appraiser's choice of what to judge; changing it
             rebuilds Part 2 from the work done in the new range. --}}
        <div id="form-box" class="general-box">
            <p id="form-sub-title">Review Period</p>
            <div class="row">
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>From<span>*</span></label>
                        <input type="date" class="form-control" name="period_from" required
                               @disabled($readOnly)
                               value="{{ old('period_from', optional($appraisal->period_from)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>To<span>*</span></label>
                        <input type="date" class="form-control" name="period_to" required
                               @disabled($readOnly)
                               value="{{ old('period_to', optional($appraisal->period_to)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>Review Date</label>
                        <input type="date" class="form-control" name="review_date"
                               @disabled($readOnly)
                               value="{{ old('review_date', optional($appraisal->review_date)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>Next Assessment</label>
                        <input type="date" class="form-control" name="next_assessment_date"
                               @disabled($readOnly)
                               value="{{ old('next_assessment_date', optional($appraisal->next_assessment_date)->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        @foreach ($summary['sections'] as $part)
            @include('appraisals._section', ['part' => $part, 'scale' => $scale, 'max' => $max, 'readOnly' => $readOnly])
        @endforeach

        {{-- The totals, part by part, and what the whole form comes to. --}}
        <div id="form-box" class="general-box">
            <p id="form-sub-title">Summary</p>
            <div id="table-div">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th class="text-center">Weighting</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['sections'] as $part)
                            <tr>
                                <td>{{ $part['section']->title }}</td>
                                <td class="text-center">{{ rtrim(rtrim(number_format($part['section']->weight(), 2), '0'), '.') }}%</td>
                                <td class="text-center">
                                    @if ($part['max'] > 0)
                                        {{ $part['earned'] }}/{{ $part['max'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $part['percentage'] === null ? '-' : $part['percentage'].'%' }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="appraisal-total-row">
                            <td colspan="3"><strong>Total</strong></td>
                            <td class="text-center">
                                <strong>{{ $summary['percentage'] === null ? '-' : $summary['percentage'].'%' }}</strong>
                            </td>
                        </tr>
                        @if ($summary['band'])
                            <tr>
                                <td colspan="3">Outcome</td>
                                <td class="text-center">
                                    <span class="tb-status" id="tb-status-{{ $summary['band']->outcome === 'Fail' ? 2 : ($summary['band']->outcome === 'Pass' ? 1 : 3) }}">
                                        {{ $summary['band']->label }}@if ($summary['band']->outcome) &middot; {{ $summary['band']->outcome }} @endif
                                    </span>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @unless ($summary['percentage'] !== null)
                <span id="note-p" class="d-block">
                    Nothing has been rated yet, so there is no score. An item left blank
                    is left out of the total and out of the maximum &mdash; which is how
                    the &ldquo;(if applicable)&rdquo; groups are skipped.
                </span>
            @endunless
        </div>

        <div id="form-box" class="general-box">
            <p id="form-sub-title">Other Comments</p>
            @if ($readOnly)
                <p id="footer-p" class="appraisal-comments">{{ $appraisal->comments ?: 'No comments.' }}</p>
            @else
                {{-- The prompt printed on the form itself. --}}
                <p id="footer-p" class="mb-2">
                    Indicate staff&rsquo;s: 1. Value to company &nbsp; 2. Value to clients (if applicable)
                    &nbsp; 3. Value to the team
                </p>
                <textarea class="form-control" name="comments" rows="4">{{ old('comments', $appraisal->comments) }}</textarea>
            @endif
        </div>

        @unless ($readOnly)
            <div id="form-btn-div">
                <a href="{{ route('appraisals.index') }}" id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save Draft</button>
            </div>
        </form>
    @else
        {{-- Back to whichever list the reader came from: their own appraisals,
             or every appraisal if they are the appraiser. --}}
        <div id="form-btn-div">
            <a href="{{ auth()->user()->isAdmin() ? route('appraisals.index') : route('my.appraisals.index') }}"
               id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
        </div>
    @endunless

    {{-- Generating hands the form to the member, so it is its own decision and
         its own form - not a second button inside the one above. --}}
    @unless ($readOnly && ! auth()->user()->isAdmin())
        <div id="form-box" class="general-box">
            @if ($appraisal->isGenerated())
                <p id="form-sub-title">Generated</p>
                <p id="footer-p" class="mb-3">
                    {{ $appraisal->staff->staff_name ?? 'The member' }} can read this appraisal
                    @if ($appraisal->generated_at) as of {{ $appraisal->generated_at->format('d M Y H:i') }} @endif.
                    Reopen it to make changes; it is hidden from them again while it is a draft.
                </p>
                <form action="{{ route('appraisals.reopen', $appraisal->id) }}" method="POST">
                    @csrf
                    <button type="submit" id="general-btn" class="btn2"><i class="ri-lock-unlock-line"></i>Reopen</button>
                </form>
            @else
                <p id="form-sub-title">Generate</p>
                <p id="footer-p" class="mb-3">
                    Until this is generated it is your own working note and
                    {{ $appraisal->staff->staff_name ?? 'the member' }} cannot see it.
                    Save your marks first &mdash; generating does not save them.
                </p>
                {{-- data-confirm belongs on the form; see public/js/modules/confirm.js --}}
                <form action="{{ route('appraisals.generate', $appraisal->id) }}" method="POST"
                      data-confirm-title="Generate appraisal"
                      data-confirm="{{ $appraisal->staff->staff_name ?? 'The member' }} will be able to read this appraisal once it is generated."
                      data-confirm-label="Generate"
                      data-confirm-icon="ri-send-plane-line">
                    @csrf
                    <button type="submit" id="general-btn" class="btn1"><i class="ri-send-plane-line"></i>Generate</button>
                </form>
            @endif
        </div>
    @endunless
@endsection
