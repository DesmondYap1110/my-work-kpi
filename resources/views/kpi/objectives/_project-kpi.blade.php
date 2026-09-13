{{--
    This position's project KPI: how the KPI score's 100 points are split
    between project work and the objectives on this page, and the project marks
    that earn the full project points. Both are adjustable per position.

        Tester: target 30 project marks, 50 points from projects
            earns 24 of 30  -> 80% of 50 = 40
            objectives 80%  -> 80% of 50 = 40
            KPI score                    = 80 / 100

    One slider sets the split; the example underneath is worked out live as it
    moves - see public/js/modules/kpi-split.js.

    A position never saved starts at the company figure (Project Setup >
    Weighting). A blank target measures project work against the tasks the
    member was given instead. See ProjectDeliveryScoreService::percentage()
    and StaffKpiScoreService::blend().

    Expects: $position, $companyProjectWeight
--}}
@php
    $ownShare = old('project_weight', $position->project_weight);
    $share = $ownShare === null || $ownShare === '' ? (int) $companyProjectWeight : (int) $ownShare;
    $target = old('project_target', $position->project_target !== null ? rtrim(rtrim((string) $position->project_target, '0'), '.') : null);
    $num = fn ($n) => rtrim(rtrim(number_format((float) $n, 1), '0'), '.');
@endphp

<div id="form-box" class="general-box mb-3">
    <form action="{{ route('kpi.weighting.update', $position->id) }}" method="POST"
          class="kpi-split" data-kpi-split data-company-share="{{ (int) $companyProjectWeight }}">
        @csrf @method('PUT')

        <p id="form-sub-title" class="mb-1">Project KPI</p>
        <p id="footer-p" class="mb-3">
            Every member's <strong>KPI score is out of 100 points</strong>. Slide to decide how
            many come from <strong>projects</strong> and how many from the
            <strong>KPI objectives</strong> - the two always add up to 100.
        </p>

        {{-- The split: both numbers, and the bar itself is the control - drag
             it (or use the arrow keys) to move points between the two. --}}
        <div class="kpi-split-slider">
            <div class="kpi-split-end is-project">
                <span class="kpi-split-end-label">Projects</span>
                <span class="kpi-split-end-value"><strong data-split-project>{{ $share }}</strong> pts</span>
            </div>
            <div class="kpi-split-end is-objectives">
                <span class="kpi-split-end-label">KPI objectives</span>
                <span class="kpi-split-end-value"><strong data-split-objectives>{{ 100 - $share }}</strong> pts</span>
            </div>
        </div>

        <input type="range" min="0" max="100" step="1" value="{{ $share }}" style="--split: {{ $share }}%"
               class="kpi-split-bar" aria-label="Points from projects" data-split-range>

        {{-- What gets saved: always the slider's figure. A position that has
             never been saved starts at the company figure. --}}
        <input type="hidden" name="project_weight" value="{{ $share }}" data-split-weight>

        <div class="row mt-3">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="kpi-split-card is-project h-100">
                    <p class="kpi-split-card-title">
                        Projects
                        <span class="kpi-split-card-points">
                            <input type="number" class="form-control" value="{{ $share }}" readonly tabindex="-1"
                                   aria-label="Points from projects" data-split-input="project"> pts
                        </span>
                    </p>
                    <div class="input-group mb-0">
                        <label>Marks needed for full project points</label>
                        <div class="weighting-field">
                            <input type="number" class="form-control" name="project_target"
                                   min="0" step="0.5" value="{{ $target }}" placeholder="e.g. 30" data-split-target>
                            <span class="weighting-suffix">marks</span>
                        </div>
                        <span id="note-p" class="d-block kpi-weighting-note">
                            Each task a member completes earns its tag's marks.
                            Leave blank to measure against the tasks they were given.
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="kpi-split-card is-objectives h-100">
                    <p class="kpi-split-card-title">
                        KPI objectives
                        <span class="kpi-split-card-points">
                            {{-- Read only: always the rest of 100, set with the slider. --}}
                            <input type="number" class="form-control" value="{{ 100 - $share }}" readonly tabindex="-1"
                                   aria-label="Points from KPI objectives" data-split-input="objectives"> pts
                        </span>
                    </p>
                    <p class="kpi-split-card-text mb-0">Scored from the KPI objectives below.</p>
                </div>
            </div>
        </div>

        {{-- A worked example, recalculated as the figures change. --}}
        <div class="kpi-split-example">
            <p class="kpi-split-example-title">Example: a member who achieves 80% of both</p>
            <div class="kpi-split-example-row">
                <span data-split-example-project-text>
                    @if ($target)
                        Earns {{ $num($target * 0.8) }} of {{ $num($target) }} marks
                    @else
                        Completes 80% of the task marks given
                    @endif
                </span>
                <strong><span data-split-example-project>{{ $num($share * 0.8) }}</span> pts</strong>
            </div>
            <div class="kpi-split-example-row">
                <span>Scores 80% on KPI objectives</span>
                <strong>+ <span data-split-example-objectives>{{ $num((100 - $share) * 0.8) }}</span> pts</strong>
            </div>
            <div class="kpi-split-example-row is-total">
                <span>KPI score</span>
                <strong>= 80 / 100</strong>
            </div>
        </div>

        <button type="submit" id="general-btn" class="btn1">
            <i class="ri-check-fill"></i>Save Project KPI
        </button>
    </form>
</div>
