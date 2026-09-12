<header id="page-topbar" class="header-d">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex" id="hd-menu-btn">
                <button type="button" class="btn btn-sm fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon"><span></span><span></span><span></span></span>
                </button>
            </div>
            <div class="d-flex align-items-center">
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>
                <div class="dropdown header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <div id="hd-arrow-div">
                                <img src="{{ asset('assets/img/arrow-down.png') }}" alt="">
                            </div>
                            <span class="text-start ms-xl-2">
                                <p id="hd-username">{{ auth()->user()->email }}</p>
                                {{-- Was hard-coded "Admin", from when the
                                     administrator was the only person who
                                     could sign in. --}}
                                <p id="hd-position">{{ auth()->user()->position->position_name ?? 'Member' }}</p>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('password.change') }}">
                            <i class="ri-lock-line fs-16 align-middle me-1"></i> <span class="align-middle">Change Password</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="mdi mdi-logout fs-16 align-middle me-1"></i> <span class="align-middle">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
