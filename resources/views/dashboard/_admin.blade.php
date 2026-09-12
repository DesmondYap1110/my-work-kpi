{{--
    The administrator's dashboard: the shape of the company, each tile a link
    into a page the 'admin' middleware guards. Included by
    dashboard/index.blade.php - see DashboardController.
--}}
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
