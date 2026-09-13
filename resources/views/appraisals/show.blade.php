@extends('layouts.app')

@php
    // Three ways onto this page: the appraiser rating a draft, the member
    // filling in their self-assessment on a draft, and anyone reading a
    // generated appraisal. The member's self-assessment touches the Employee
    // column only; everything else on the page is the appraiser's.
    $selfAssessment = $selfAssessment ?? false;
    $appraiserLocked = $readOnly || $selfAssessment;
@endphp

@section('title', $readOnly || $selfAssessment ? 'My Appraisal' : 'Review Form')

@section('content')
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
          @if ($selfAssessment)
            <span class="tb-status" id="tb-status-3">Self-assessment</span>
          @else
            <span class="tb-status" id="tb-status-{{ $appraisal->status->colourId() }}">{{ $appraisal->status->label() }}</span>
            <p class="appraisal-total mb-0">
                {{ $summary['percentage'] === null ? '-' : $summary['percentage'].'%' }}
            </p>
            @if ($summary['band'])
                <p id="footer-p" class="mb-0">
                    {{ $summary['band']->label }}@if ($summary['band']->outcome) &middot; {{ $summary['band']->outcome }} @endif
                </p>
            @endif
          @endif
        </div>
    </div>

    {{-- Opened only when there is something to submit. A member reading a
         finished appraisal has no business being inside a form that posts to a
         route they are refused anyway. --}}
    @unless ($readOnly)
        <form action="{{ $selfAssessment ? route('my.appraisals.update', $appraisal->id) : route('appraisals.update', $appraisal->id) }}" method="POST">
            @csrf @method('PUT')
    @endunless

        {{-- The period is the appraiser's choice of what to judge; changing it
             recounts the project marks over the new range. --}}
        <div id="form-box" class="general-box">
            <p id="form-sub-title">Review Period</p>
            <div class="row">
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>From<span>*</span></label>
                        <input type="date" class="form-control" name="period_from" required
                               @disabled($appraiserLocked)
                               value="{{ old('period_from', optional($appraisal->period_from)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>To<span>*</span></label>
                        <input type="date" class="form-control" name="period_to" required
                               @disabled($appraiserLocked)
                               value="{{ old('period_to', optional($appraisal->period_to)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>Review Date</label>
                        <input type="date" class="form-control" name="review_date"
                               @disabled($appraiserLocked)
                               value="{{ old('review_date', optional($appraisal->review_date)->format('Y-m-d')) }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>Next Assessment</label>
                        <input type="date" class="form-control" name="next_assessment_date"
                               @disabled($appraiserLocked)
                               value="{{ old('next_assessment_date', optional($appraisal->next_assessment_date)->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- The two parts of a KPI score, as the position's KPI Setting defines
             them: project marks earned in the period, then the KPI objectives
             rated here. See AssessmentScoreService::summary(). --}}
        @php
            $p = $summary['projects'];
            $o = $summary['objectives'];
            $num = fn ($n) => $n === null ? '-' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
        @endphp

        <div id="form-box" class="general-box">
            <div class="appraisal-section-head">
                <p id="form-sub-title" class="mb-0">Projects</p>
                <span class="appraisal-weight">{{ $p['share'] }} of 100 points</span>
            </div>
            <p id="footer-p" class="mb-3">
                Task marks {{ $appraisal->staff->staff_name ?? 'this member' }} earned between {{ $appraisal->periodLabel() }}.
                @unless ($appraiserLocked) Change the review period above and save to recount. @endunless
            </p>
            <div id="table-div">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th class="text-center">Tag</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Marks</th>
                        </tr>
                    </thead>
                    <tbody data-show-more="5" data-show-more-label="tasks">
                        @forelse ($p['tasks'] as $task)
                            <tr>
                                <td>{{ $task->title }} <span class="kpi-item-desc">{{ $task->project->title ?? '-' }}</span></td>
                                <td class="text-center">{{ $task->tag->name ?? 'No tag' }}</td>
                                <td class="text-center"><span class="tb-status" id="tb-status-{{ $task->status->colourId() }}">{{ $task->status->label() }}</span></td>
                                <td class="text-center">
                                    @if ($task->status->isDone())
                                        <strong>{{ $num($task->points()) }}</strong>
                                    @else
                                        <span class="kpi-muted">0 / {{ $num($task->points()) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No project work in this period.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Earned</strong></td>
                            <td class="text-center">
                                <strong>{{ $num($p['earned']) }}</strong> / {{ $p['target'] ? $num($p['target']).' target' : $num($p['assigned']).' assigned' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @include('appraisals._section', [
            'part' => $o,
            'objectivesShare' => $o['share'],
            'selfAssessment' => $selfAssessment,
            // Employee is the member's column, Reviewer the appraiser's.
            'employeeReadOnly' => ! $selfAssessment,
            'reviewerReadOnly' => $appraiserLocked,
        ])

      @unless ($selfAssessment)
        {{-- Projects + KPI objectives = KPI score, the same sum as the
             member's KPI page. --}}
        <div id="form-box" class="general-box">
            <p id="form-sub-title">Summary</p>
            <div id="table-div">
                <table class="table table-bordered align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th class="text-center">Result</th>
                            <th class="text-center">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Projects</td>
                            <td class="text-center">
                                @if ($p['percentage'] === null)
                                    <span class="kpi-muted">No project work</span>
                                @else
                                    {{ $num($p['earned']) }} of {{ $num($p['target'] ?? $p['assigned']) }} marks ({{ $num($p['percentage']) }}%)
                                @endif
                            </td>
                            <td class="text-center">{{ $num($p['points']) }} <span class="kpi-muted">/ {{ $p['share'] }}</span></td>
                        </tr>
                        <tr>
                            <td>KPI objectives</td>
                            <td class="text-center">
                                @if ($o['percentage'] === null)
                                    <span class="kpi-muted">Not rated yet</span>
                                @else
                                    {{ $num($o['earned']) }} of {{ $num($o['max']) }} rated ({{ $num($o['percentage']) }}%)
                                @endif
                            </td>
                            <td class="text-center">{{ $num($o['points']) }} <span class="kpi-muted">/ {{ $o['share'] }}</span></td>
                        </tr>
                        <tr class="appraisal-total-row">
                            <td colspan="2"><strong>KPI score</strong></td>
                            <td class="text-center"><strong>{{ $num($summary['percentage']) }}</strong> <span class="kpi-muted">/ 100</span></td>
                        </tr>
                        @if ($summary['band'])
                            <tr>
                                <td colspan="2">Outcome</td>
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
        </div>

        <div id="form-box" class="general-box">
            <p id="form-sub-title">Other Comments</p>
            @if ($readOnly)
                <p id="footer-p" class="appraisal-comments">{{ $appraisal->comments ?: 'No comments.' }}</p>
            @else
                <textarea class="form-control" name="comments" rows="4">{{ old('comments', $appraisal->comments) }}</textarea>
            @endif
        </div>
      @endunless

    @php $memberName = $appraisal->staff->staff_name ?? 'the member'; @endphp

        @if ($selfAssessment)
            <div id="form-box" class="general-box appraisal-actions">
                <div class="appraisal-actions-status">
                    <span class="tb-status" id="tb-status-3">Draft</span>
                    <span>Rate yourself on each item. You can change it until your appraiser generates the review.</span>
                </div>
                <div class="appraisal-actions-buttons">
                    <a href="{{ route('my.appraisals.index') }}" id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
                    <button type="button" id="general-btn" class="btn2" onclick="window.print()"><i class="ri-printer-line"></i>Print</button>
                    <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-line"></i>Save My Marks</button>
                </div>
            </div>
        </form>
        @elseif (! $readOnly)
            {{-- One bar for everything that finishes this page. Save & Generate
                 saves the marks first, so nothing typed is lost, and asks before
                 handing the appraisal over - see public/js/modules/confirm.js. --}}
            <div id="form-box" class="general-box appraisal-actions">
                <div class="appraisal-actions-status">
                    <span class="tb-status" id="tb-status-3">Draft</span>
                    <span>Only you can see this until it is generated.</span>
                    <button type="button" class="kpi-help-btn" aria-label="What does generating do?"
                            data-help-hover="Save Draft keeps your marks private while you work. Save & Generate saves them and shares the appraisal with {{ $memberName }}. You can reopen it later to make changes.">
                        <i class="ri-question-line"></i>
                    </button>
                </div>
                <div class="appraisal-actions-buttons">
                    <a href="{{ route('appraisals.index') }}" id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
                    {{-- Exports what is saved: save first to include changes on screen. --}}
                    <a href="{{ route('appraisals.export-one', $appraisal->id) }}" id="general-btn" class="btn2" download title="Download the saved form as CSV"><i class="ri-file-download-line"></i>Export CSV</a>
                    <button type="button" id="general-btn" class="btn2" onclick="window.print()"><i class="ri-printer-line"></i>Print</button>
                    <button type="submit" id="general-btn" class="btn2"><i class="ri-save-3-line"></i>Save Draft</button>
                    <button type="submit" id="general-btn" class="btn1" name="generate" value="1"
                            data-confirm-title="Generate appraisal"
                            data-confirm="Your marks will be saved and {{ $memberName }} will be able to read this appraisal."
                            data-confirm-label="Save & Generate"
                            data-confirm-icon="ri-send-plane-line">
                        <i class="ri-send-plane-line"></i>Save &amp; Generate
                    </button>
                </div>
            </div>
        </form>
    @else
        <div id="form-box" class="general-box appraisal-actions">
            <div class="appraisal-actions-status">
                @if ($appraisal->isGenerated())
                    <span class="tb-status" id="tb-status-1">Generated</span>
                    <span>
                        {{ auth()->user()->isAdmin() ? ucfirst($memberName).' can read this' : 'Shared with you' }}@if ($appraisal->generated_at) since {{ $appraisal->generated_at->format('d M Y H:i') }}@endif.
                    </span>
                @endif
            </div>
            <div class="appraisal-actions-buttons">
                {{-- Back to whichever list the reader came from. --}}
                <a href="{{ auth()->user()->isAdmin() ? route('appraisals.index') : route('my.appraisals.index') }}"
                   id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('appraisals.export-one', $appraisal->id) }}" id="general-btn" class="btn2" download><i class="ri-file-download-line"></i>Export CSV</a>
                @endif
                <button type="button" id="general-btn" class="btn2" onclick="window.print()"><i class="ri-printer-line"></i>Print</button>
                @if (auth()->user()->isAdmin() && $appraisal->isGenerated())
                    <form action="{{ route('appraisals.reopen', $appraisal->id) }}" method="POST" class="d-inline"
                          data-confirm-title="Reopen appraisal"
                          data-confirm="{{ ucfirst($memberName) }} will no longer see this until you generate it again."
                          data-confirm-label="Reopen"
                          data-confirm-icon="ri-lock-unlock-line">
                        @csrf
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-lock-unlock-line"></i>Reopen to Edit</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
@endsection
