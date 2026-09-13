@extends('layouts.app')

@section('title', 'KPI Report')

@php
    $num = fn ($n, $suffix = '') => $n === null ? '-' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.').$suffix;
    $rateBar = function (?float $rate) {
        if ($rate === null) {
            return '<span class="kpi-muted">-</span>';
        }
        $tone = $rate >= 70 ? 'is-good' : ($rate >= 50 ? 'is-fair' : 'is-poor');

        return '<div class="report-bar '.$tone.'"><span style="width:'.min(100, max(0, $rate)).'%"></span></div>'
            .'<small>'.rtrim(rtrim(number_format($rate, 1), '0'), '.').'%</small>';
    };
@endphp

@section('content')
    {{--
        KPI Report. Six reports on one page, all driven by the filter bar, so
        narrowing to a team or a member narrows every one of them. Scores follow
        exactly the rules of a member's KPI page - see App\Services\KpiReportService.
    --}}
    <form method="GET" action="{{ route('kpi-report.index') }}" id="form-box" class="general-box report-filter">
        <div class="row align-items-end">
            <div class="col-lg-2 col-md-4">
                <div class="input-group mb-lg-0">
                    <label>Period</label>
                    <select class="form-control" name="period" data-report-period>
                        @foreach ($periods as $value => $label)
                            <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-4" data-report-custom @if ($period !== 'custom') hidden @endif>
                <div class="input-group mb-lg-0">
                    <label>From</label>
                    <input type="date" class="form-control" name="from" value="{{ $from->format('Y-m-d') }}">
                </div>
            </div>
            <div class="col-lg-2 col-md-4" data-report-custom @if ($period !== 'custom') hidden @endif>
                <div class="input-group mb-lg-0">
                    <label>To</label>
                    <input type="date" class="form-control" name="to" value="{{ $to->format('Y-m-d') }}">
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="input-group mb-lg-0">
                    <label>Team</label>
                    <select class="form-control" name="team_id">
                        <option value="">All teams</option>
                        @foreach ($teams as $id => $name)
                            <option value="{{ $id }}" @selected(request('team_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="input-group mb-lg-0">
                    <label>Position</label>
                    <select class="form-control" name="position_id">
                        <option value="">All positions</option>
                        @foreach ($positions as $id => $name)
                            <option value="{{ $id }}" @selected(request('position_id') == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <div class="input-group mb-lg-0">
                    <label>Member</label>
                    {{-- Searchable: a company may have many members. --}}
                    <select class="form-control" name="staff_id" data-report-member>
                        <option value="">All members</option>
                        @foreach ($allMembers->groupBy(fn ($m) => $m->team->team_name ?? 'No team')->sortKeys() as $teamName => $teamMembers)
                            <optgroup label="{{ $teamName }}">
                                @foreach ($teamMembers as $member)
                                    <option value="{{ $member->id }}" @selected(request('staff_id') == $member->id)>{{ $member->staff_name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-auto col-md-4">
                <div class="report-filter-actions">
                    <button type="submit" id="general-btn" class="btn1"><i class="ri-filter-3-line"></i>Apply</button>
                    <a href="{{ route('kpi-report.index') }}" id="general-btn" class="btn2"><i class="ri-refresh-line"></i>Reset</a>
                </div>
            </div>
        </div>

        {{-- Export and print what is on screen: every link carries the
             applied filters (the query string), not unsaved changes above. --}}
        <div class="report-toolbar">
            <span class="kpi-item-desc ms-0">{{ $from->format('d M Y') }} &ndash; {{ $to->format('d M Y') }}</span>
            <div class="report-toolbar-actions">
                <div class="dropdown">
                    {{-- No .dropdown-toggle: the theme draws its caret over the
                         label ("SV⌄"), so the arrow is an icon after the text. --}}
                    <button type="button" id="general-btn" class="btn2 report-export-btn" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri-file-download-line"></i>Export CSV<i class="ri-arrow-down-s-line report-export-caret"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach ($exports as $section => $label)
                            <li>
                                <a class="dropdown-item" download href="{{ route('kpi-report.export', request()->query() + ['section' => $section]) }}">{{ $label }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" id="general-btn" class="btn1" onclick="window.print()">
                    <i class="ri-printer-line"></i>Print
                </button>
            </div>
        </div>
    </form>

    {{-- Printed in place of the filter bar, so a paper copy says what it covers. --}}
    <div class="print-only report-print-head">
        <h2>KPI Report</h2>
        <p>
            {{ $from->format('d M Y') }} &ndash; {{ $to->format('d M Y') }}
            &middot; {{ request('team_id') ? ($teams[request('team_id')] ?? 'Team') : 'All teams' }}
            &middot; {{ request('position_id') ? ($positions[request('position_id')] ?? 'Position') : 'All positions' }}
            &middot; {{ request('staff_id') ? ($allMembers->firstWhere('id', (int) request('staff_id'))->staff_name ?? 'Member') : 'All members' }}
            &middot; printed {{ now()->format('d M Y H:i') }}
        </p>
    </div>

    {{-- 1. KPI Summary --}}
    <div id="form-box" class="general-box">
        <div class="appraisal-section-head">
            <p id="form-sub-title" class="mb-0">KPI Summary</p>
            <span class="kpi-item-desc">
                {{ $from->format('d M Y') }} &ndash; {{ $to->format('d M Y') }}
            </span>
        </div>
        {{-- Highest, lowest and average are over the members who were scored;
             one with nothing to score is left out rather than counted as zero. --}}
        <div class="report-stats">
            <div class="report-stat">
                <span class="report-stat-icon"><i class="ri-arrow-up-line"></i></span>
                <div>
                    <p class="report-stat-label">Highest KPI score</p>
                    <p class="report-stat-value">{{ $num($summary['highest']['percentage'] ?? null) }} <small>/ 100</small></p>
                    <p class="report-stat-note">{{ $summary['highest']['staff']->staff_name ?? 'Nobody scored' }}</p>
                </div>
            </div>
            <div class="report-stat">
                <span class="report-stat-icon is-low"><i class="ri-arrow-down-line"></i></span>
                <div>
                    <p class="report-stat-label">Lowest KPI score</p>
                    <p class="report-stat-value">{{ $num($summary['lowest']['percentage'] ?? null) }} <small>/ 100</small></p>
                    <p class="report-stat-note">{{ $summary['lowest']['staff']->staff_name ?? 'Nobody scored' }}</p>
                </div>
            </div>
            <div class="report-stat">
                <span class="report-stat-icon"><i class="ri-bar-chart-line"></i></span>
                <div>
                    <p class="report-stat-label">Average KPI score</p>
                    <p class="report-stat-value">{{ $num($summary['percentage']) }} <small>/ 100</small></p>
                    <p class="report-stat-note">{{ $summary['scored'] }} of {{ $summary['members'] }} {{ Str::plural('member', $summary['members']) }} scored</p>
                </div>
            </div>
            <div class="report-stat">
                <span class="report-stat-icon"><i class="ri-clipboard-line"></i></span>
                <div>
                    <p class="report-stat-label">Total projects</p>
                    <p class="report-stat-value">{{ $projects->count() }}</p>
                    <p class="report-stat-note">with work in this period</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Performance Trend --}}
    <div id="form-box" class="general-box">
        <div class="appraisal-section-head">
            <p id="form-sub-title" class="mb-0">Performance Trend</p>
            <span class="kpi-item-desc">Monthly average</span>
        </div>
        <div id="report-trend-chart" class="report-chart"></div>
    </div>

    <div class="row">
        {{-- 3. Project Report --}}
        <div class="col-xl-7">
            <div id="form-box" class="general-box report-equal">
                <div class="appraisal-section-head">
                    <p id="form-sub-title" class="mb-0">Project Report</p>
                    <span class="kpi-item-desc">Tasks due or finished in the period</span>
                </div>
                <div id="table-div">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th class="text-center">Completed tasks</th>
                                <th class="text-center">Points earned</th>
                                <th class="text-center report-rate-col">Completion rate</th>
                            </tr>
                        </thead>
                        <tbody data-show-more="8" data-show-more-label="projects">
                            @forelse ($projects as $row)
                                <tr>
                                    <td>
                                        {{ $row['project']->title ?? 'Deleted project' }}
                                        @if ($row['project'])
                                            <span class="kpi-item-desc">{{ $row['project']->status->label() }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $row['done'] }} / {{ $row['total'] }}</td>
                                    <td class="text-center">{{ $num($row['earned']) }} <span class="kpi-muted">/ {{ $num($row['assigned']) }}</span></td>
                                    <td class="report-rate-col">{!! $rateBar($row['rate']) !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">No project work in this period.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($projects->isNotEmpty())
                            @php
                                $pTotal = $projects->sum('total');
                                $pDone = $projects->sum('done');
                            @endphp
                            <tfoot>
                                <tr>
                                    <td class="text-end"><strong>Total</strong></td>
                                    <td class="text-center"><strong>{{ $pDone }} / {{ $pTotal }}</strong></td>
                                    <td class="text-center"><strong>{{ $num($projects->sum('earned')) }}</strong> <span class="kpi-muted">/ {{ $num($projects->sum('assigned')) }}</span></td>
                                    <td class="report-rate-col">{!! $rateBar($pTotal ? round($pDone / $pTotal * 100, 1) : null) !!}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- 6. Task Breakdown --}}
        <div class="col-xl-5">
            <div id="form-box" class="general-box report-equal">
                <div class="appraisal-section-head">
                    <p id="form-sub-title" class="mb-0">Task Breakdown</p>
                    <span class="kpi-item-desc">Points earned by work category</span>
                </div>
                @if ($tags->sum('earned') > 0)
                    <div id="report-tag-chart" class="report-chart report-chart-sm"></div>
                @endif
                <div id="table-div">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tag</th>
                                <th class="text-center">Tasks</th>
                                <th class="text-center">Points</th>
                            </tr>
                        </thead>
                        <tbody data-show-more="6" data-show-more-label="tags">
                            @forelse ($tags as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="text-center">{{ $row['done'] }} / {{ $row['total'] }}</td>
                                    <td class="text-center">{{ $num($row['earned']) }} <span class="kpi-muted">/ {{ $num($row['assigned']) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">No tasks in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Team Performance --}}
    <div id="form-box" class="general-box">
        <div class="appraisal-section-head">
            <p id="form-sub-title" class="mb-0">Team Performance</p>
            <span class="kpi-item-desc">Teams compared by average KPI score</span>
        </div>

        {{-- Teams side by side. Status is the performance band the team's
             average falls in - Settings > Project Form Setup. --}}
        <div id="table-div" class="mb-3">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Team</th>
                        <th class="text-center">Avg. KPI</th>
                        <th class="text-center">Avg. project</th>
                        <th class="text-center">Avg. objectives</th>
                        <th class="text-center">Top performer</th>
                        <th class="text-center">Members</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody data-show-more="8" data-show-more-label="teams">
                    @forelse ($teamRows as $row)
                        <tr>
                            <td>{{ $row['team'] }}</td>
                            <td class="text-center"><strong>{{ $num($row['percentage']) }}</strong></td>
                            <td class="text-center">{{ $num($row['project'], '%') }}</td>
                            <td class="text-center">{{ $num($row['objective'], '%') }}</td>
                            <td class="text-center">
                                @if ($row['top'])
                                    <a href="{{ route('staff.view-kpi', $row['top']['staff']->id) }}" id="tb-link">{{ $row['top']['staff']->staff_name }}</a>
                                    <span class="kpi-item-desc">{{ $num($row['top']['percentage']) }}</span>
                                @else
                                    <span class="kpi-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $row['members'] }}
                                @if ($row['scored'] < $row['members'])
                                    <span class="kpi-item-desc">{{ $row['scored'] }} scored</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($row['band'])
                                    <span class="tb-status" id="tb-status-{{ $row['band']->outcome === 'Fail' ? 2 : ($row['band']->outcome === 'Pass' ? 1 : 3) }}">{{ $row['band']->label }}</span>
                                @else
                                    <span class="kpi-muted">Not scored</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No teams match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Teams compared: average project + objective points per team. --}}
        @if ($teamChart->isNotEmpty())
            <div id="report-team-chart" class="report-chart mb-0"></div>
        @endif
    </div>

    {{-- Member Ranking: every member in the filters, highest KPI score first,
         whatever their team. --}}
    <div id="form-box" class="general-box">
        <div class="appraisal-section-head">
            <p id="form-sub-title" class="mb-0">Member Ranking</p>
            <span class="kpi-item-desc">All members ranked by KPI score</span>
        </div>
        <div id="table-div">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Member</th>
                        <th class="text-center">Team</th>
                        <th class="text-center">Project</th>
                        <th class="text-center">Objectives</th>
                        <th class="text-center">KPI score</th>
                        <th class="text-center">Band</th>
                    </tr>
                </thead>
                <tbody data-show-more="10" data-show-more-label="members">
                    @forelse ($ranking as $i => $row)
                        <tr>
                            <td class="text-center">{{ $row['percentage'] === null ? '-' : $i + 1 }}</td>
                            <td>
                                <a href="{{ route('staff.view-kpi', $row['staff']->id) }}" id="tb-link">{{ $row['staff']->staff_name }}</a>
                                <span class="kpi-item-desc">{{ $row['staff']->position->position_name ?? '-' }}</span>
                            </td>
                            <td class="text-center">{{ $row['staff']->team->team_name ?? '-' }}</td>
                            <td class="text-center">{{ $num($row['project_points']) }} <span class="kpi-muted">/ {{ $num($row['project_share']) }}</span></td>
                            <td class="text-center">{{ $num($row['objective_points']) }} <span class="kpi-muted">/ {{ $num($row['objective_share']) }}</span></td>
                            <td class="text-center"><strong>{{ $num($row['percentage']) }}</strong> <span class="kpi-muted">/ 100</span></td>
                            <td class="text-center">
                                @if ($row['band'])
                                    <span class="tb-status" id="tb-status-{{ $row['band']->outcome === 'Fail' ? 2 : ($row['band']->outcome === 'Pass' ? 1 : 3) }}">{{ $row['band']->label }}</span>
                                @else
                                    <span class="kpi-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No members match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.49.0/apexcharts.min.js"></script>
    <script>
        (function () {
            'use strict';

            // Custom dates only matter for a custom period.
            var periodSelect = document.querySelector('[data-report-period]');
            periodSelect.addEventListener('change', function () {
                document.querySelectorAll('[data-report-custom]').forEach(function (el) {
                    el.hidden = periodSelect.value !== 'custom';
                });
            });

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('[data-report-member]').select2({ width: '100%', placeholder: 'All members', allowClear: true });
            }

            if (!window.ApexCharts) {
                return;
            }

            var css = getComputedStyle(document.documentElement);
            var primary = (css.getPropertyValue('--brand-primary') || '').trim() || '#6366f1';
            var palette = [primary, '#0ab39c', '#f7b84b', '#f06548', '#299cdb', '#405189', '#6559cc', '#02a8b5'];
            var base = { fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } };
            var fmt = function (v) { return v === null || v === undefined ? '-' : Math.round(v * 100) / 100; };

            var trend = @json($trend);
            new ApexCharts(document.querySelector('#report-trend-chart'), {
                chart: Object.assign({ type: 'line', height: 300 }, base),
                series: [
                    { name: 'KPI score', data: trend.map(function (m) { return m.percentage; }) },
                    { name: 'Project score %', data: trend.map(function (m) { return m.project; }) },
                    { name: 'Objective score %', data: trend.map(function (m) { return m.objective; }) },
                ],
                xaxis: { categories: trend.map(function (m) { return m.label; }) },
                yaxis: { min: 0, max: 100, tickAmount: 5, labels: { formatter: fmt } },
                stroke: { width: [3, 2, 2], curve: 'smooth', dashArray: [0, 5, 5] },
                markers: { size: 4 },
                colors: [primary, '#0ab39c', '#f7b84b'],
                tooltip: { y: { formatter: fmt } },
                legend: { position: 'top' },
                noData: { text: 'Nothing scored in this period' },
            }).render();

            var tags = @json($tagChart);
            var tagEl = document.querySelector('#report-tag-chart');
            if (tagEl && tags.length) {
                new ApexCharts(tagEl, {
                    chart: Object.assign({ type: 'donut', height: 260 }, base),
                    series: tags.map(function (t) { return t.earned; }),
                    labels: tags.map(function (t) { return t.name; }),
                    colors: palette,
                    legend: { position: 'bottom' },
                    dataLabels: { formatter: function (v) { return Math.round(v) + '%'; } },
                    tooltip: { y: { formatter: function (v) { return fmt(v) + ' pts'; } } },
                    plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'Points', formatter: function (w) {
                        return fmt(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0));
                    } } } } } },
                }).render();
            }

            var teams = @json($teamChart);
            var teamEl = document.querySelector('#report-team-chart');
            if (teamEl && teams.length) {
                new ApexCharts(teamEl, {
                    chart: Object.assign({ type: 'bar', stacked: true, height: Math.max(220, teams.length * 44 + 80) }, base),
                    series: [
                        { name: 'Avg. project points', data: teams.map(function (r) { return r.project; }) },
                        { name: 'Avg. objective points', data: teams.map(function (r) { return r.objective; }) },
                    ],
                    xaxis: { categories: teams.map(function (r) { return r.name; }), max: 100, labels: { formatter: fmt } },
                    plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3 } },
                    colors: [primary, '#0ab39c'],
                    dataLabels: { enabled: false },
                    tooltip: { y: { formatter: function (v) { return fmt(v) + ' pts'; } } },
                    legend: { position: 'top' },
                }).render();
            }
        })();
    </script>
@endpush
