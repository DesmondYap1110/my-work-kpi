<?php
 
    $memberaccSQL= "SELECT S.*, P.position_name, T.team_name FROM staff S LEFT JOIN staff_position P ON S.position_id=P.position_ID LEFT JOIN team T ON S.team_id=T.team_id WHERE S.staff_id = $user_ID";
    $memberacc = mysqli_query($KPI,$memberaccSQL) or die(mysqli_error($KPI));
    $row_memberacc = mysqli_fetch_assoc($memberacc); 
    $totalrow_memberacc = mysqli_num_rows($memberacc);

?>
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
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>
                <div class="dropdown header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <div id="hd-arrow-div">
                                <img src="<?php echo $base_url; ?>/img/arrow-down.png">
                            </div>
                            <span class="text-start ms-xl-2">
                                <p id="hd-username"><?php echo $row_memberacc['email']; ?></p>
                                <p id="hd-position">Admin</p>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="<?php echo $base_url_dashboard; ?>/change-password.php">
                            <i class="ri-lock-line fs-16 align-middle me-1"></i> <span class="align-middle">Change Password</span>
                        </a>
                        <a class="dropdown-item" href="<?php echo $logoutAction;?>">
                            <i class="mdi mdi-logout fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>