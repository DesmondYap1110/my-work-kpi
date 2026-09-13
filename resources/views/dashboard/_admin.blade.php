{{--
    The administrator's dashboard: the shape of the company, each tile a link
    into a page the 'admin' middleware guards. Included by
    dashboard/index.blade.php - see DashboardController.
--}}
    {{-- Appraisals overdue or due within a week, from each member's review
         cycle - see App\Services\AppraisalScheduleService. Only shown when
         there is something to act on. --}}
    @if ($appraisalsDue->isNotEmpty())
        @php $overdueCount = $appraisalsDue->where('state', 'overdue')->count(); @endphp
        <div id="form-box" class="general-box appraisal-due-notice {{ $overdueCount ? 'is-overdue' : 'is-soon' }}">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <p id="form-sub-title" class="mb-0 d-flex align-items-center gap-2">
                    <i class="ri-notification-3-line"></i>
                    Appraisals due
                    @if ($overdueCount)
                        <span class="tb-status" id="tb-status-2">{{ $overdueCount }} overdue</span>
                    @endif
                    @if ($appraisalsDue->count() - $overdueCount)
                        <span class="tb-status" id="tb-status-3">{{ $appraisalsDue->count() - $overdueCount }} due soon</span>
                    @endif
                </p>
                <a href="{{ route('appraisals.schedule', ['state' => $overdueCount ? 'overdue' : 'soon']) }}" id="general-btn" class="btn1">
                    <i class="ri-survey-line"></i>View Schedule
                </a>
            </div>
            <ul class="appraisal-due-list mb-0">
                @foreach ($appraisalsDue->take(5) as $row)
                    <li>
                        <strong>{{ $row['staff']->staff_name }}</strong>
                        <span class="kpi-item-desc">{{ $row['staff']->position->position_name ?? '' }} &middot; every {{ strtolower($row['cycle']->label()) }}</span>
                        <span class="appraisal-due-when">
                            {{ $row['due']->format('d M Y') }}
                            @if ($row['state'] === 'overdue')
                                <span class="tb-status" id="tb-status-2">Overdue {{ abs($row['days']) }} {{ Str::plural('day', abs($row['days'])) }}</span>
                            @else
                                <span class="tb-status" id="tb-status-3">{{ $row['days'] === 0 ? 'Due today' : 'Due in '.$row['days'].' '.Str::plural('day', $row['days']) }}</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
            @if ($appraisalsDue->count() > 5)
                <p id="footer-p" class="mb-0 mt-2">and {{ $appraisalsDue->count() - 5 }} more on the Schedule.</p>
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('manage-pending.index') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-bar-chart-2-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Pending Approval of KPI</p>
                    <p id="ft-p">{{ $pendingKpiCount }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('positions.index') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-user-settings-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Pending KPI for Position</p>
                    <p id="ft-p">{{ $positionsWithoutKpiCount }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('teams.index') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-team-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Total Team</p>
                    <p id="ft-p">{{ $activeTeamsCount }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('staff.index') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-user-3-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Total Member</p>
                    <p id="ft-p">{{ $activeMembersCount }}</p>
                </div>
            </a>
        </div>
        <div class="col-lg-4">
            <a id="ft-box" href="{{ route('projects.index') }}">
                <div id="ft-icon-div">
                    <div id="ft-icon">
                        <i class="bx ri-clipboard-line"></i>
                    </div>
                </div>
                <div id="ft-info-div">
                    <p id="ft-title">Total Project</p>
                    <p id="ft-p">{{ $totalProjectsCount }}</p>
                </div>
            </a>
        </div>
    </div>
