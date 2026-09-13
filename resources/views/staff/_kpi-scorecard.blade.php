{{--
    A member's KPI score out of 100, laid out the way it is calculated.

        Project marks   earned / target  ->  x of the project share
      + KPI objectives  appraisal marks  ->  y of the objectives share
      = KPI score                             out of 100

    Every number comes from StaffKpiScoreService::finalScore(), and the two
    parts use the same rule as the total (StaffKpiScoreService::points()), so
    they always add up to what is printed beside them.

    Expects: $staff, $period (StaffKpiScoreService::reviewPeriod()), $finalScore,
             $appraisals, $selectedAppraisal, $appraisalObjectives
             (AssessmentScoreService::objectives() for the selected appraisal)
--}}
@php
    $d = $finalScore['delivery_detail'];
    $o = $finalScore['objective_detail'];
    $nominalProjectShare = round($finalScore['weight'] * 100);
    $fmt = fn ($n) => $n === null ? '-' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $periodLabel = $finalScore['from']->format('d M Y').' – '.$finalScore['to']->format('d M Y');
    $lastAppraisal = $period['lastAppraisal'];
@endphp

<div id="form-box" class="general-box mb-3">
    <div class="kpi-card-head">
        <div>
            <p id="form-sub-title" class="mb-1">KPI Score</p>
            <p id="footer-p" class="mb-0 kpi-card-period">
                {{ $periodLabel }}
                @if ($lastAppraisal)
                    <span class="kpi-item-desc kpi-card-period-note">last appraisal {{ $lastAppraisal->periodLabel() }}</span>
                @else
                    <span class="kpi-item-desc kpi-card-period-note">no appraisal yet</span>
                @endif
            </p>
        </div>
        <div class="kpi-card-total">
            <span class="kpi-card-total-value">{{ $fmt($finalScore['percentage']) }}</span>
            <span class="kpi-card-total-of">/ 100</span>
        </div>
    </div>

    {{-- The review period. A preset submits straight away; picking a date
         switches to Custom, applied with the button. --}}
    <form method="GET" action="" class="kpi-period-form">
        <select class="form-control" name="range" aria-label="Review period"
                onchange="if (this.value !== 'custom') { this.form.from.disabled = this.form.to.disabled = true; this.form.submit(); }">
            <option value="since_appraisal" @selected($period['range'] === 'since_appraisal') @disabled(! $lastAppraisal)>Since last appraisal</option>
            <option value="last_appraisal" @selected($period['range'] === 'last_appraisal') @disabled(! $lastAppraisal)>Last appraisal period</option>
            <option value="this_year" @selected($period['range'] === 'this_year')>This year</option>
            <option value="custom" @selected($period['range'] === 'custom')>Custom dates</option>
        </select>
        <input type="date" class="form-control" name="from" aria-label="From" value="{{ $finalScore['from']->format('Y-m-d') }}"
               onchange="this.form.range.value = 'custom'">
        <span class="kpi-period-to">to</span>
        <input type="date" class="form-control" name="to" aria-label="To" value="{{ $finalScore['to']->format('Y-m-d') }}"
               onchange="this.form.range.value = 'custom'">
        <button type="submit" id="general-btn" class="btn1" onclick="this.form.range.value = 'custom'"><i class="ri-filter-3-line"></i>Apply</button>
    </form>

    {{-- The calculation, in the same words as the position's Project KPI box. --}}
    <div class="kpi-sum">
        <div class="kpi-sum-part is-project">
            <p class="kpi-sum-label">Project marks</p>
            <p class="kpi-sum-points">
                <strong>{{ $fmt($finalScore['project_points']) }}</strong>
                <span>of {{ $nominalProjectShare }}</span>
            </p>
            <p class="kpi-sum-detail">
                @if ($nominalProjectShare == 0)
                    Not counted for this position.
                @elseif ($d['target'])
                    {{ $fmt($d['earned']) }} of {{ $fmt($d['target']) }} target marks
                @elseif ($finalScore['delivery'] === null)
                    No project work yet.
                @else
                    {{ $fmt($d['earned']) }} of {{ $fmt($d['assigned']) }} marks assigned
                @endif
            </p>
        </div>

        <span class="kpi-sum-op">+</span>

        <div class="kpi-sum-part is-objectives">
            <p class="kpi-sum-label">KPI objectives</p>
            <p class="kpi-sum-points">
                <strong>{{ $fmt($finalScore['objective_points']) }}</strong>
                <span>of {{ 100 - $nominalProjectShare }}</span>
            </p>
            <p class="kpi-sum-detail">
                @if ($finalScore['objective'] === null)
                    No generated appraisal in this period.
                @else
                    {{ $fmt($o['earned']) }} of {{ $fmt($o['max']) }} rated marks
                    &middot; {{ $o['appraisals'] }} {{ Str::plural('appraisal', $o['appraisals']) }}
                @endif
            </p>
        </div>

        <span class="kpi-sum-op">=</span>

        <div class="kpi-sum-part is-total">
            <p class="kpi-sum-label">KPI score</p>
            <p class="kpi-sum-points">
                <strong>{{ $fmt($finalScore['percentage']) }}</strong>
                <span>of 100</span>
            </p>
            <p class="kpi-sum-detail">
                @if ($finalScore['percentage'] !== null
                    && ($finalScore['delivery'] === null || $finalScore['objective'] === null)
                    && $nominalProjectShare > 0 && $nominalProjectShare < 100)
                    {{-- blend() lets the half that has something to score stand
                         alone, so say so rather than leave the maths looking wrong. --}}
                    Only one part has been scored so far, so it counts for the full 100.
                @else
                    &nbsp;
                @endif
            </p>
        </div>
    </div>

    <div class="row">
        {{-- Project marks: the tasks behind the project half. --}}
        <div class="col-12 mb-4">
            <div class="kpi-panel-head">
                <p class="kpi-panel-title mb-0">
                    Project marks
                    <span class="kpi-item-desc">tasks due or completed {{ $periodLabel }}</span>
                </p>
                {{-- Filters as you type - see public/js/modules/table-search.js --}}
                <input type="search" class="form-control kpi-panel-search" placeholder="Search task, project, tag or status" aria-label="Search project marks" data-table-search="#kpi-project-table">
            </div>
            <div id="table-div">
                <table class="table table-bordered align-middle" id="kpi-project-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th class="text-center">Tag</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($d['tasks'] as $task)
                            <tr>
                                <td>
                                    {{ $task->title }}
                                    <span class="kpi-item-desc">{{ $task->project->title ?? '-' }}</span>
                                </td>
                                <td class="text-center">{{ $task->tag->name ?? 'No tag' }}</td>
                                <td class="text-center">
                                    <span class="tb-status" id="tb-status-{{ $task->status->colourId() }}">{{ $task->status->label() }}</span>
                                </td>
                                <td class="text-center">
                                    {{-- Only finished work earns its tag's points. --}}
                                    @if ($task->status->isDone())
                                        <strong>{{ $fmt($task->points()) }}</strong>
                                    @else
                                        <span class="kpi-muted">0 / {{ $fmt($task->points()) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No project tasks in this period.</td></tr>
                        @endforelse
                        <tr data-search-empty hidden><td colspan="4" class="text-center">No task matches your search.</td></tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Earned</strong></td>
                            <td class="text-center">
                                <strong>{{ $fmt($d['earned']) }}</strong>
                                / {{ $d['target'] ? $fmt($d['target']) : $fmt($d['assigned']) }}
                                <span class="kpi-item-desc">{{ $d['target'] ? 'target' : 'assigned' }}</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- KPI objectives: the appraiser's marks item by item, from one of the
             generated appraisals behind the objectives half. --}}
        <div class="col-12">
            <div class="kpi-panel-head">
                <p class="kpi-panel-title mb-0">
                    KPI objectives
                    <span class="kpi-item-desc">marks from generated appraisals covering {{ $periodLabel }}</span>
                </p>
                <div class="kpi-panel-tools">
                    <input type="search" class="form-control kpi-panel-search" placeholder="Search category, objective or item" aria-label="Search KPI objectives" data-table-search="#kpi-objective-table">
                    @if ($appraisals->count() > 1)
                        <form method="GET" action="" class="kpi-panel-filter">
                            {{-- Keep the review period when switching appraisal. --}}
                            <input type="hidden" name="range" value="{{ $period['range'] }}">
                            @if ($period['range'] === 'custom')
                                <input type="hidden" name="from" value="{{ $finalScore['from']->format('Y-m-d') }}">
                                <input type="hidden" name="to" value="{{ $finalScore['to']->format('Y-m-d') }}">
                            @endif
                            <select class="form-control" name="aid" aria-label="Appraisal" onchange="this.form.submit()">
                                @foreach ($appraisals as $appraisal)
                                    <option value="{{ $appraisal->id }}" @selected($selectedAppraisal?->id === $appraisal->id)>Appraisal {{ $appraisal->periodLabel() }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </div>

            @if ($selectedAppraisal)
                @php
                    $appraisalUrl = ($self ?? false)
                        ? route('my.appraisals.show', $selectedAppraisal->id)
                        : route('appraisals.show', $selectedAppraisal->id);
                @endphp
                <div id="table-div">
                    <table class="table table-bordered align-middle" id="kpi-objective-table">
                        <thead>
                            <tr>
                                <th>Objective</th>
                                <th class="text-center">Employee</th>
                                <th class="text-center">Reviewer</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($appraisalObjectives['groups'] as $group)
                                <tr data-search-level="1"><td colspan="3" id="tb-sub-til">{{ $group['category']->name }}</td></tr>
                                @php $lastObjective = null; @endphp
                                @foreach ($group['rows'] as $row)
                                    @if ($row['objective'] !== $lastObjective)
                                        <tr data-search-level="2"><td colspan="3" class="kpi-objective-row">{{ $row['objective'] }}</td></tr>
                                        @php $lastObjective = $row['objective']; @endphp
                                    @endif
                                    @php $best = $row['marks'] ? max($row['marks']) : null; @endphp
                                    <tr>
                                        <td class="kpi-item-cell">{{ $row['info']->title }}</td>
                                        <td class="text-center">
                                            {{ $row['employee_score'] ?? '-' }}@if ($row['employee_score'] !== null && $best) <span class="kpi-muted">/ {{ $best }}</span>@endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row['reviewer_score'] !== null)
                                                <strong>{{ $row['reviewer_score'] }}</strong>@if ($best) <span class="kpi-muted">/ {{ $best }}</span>@endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="3" class="text-center">This position has no KPI objectives yet.</td></tr>
                            @endforelse
                            <tr data-search-empty hidden><td colspan="3" class="text-center">No objective matches your search.</td></tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="text-end">
                                    <strong>Rated</strong>
                                    <a href="{{ $appraisalUrl }}" id="tb-link" class="ms-2">Open appraisal</a>
                                </td>
                                <td colspan="2" class="text-center">
                                    @if ($appraisalObjectives['max'] > 0)
                                        <strong>{{ $fmt($appraisalObjectives['earned']) }}</strong> / {{ $fmt($appraisalObjectives['max']) }}
                                        <span class="kpi-muted">({{ $fmt($appraisalObjectives['percentage']) }}%)</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <p id="footer-p" class="mb-0">
                    The reviewer's mark counts; the employee's own mark stands in only where the reviewer left an item blank.
                </p>
            @else
                <p id="footer-p" class="mb-0">
                    No generated appraisal covers this period, so KPI objectives are not scored yet.
                    They come from the appraiser's marks once an appraisal is generated.
                </p>
            @endif
        </div>
    </div>
</div>
