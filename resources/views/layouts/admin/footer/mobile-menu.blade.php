{{--
    Mobile bottom nav (shown under 991px).

    Administrator: Member, Project, Dashboard, Report, Appraisal - Dashboard
    in the middle. Colour: the Mobile menu colour in Settings > Theme Setting.

    The active item carries id="active", which the theme's
    #mm-section li:nth-child(N)#active ~ #mm-indicator rules use to slide the
    indicator - so the administrator's five items must stay in this order and
    must not be rendered conditionally, or the indicator lands on the wrong
    entry. Staff get their own, shorter bar instead of a trimmed-down copy of
    that one, and no indicator: the theme has no rule for a three-item slide,
    and li#active still colours the icon and label on its own.
--}}
<div id="mm-section">
    @if (auth()->user()?->isAdmin())
        <ul>
            <li class="list" @if(request()->routeIs('staff.*')) id="active" @endif>
                <a href="{{ route('staff.index') }}">
                    <span id="mm-icon"><i class="ri-user-3-line"></i></span>
                    <span id="mm-text">Member</span>
                </a>
            </li>
            <li class="list" @if(request()->routeIs('projects.*', 'project-tasks.*')) id="active" @endif>
                <a href="{{ route('projects.index') }}">
                    <span id="mm-icon"><i class="ri-clipboard-line"></i></span>
                    <span id="mm-text">Project</span>
                </a>
            </li>
            <li class="list" @if(request()->routeIs('dashboard')) id="active" @endif>
                <a href="{{ route('dashboard') }}">
                    <span id="mm-icon"><i class="ri-dashboard-2-line"></i></span>
                    <span id="mm-text">Dashboard</span>
                </a>
            </li>
            <li class="list" @if(request()->routeIs('kpi-report.*')) id="active" @endif>
                <a href="{{ route('kpi-report.index') }}">
                    <span id="mm-icon"><i class="ri-bar-chart-2-line"></i></span>
                    <span id="mm-text">Report</span>
                </a>
            </li>
            <li class="list" @if(request()->routeIs('appraisals.*')) id="active" @endif>
                <a href="{{ route('appraisals.index') }}">
                    <span id="mm-icon"><i class="ri-survey-line"></i></span>
                    <span id="mm-text">Appraisal</span>
                </a>
            </li>
            <div id="mm-indicator"></div>
        </ul>
    @else
        <ul>
            <li class="list" @if(request()->routeIs('my.kpi')) id="active" @endif>
                <a href="{{ route('my.kpi') }}">
                    <span id="mm-icon"><i class="ri-bar-chart-2-line"></i></span>
                    <span id="mm-text">My KPI</span>
                </a>
            </li>
            <li class="list" @if(request()->routeIs('dashboard')) id="active" @endif>
                <a href="{{ route('dashboard') }}">
                    <span id="mm-icon"><i class="ri-dashboard-2-line"></i></span>
                    <span id="mm-text">Dashboard</span>
                </a>
            </li>
            <li class="list" @if(request()->routeIs('my.tasks')) id="active" @endif>
                <a href="{{ route('my.tasks') }}">
                    <span id="mm-icon"><i class="ri-list-check-2"></i></span>
                    <span id="mm-text">My Tasks</span>
                </a>
            </li>
        </ul>
    @endif
</div>
