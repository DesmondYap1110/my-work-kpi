 <?php
    require_once('script.php');
    $pg = "dashboard";

    //HOW MANY KPI HAVEN't APPROVE
    mysqli_select_db($KPI, $database_KPI);
    $hkpisql = "SELECT b.staff_name, c.position_name, d.p_Title, f.kojbInfo_title ,a.kpiproject_id, a.kojbInfo_id, a.kpi_id, a.createddate, a.mark FROM `project_kpi` a LEFT JOIN `staff` b ON a.staff_id = b.staff_id LEFT JOIN `staff_position` c ON b.position_id= c.position_ID LEFT JOIN `project` d ON a.project_id = d.project_id LEFT JOIN `kpi` e ON a.kpi_id = e.kpi_id LEFT JOIN `kpi_objective_info` f ON a.kojbInfo_id = f.kojbInfo_id WHERE a.status IS NULL AND a.createddate IS NOT NULL";
    $hkpi = mysqli_query($KPI,$hkpisql) or die(mysqli_error($KPI));
    $row_hkpi = mysqli_fetch_assoc($hkpi);
    $totalrow_hkpi = mysqli_num_rows($hkpi);

    //HOW MANY POSITION HAVEN't ASSIGN KPI
    mysqli_select_db($KPI, $database_KPI);
    $hpositionsql = "SELECT * FROM `staff_position` WHERE deleted='0' AND kpistatus ='0'";
    $hposition=mysqli_query($KPI,$hpositionsql) or die(mysqli_error($KPI));
    $row_hposition = mysqli_fetch_assoc($hposition);
    $totalrow_hposition = mysqli_num_rows($hposition);

    //HOW MANY TEAM
    mysqli_select_db($KPI,$database_KPI);
    $hteamsql = "SELECT * FROM `team` WHERE deleted='0' AND team_status ='1'";
    $hteam = mysqli_query($KPI,$hteamsql) or die(mysqli_error($KPI));
    $row_hteam = mysqli_fetch_assoc($hteam);
    $totalrow_hteam = mysqli_num_rows($hteam);

    //HOW MANY MEMBER
    mysqli_select_db($KPI,$database_KPI);
    $hmembersql = "SELECT * FROM `staff` WHERE deleted='0' AND staffstatus ='1' AND position_id !='1'";
    $hmember = mysqli_query($KPI,$hmembersql) or die(mysqli_error($KPI));
    $row_hmember = mysqli_fetch_assoc($hmember);
    $totalrow_hmember = mysqli_num_rows($hmember);
    
    //HOW MANY PROJECT
    mysqli_select_db($KPI,$database_KPI);
    $SQL_p = "SELECT P.*, T.team_name FROM project P LEFT JOIN team T ON P.team_id=T.team_id WHERE P.deleted = 0";
    $project = mysqli_query($KPI,$SQL_p) or die(mysqli_error($KPI));
    $row_project = mysqli_fetch_assoc($project); 
    $totalrow_project = mysqli_num_rows($project);



?>


<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
    
<head>
    <meta charset="utf-8">
    <title>Dashboard | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->

    
    <!-- COLOR THEME SECTION -->
    <?php include "inc/color-dark-theme.php"; ?>
    <!-- END OF COLOR THEME SECTION -->

    
    <style type="text/css">
        /*FEATURES SECTION*/
        #ft-section 
        {
            padding-top: 6rem;
            padding-bottom: 0.5rem;
        }
        
        #ft-box 
        {
            z-index: 1;
            display: flex;
            overflow: hidden;
            position: relative;
            align-items: center;
            border-radius: 5px;
            padding: 25px 15px;
            margin-bottom: 15px;
            transition: all 0.8s;
        }
        
        #ft-box:after {
            height: 0;
            bottom: 0;
            content: "";
            left: 0;
            width: 100%;
            position: absolute;
            z-index: -1;
            transition: all 0.8s;
        }
        
        #ft-box:hover:after 
        {
            height: 100%;
        }
        
        #ft-info-div 
        {
            margin-left: 15px;
        }
        
        #ft-title 
        {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.4;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        
        #ft-p 
        {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.4;
            margin-bottom: 0;
        }
        
        #ft-icon 
        {
            width: 4.5rem;
            height: 4.5rem;
            font-size: 26px;
            line-height: 4.5rem;
            text-align: center;
            border-radius: 50%;
        }
        
        #ft-icon i 
        {
            line-height: unset;
        }
        
        @media (max-width: 991px)
        {
            #ft-box 
            {
                padding: 25px 15px;
            }
            
            #ft-title
            {
                font-size: 13px;
            }
            
            #ft-p 
            {
                font-size: 25px;
            }
        }
    </style>
</head>
<body class="body-d">
    <div id="layout-wrapper">

        <!-- HEADER SECTION -->
        <?php include "inc/header.php"; ?>
        <!-- END OF HEADER SECTION -->

        <!-- SIDEBAR SECTION -->
        <?php include "inc/sidebar.php"; ?>
        <!-- END OF SIDEBAR SECTION -->

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">

                <!-- FEATURES SECTION -->
                    <section id="ft-section">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-4">
                                    <a id="ft-box" href="<?php echo $base_url_dashboard; ?>/manage-pending.php">
                                        <div id="ft-icon-div">
                                            <div id="ft-icon">
                                                <i class="bx ri-bar-chart-2-line"></i>
                                            </div>
                                        </div>
                                        <div id="ft-info-div">
                                            <p id="ft-title">Pending Approval of KPI</p>
                                            <p id="ft-p"><?php echo $totalrow_hkpi;?></p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-4">
                                    <a id="ft-box" href="<?php echo $base_url_dashboard; ?>/manage-kpiobjective.php">
                                        <div id="ft-icon-div">
                                            <div id="ft-icon">
                                                <i class="bx ri-user-settings-line"></i>
                                            </div>
                                        </div>
                                        <div id="ft-info-div">
                                            <p id="ft-title">Pending KPI for Position</p>
                                            <p id="ft-p"><?php echo $totalrow_hposition;?></p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-4">
                                    <a id="ft-box" href="<?php echo $base_url_dashboard; ?>/team.php">
                                        <div id="ft-icon-div">
                                            <div id="ft-icon">
                                                <i class="bx ri-team-line"></i>
                                            </div>
                                        </div>
                                        <div id="ft-info-div">
                                            <p id="ft-title">Total Team</p>
                                            <p id="ft-p"><?php echo $totalrow_hteam;?></p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-4">
                                    <a id="ft-box" href="<?php echo $base_url_dashboard; ?>/member.php">
                                        <div id="ft-icon-div">
                                            <div id="ft-icon">
                                                <i class="bx ri-user-3-line"></i>
                                            </div>
                                        </div>
                                        <div id="ft-info-div">
                                            <p id="ft-title">Total Member</p>
                                            <p id="ft-p"><?php echo $totalrow_hmember; ?></p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-4">
                                    <a id="ft-box" href="<?php echo $base_url_dashboard; ?>/manage-project.php">
                                        <div id="ft-icon-div">
                                            <div id="ft-icon">
                                                <i class="bx ri-clipboard-line"></i>
                                            </div>
                                        </div>
                                        <div id="ft-info-div">
                                            <p id="ft-title">Total Project</p>
                                            <p id="ft-p"><?php echo $totalrow_project; ?></p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </section>
                <!-- END OF FEATURES SECTION -->
              
            </div>

            <!-- FOOTER SECTION -->
            <?php include "inc/footer.php"; ?>
            <!-- END OF FOOTER SECTION -->
            
        </div>
    </div>

    <!-- FOOTER CONS SECTION -->
    <?php include "inc/footer-cons.php"; ?>
    <!-- END OF FOOTER CONS SECTION -->

</body>
</html>