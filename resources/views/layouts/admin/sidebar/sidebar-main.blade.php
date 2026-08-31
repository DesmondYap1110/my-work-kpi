<div class="app-menu navbar-menu sidebar-d">
    <div class="navbar-brand-box sidebar-logo-d">
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><img src="{{ asset('images/logo-sm.svg') }}" class="sb-sm-logo" alt="MyKPI"></span>
            <span class="logo-lg"><img src="{{ asset('images/logo-light.svg') }}" class="sb-bg-logo" alt="MyKPI"></span>
        </a>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">
                <x-sidebar.ui.list route="dashboard" icon="ri-dashboard-2-line" label="Dashboard" />
                <x-sidebar.ui.list route="positions.index" icon="ri-user-settings-line" label="Position" active="positions.*" />
                <x-sidebar.ui.list route="teams.index" icon="ri-team-line" label="Team" active="teams.*" />
                <x-sidebar.ui.list route="staff.index" icon="ri-user-3-line" label="Member" active="staff.*" />

                <x-sidebar.ui.dropdown id="sb-project" icon="ri-clipboard-line" label="Project"
                                       :active="['projects.*', 'project-phases.*']">
                    <x-sidebar.ui.dropdown-list route="projects.index" label="Manage Project" active="projects.*" />
                    <x-sidebar.ui.dropdown-list route="project-phases.index" label="Manage Project Phase" active="project-phases.*" />
                </x-sidebar.ui.dropdown>

                <x-sidebar.ui.dropdown id="sb-kpi" icon="ri-bar-chart-2-line" label="KPI"
                                       :active="['kpi.*', 'manage-pending.*']">
                    <x-sidebar.ui.dropdown-list route="kpi.index" label="Manage KPI" active="kpi.*" />
                    <x-sidebar.ui.dropdown-list route="manage-pending.index" label="Manage Pending" active="manage-pending.*" />
                </x-sidebar.ui.dropdown>

                <x-sidebar.ui.dropdown id="sb-settings" icon="ri-settings-3-line" label="Settings"
                                       :active="['password.change']">
                    <x-sidebar.ui.dropdown-list route="password.change" label="Change Password" />
                    <x-sidebar.ui.dropdown-list>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">Log Out</button>
                        </form>
                    </x-sidebar.ui.dropdown-list>
                </x-sidebar.ui.dropdown>
            </ul>
        </div>
    </div>
</div>

<div class="vertical-overlay"></div>
