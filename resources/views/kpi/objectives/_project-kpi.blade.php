{{--
    This position's project KPI: the marks it must earn from projects, and how
    the KPI score out of 100 is split between project work and the objectives
    on this page. Both are adjustable per position.

        Tester: target 30 project marks, project share 50
            earns 24 of 30  -> 80% of 50 = 40
            objectives 56/70 -> 80% of 50 = 40
            KPI score                     = 80 / 100

    A blank share follows the company figure on Project Setup > Weighting. A
    blank target measures project work against the tasks the member was given
    instead. See ProjectDeliveryScoreService::percentage() and
    StaffKpiScoreService::blend().

    Expects: $position, $companyProjectWeight
--}}
@php
    $ownShare = old('project_weight', $position->project_weight);
    $share = $ownShare === null || $ownShare === '' ? $companyProjectWeight : (int) $ownShare;
    $target = old('project_target', $position->project_target !== null ? rtrim(rtrim((string) $position->project_target, '0'), '.') : null);
@endphp

<div id="form-box" class="general-box mb-3">
    <form action="{{ route('kpi.weighting.update', $position->id) }}" method="POST">
        @csrf @method('PUT')

        <p id="form-sub-title" class="mb-1">Project KPI</p>
        <p id="footer-p" class="mb-3">
            Used to <strong>calculate this position's KPI score out of 100</strong>. The project
            part is calculated from the marks a member earns in projects &mdash; each task
            they complete earns the points of its tag. The rest is calculated from the KPI
            objectives on this page.
        </p>

        <div class="row">
            <div class="col-lg-4">
                <div class="input-group">
                    <label>Project marks to achieve</label>
                    <div class="weighting-field">
                        <input type="number" class="form-control" name="project_target"
                               min="0" step="0.5" value="{{ $target }}" placeholder="e.g. 30">
                        <span class="weighting-suffix">marks</span>
                    </div>
                    <span id="note-p" class="d-block kpi-weighting-note">
                        Tag points a member must earn from completed tasks to reach full project score.
                        Leave blank to measure against the tasks they were given.
                    </span>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <label>Project share of the KPI</label>
                    <div class="weighting-field">
                        <input type="number" class="form-control" name="project_weight"
                               min="0" max="100" step="1" value="{{ $ownShare }}"
                               placeholder="{{ $companyProjectWeight }}">
                        <span class="weighting-suffix">out of 100</span>
                    </div>
                    <span id="note-p" class="d-block kpi-weighting-note">
                        The KPI objectives take the rest.
                        @if ($position->project_weight === null)
                            Blank follows the company figure ({{ $companyProjectWeight }}).
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- The calculation in words, from what is saved - so it reads the
             way the score will actually be worked out. --}}
        <div class="kpi-weighting-split mb-3">
            <span class="kpi-weighting-part is-project">
                @if ($target)
                    <strong>{{ $target }} marks</strong> from projects
                @else
                    <strong>Project work</strong>
                @endif
                &rarr; counts as <strong>{{ $share }}</strong>
            </span>
            <span class="kpi-weighting-plus">+</span>
            <span class="kpi-weighting-part is-objectives">
                <strong>KPI objectives</strong> &rarr; counts as <strong>{{ 100 - $share }}</strong>
            </span>
            <span class="kpi-weighting-plus">=</span>
            <span class="kpi-weighting-part is-objectives"><strong>100</strong> KPI score</span>
        </div>

        <button type="submit" id="general-btn" class="btn1">
            <i class="ri-check-fill"></i>Save Project KPI
        </button>
    </form>
</div>
