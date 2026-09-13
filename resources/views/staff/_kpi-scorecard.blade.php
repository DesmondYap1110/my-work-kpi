{{--
    A member's KPI score out of 100, laid out the way it is calculated.

        Project marks   earned / target  ->  x of the project share
      + KPI objectives  approved / max   ->  y of the objectives share
      = KPI score                             out of 100

    Every number comes from StaffKpiScoreService::finalScore(), and the two
    parts use the same rule as the total (StaffKpiScoreService::points()), so
    they always add up to what is printed beside them.

    Expects: $staff, $period (StaffKpiScoreService::reviewPeriod()), $finalScore,
             $objectiveBreakdown, $completedProjects, $selectedProjectId, $projectScore
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
            <p id="footer-p" class="mb-0">
                {{ $periodLabel }}
                @if ($lastAppraisal)
                    <span class="kpi-item-desc">last appraisal {{ $lastAppraisal->periodLabel() }}</span>
                @else
                    <span class="kpi-item-desc">no appraisal yet</span>
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
                    No approved objective marks yet.
                @else
                    {{ $o['total_mark'] }} of {{ $o['max_possible'] }} marks approved
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

        {{-- KPI objectives: approved marks item by item. --}}
        <div class="col-12">
            <div class="kpi-panel-head">
                <p class="kpi-panel-title mb-0">
                    KPI objectives
                    <span class="kpi-item-desc">approved marks from completed projects</span>
                </p>
                <div class="kpi-panel-tools">
                    <input type="search" class="form-control kpi-panel-search" placeholder="Search category, objective or item" aria-label="Search KPI objectives" data-table-search="#kpi-objective-table">
                    @if ($completedProjects->isNotEmpty())
                        <form method="GET" action="" class="kpi-panel-filter">
                            {{-- Keep the review period when switching project. --}}
                            <input type="hidden" name="range" value="{{ $period['range'] }}">
                            @if ($period['range'] === 'custom')
                                <input type="hidden" name="from" value="{{ $finalScore['from']->format('Y-m-d') }}">
                                <input type="hidden" name="to" value="{{ $finalScore['to']->format('Y-m-d') }}">
                            @endif
                            <select class="form-control" name="pid" aria-label="Project" onchange="this.form.submit()">
                                <option value="">All completed projects</option>
                                @foreach ($completedProjects as $project)
                                    <option value="{{ $project->id }}" @selected($selectedProjectId == $project->id)>{{ $project->title }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </div>

            <div id="table-div">
                <table class="table table-bordered align-middle" id="kpi-objective-table">
                    <thead>
                        <tr>
                            <th>Objective</th>
                            <th class="text-center">Marks</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($objectiveBreakdown as $group)
                            <tr data-search-level="1"><td colspan="3" id="tb-sub-til">{{ $group['category'] }}</td></tr>
                            @foreach ($group['objectives'] as $objective)
                                <tr data-search-level="2"><td colspan="3" class="kpi-objective-row">{{ $objective['title'] }}</td></tr>
                                @foreach ($objective['items'] as $item)
                                    <tr>
                                        <td class="kpi-item-cell">{{ $item['title'] }}</td>
                                        <td class="text-center">
                                            @if ($item['max'] > 0)
                                                <strong>{{ $item['approved'] }}</strong> / {{ $item['max'] }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($item['pending'])
                                                <span class="tb-status" id="tb-status-3">{{ $item['pending'] }} pending</span>
                                            @endif
                                            @if ($item['rejected'])
                                                <span class="tb-status" id="tb-status-2">{{ $item['rejected'] }} rejected</span>
                                            @endif
                                            @if (! $item['pending'] && ! $item['rejected'])
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @empty
                            <tr><td colspan="3" class="text-center">This position has no KPI objectives yet.</td></tr>
                        @endforelse
                        <tr data-search-empty hidden><td colspan="3" class="text-center">No objective matches your search.</td></tr>
                    </tbody>
                    @php $objTotal = $projectScore ?? $o; @endphp
                    <tfoot>
                        <tr>
                            <td class="text-end"><strong>{{ $selectedProjectId ? 'This project' : 'Approved' }}</strong></td>
                            <td class="text-center">
                                @if ($objTotal['max_possible'] > 0)
                                    <strong>{{ $objTotal['total_mark'] }}</strong> / {{ $objTotal['max_possible'] }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $objTotal['max_possible'] > 0 ? $fmt($objTotal['percentage']).'%' : '' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if ($completedProjects->isEmpty())
                <p id="footer-p" class="mb-0">
                    Objective marks are recorded when a project this member worked on is completed - none was completed in this period.
                </p>
            @endif
        </div>
    </div>
</div>
