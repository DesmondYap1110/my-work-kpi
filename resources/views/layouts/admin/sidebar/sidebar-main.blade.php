<div class="app-menu navbar-menu sidebar-d">
    <div class="navbar-brand-box sidebar-logo-d">
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><img src="{{ \App\Support\Branding::logo('mobile') }}" class="sb-sm-logo" alt="{{ \App\Support\Branding::name() }}"></span>
            <span class="logo-lg"><img src="{{ \App\Support\Branding::logo('sidebar') }}" class="sb-bg-logo" alt="{{ \App\Support\Branding::name() }}"></span>
        </a>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">
                <x-sidebar.ui.list route="dashboard" icon="ri-dashboard-2-line" label="Dashboard" />

                {{-- Member is the most-visited page, so it keeps a shortcut of
                     its own as well as its place in the group below. --}}
                <x-sidebar.ui.list route="staff.index" icon="ri-user-3-line" label="Member" active="staff.*" />

                <x-sidebar.ui.dropdown id="sb-hr" icon="ri-group-line" label="Human Resource"
                                       :active="['positions.*', 'teams.*', 'staff.*']">
                    <x-sidebar.ui.dropdown-list route="teams.index" label="Team" active="teams.*" />
                    <x-sidebar.ui.dropdown-list route="positions.index" label="Position" active="positions.*" />
                    <x-sidebar.ui.dropdown-list route="staff.index" label="Member" active="staff.*" />
                </x-sidebar.ui.dropdown>

                <x-sidebar.ui.dropdown id="sb-project" icon="ri-clipboard-line" label="Project"
                                       :active="['projects.*', 'project-phases.*']">
                    <x-sidebar.ui.dropdown-list route="projects.index" label="Manage Project" active="projects.*" />
                    <x-sidebar.ui.dropdown-list route="project-phases.index" label="Manage Project Phase" active="project-phases.*" />
                </x-sidebar.ui.dropdown>

                <x-sidebar.ui.dropdown id="sb-kpi" icon="ri-bar-chart-2-line" label="KPI"
                                       :active="['kpi.*', 'manage-pending.*']">
                    <x-sidebar.ui.dropdown-list route="manage-pending.index" label="Manage Pending" active="manage-pending.*" />
                </x-sidebar.ui.dropdown>

                <x-sidebar.ui.dropdown id="sb-settings" icon="ri-settings-3-line" label="Settings"
                                       :active="['password.change', 'project-tags.*']">
                    <x-sidebar.ui.dropdown-list route="project-tags.index" label="Project Tags" active="project-tags.*" />
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
