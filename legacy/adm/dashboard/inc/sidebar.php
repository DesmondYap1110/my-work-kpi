<div class="app-menu navbar-menu sidebar-d">
    
    <div class="navbar-brand-box sidebar-logo-d">
        <a href="<?php echo $base_url_dashboard; ?>" class="logo logo-light">
            <span class="logo-sm"><img src="<?php echo $base_url; ?>/img/favicon-192x192.png" alt="<?php echo $base_name; ?>" title="<?php echo $base_name; ?>" class="sb-sm-logo"></span>
            <span class="logo-lg"><img src="<?php echo $base_url; ?>/img/logo.png" alt="<?php echo $base_name; ?>" title="<?php echo $base_name; ?>" class="sb-bg-logo"></span>
        </a>
        
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <div id="sb-mb-logo-div">
                <a href="<?php echo $base_url_dashboard; ?>">
                    <img src="<?php echo $base_url; ?>/img/logo.png" alt="<?php echo $base_name; ?>" title="<?php echo $base_name; ?>">
                </a>
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="nav-item">
                    <a <?php if ($pg == "dashboard") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="<?php echo $base_url_dashboard; ?>">
                        <i class="ri-dashboard-2-line"></i><span>Dashboards</span>
                    </a>
                </li>
                
                <!--Postion -->
                <li class="nav-item">
                    <a <?php if ($pg == "position") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="<?php echo $base_url_dashboard; ?>/position.php">
                        <i class="ri-user-settings-line"></i><span>Position</span>
                    </a>
                </li>
                <!--Position -->
                
                <!--Team -->
                <li class="nav-item">
                    <a <?php if ($pg == "team") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="<?php echo $base_url_dashboard; ?>/team.php">
                        <i class="ri-team-line"></i><span>Team</span>
                    </a>
                </li>
                <!--Team -->
                 
                <!--Member -->
                <li class="nav-item">
                    <a <?php if ($pg == "member") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="<?php echo $base_url_dashboard; ?>/member.php">
                        <i class="ri-user-3-line"></i><span>Member</span>
                    </a>
                </li>
                 <!--Member -->
               
           
                
                <!--Project -->
                <li class="nav-item">
                    <a <?php if ($pg == "project") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="#sb-project" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="#sb-project">
                        <i class="ri-clipboard-line"></i><span>Project</span>
                    </a>
                    <div class="collapse menu-dropdown sb-menu-d <?php if ($pg == "project"){ echo "show"; } ?>" id="sb-project">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a <?php if ($subpg == "mp") { ?>id="sb-sub-active"<?php } ?> href="<?php echo $base_url_dashboard; ?>/manage-project.php" class="nav-link">Manage Project</a>
                            </li>
                            <li class="nav-item">
                                <a <?php if ($subpg == "mpp") { ?>id="sb-sub-active"<?php } ?> href="<?php echo $base_url_dashboard; ?>/manage-projectphase.php" class="nav-link">Manage Project Phase</a>
                            </li>
                           
                        </ul>
                    </div>
                </li>
                 <!--Project -->
                
                
                 <!--KPI -->
                <li class="nav-item">
                    <a <?php if ($pg == "kpi") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="#sb-menu1" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="#sb-menu1">
                        <i class="ri-bar-chart-2-line"></i><span>KPI</span>
                    </a>
                    <div class="collapse menu-dropdown sb-menu-d <?php if ($pg == "kpi"){ echo "show"; } ?>" id="sb-menu1">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a <?php if ($subpg == "mo") { ?>id="sb-sub-active"<?php } ?> href="<?php echo $base_url_dashboard; ?>/manage-kpiobjective.php" class="nav-link">Manage KPI</a>
                            </li>
                            <li class="nav-item">
                                <a <?php if ($subpg == "mkpi") { ?>id="sb-sub-active"<?php } ?> href="<?php echo $base_url_dashboard; ?>/manage-pending.php" class="nav-link">Manage Pending</a>
                            </li>                           
                        </ul>
                    </div>
                </li>
                 <!--KPI -->
                

                <li class="nav-item">
                    <a <?php if ($pg == "setting") { ?>id="sb-active"<?php } ?> class="nav-link menu-link sb-menu-d" href="#sb-setting" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sb-setting">
                        <i class="ri-settings-5-line"></i><span>Settings</span>
                    </a>
                    <div class="collapse menu-dropdown sb-menu-d <?php if ($pg == "setting"){ echo "show"; } ?>" id="sb-setting">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a <?php if ($subpg == "file_manage") { ?>id="sb-sub-active"<?php } ?> href="<?php echo $base_url_dashboard; ?>/file-manager.php" class="nav-link">File Manager</a>
                            </li>
                            <li class="nav-item">
                                <a <?php if ($subpg == "pass") { ?>id="sb-sub-active"<?php } ?> href="<?php echo $base_url_dashboard; ?>/change-password.php" class="nav-link">Change Password</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $logoutAction;?>" class="nav-link">Log Out</a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>