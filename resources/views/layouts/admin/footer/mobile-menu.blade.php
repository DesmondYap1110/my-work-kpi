{{--
    Mobile bottom nav (shown under 991px). The active item carries id="active",
    which the theme's #mm-section li:nth-child(N)#active ~ #mm-indicator rules
    use to slide the indicator - so these five items must stay in this order
    and must not be rendered conditionally, or the indicator lands on the
    wrong entry.
--}}
<div id="mm-section">
    <ul>
        <li class="list" @if(request()->routeIs('positions.*')) id="active" @endif>
            <a href="{{ route('positions.index') }}">
                <span id="mm-icon"><i class="ri-user-settings-line"></i></span>
                <span id="mm-text">Position</span>
            </a>
        </li>
        <li class="list" @if(request()->routeIs('teams.*')) id="active" @endif>
            <a href="{{ route('teams.index') }}">
                <span id="mm-icon"><i class="ri-team-line"></i></span>
                <span id="mm-text">Team</span>
            </a>
        </li>
        <li class="list" @if(request()->routeIs('dashboard')) id="active" @endif>
            <a href="{{ route('dashboard') }}">
                <span id="mm-icon"><i class="ri-dashboard-2-line"></i></span>
                <span id="mm-text">Dashboard</span>
            </a>
        </li>
        <li class="list" @if(request()->routeIs('staff.*')) id="active" @endif>
            <a href="{{ route('staff.index') }}">
                <span id="mm-icon"><i class="ri-user-3-line"></i></span>
                <span id="mm-text">Member</span>
            </a>
        </li>
        <li class="list" @if(request()->routeIs('projects.*')) id="active" @endif>
            <a href="{{ route('projects.index') }}">
                <span id="mm-icon"><i class="ri-clipboard-line"></i></span>
                <span id="mm-text">Project</span>
            </a>
        </li>
        <div id="mm-indicator"></div>
    </ul>
</div>
