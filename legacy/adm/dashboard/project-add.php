<?php
    require_once('script.php');
    $pg = "project";
    $subpg = "mp";

    if(isset($_POST['add']) && ($_POST['add']=="add_form"))
    {
        $p_Title = mysqli_real_escape_string($KPI, $_POST['title']);
        $p_addDate = mysqli_real_escape_string($KPI, $_POST['date']);
        $p_SDate = mysqli_real_escape_string($KPI, $_POST['sdate']);
        $p_EDate = mysqli_real_escape_string($KPI,$_POST['edate']);
        $team_id = mysqli_real_escape_string($KPI, $_POST['team']);
        $date_assign =date("Y-m-d");
        $p_status = 1;
        if($p_addDate>$p_SDate ||$p_addDate>$p_EDate||$p_SDate>$p_EDate)
        {
            $togo = "project-add.php?date=wrong";
            header(sprintf("Location: %s",$togo));
        }
        else
        {
            mysqli_select_db($KPI,$database_KPI);
            $addprojectsql = "INSERT INTO project (p_Title , p_addDate , p_SDate , p_EDate , team_id , date_assign , p_status) VALUES ('$p_Title','$p_addDate','$p_SDate','$p_EDate','$team_id','$date_assign','$p_status')";
            $addprojectquery = mysqli_query($KPI,$addprojectsql) or die(mysqli_error($KPI));
            $togo = "manage-project.php?add=succ";
            header(sprintf("Location: %s",$togo));
        }
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Add Project | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->

    <!-- COLOR THEME SECTION -->
    <?php include "inc/color-dark-theme.php"; ?>
    <!-- END OF COLOR THEME SECTION -->

    <style type="text/css">
        #tb-title {
            margin-bottom: 5px;
        }
        @media (max-width: 991px){
            #tb-title {
                margin-bottom: 15px;
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
            <!-- BREADCRUMB SECTION -->
            <section id="bc-section">
                <div id="bc-div">
                    <a href="<?php echo $base_url_dashboard; ?>"><i class="ri-dashboard-2-line"></i></a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    Project
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <a href="<?php echo $base_url_dashboard; ?>/manage-project.php">Manage Project</a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Add Project</span>
                </div>
            </section>
            <!-- END OF BREADCRUMB SECTION -->
 
            <div class="page-content">
                <!-- SECTION -->
                <section id="general-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-8">
                                <div id="form-box" class="general-box">
                                    <form name="add_form" method="POST" action="">
                                        <p id="form-sub-title">Add Project</p>
                                        <?php if (isset($_GET['date'])) { $check = $_GET['date']; if ($check == 'wrong') { ?>
                                            <div class="alert alert-danger" role="alert" style="margin-bottom: 15px;">
                                                <p class="alert-heading" style="line-height: 1.4; margin-bottom: 8px; font-size: 16px; font-weight: 700;"><strong>Invalid Date!</strong></p>
                                                <p class="alert-heading" style="line-height: 0.8; font-size: 13px;">1. Create date cannot be greater than start date or end date</p> 
                                                <p class="alert-heading" style="line-height: 0.8; font-size: 13px;">2. Start date cannot be greater than end date</p>
                                            </div>
                                        <?php } } ?>
                                         <!-- FORM SECTION -->
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Title<span>*</span></label>
                                                    <input type="text" class="form-control" id="title" name="title" maxlength="200" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="date" name="date" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Start Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="sdate" name="sdate" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>End Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="edate" name="edate" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Team<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="team" name="team" required>
                                                        <option selected disabled value="">Select Team</option>
                                                        <?php 
                                                            if($totalrow_team>0)
                                                            {
                                                                do
                                                                {
                                                                    $tid = $row_team['team_id'];
                                                                    $teamDisplay = $row_team['team_name'];
                                                                    $deletedS = $row_team['deleted'];
                                                                    $teamS = $row_team['team_status'];
                                                                    //#################THE PROJECT ONLY ASSIGN TO THE TEAM THAT CONSISTS MEMBER
                                                                    mysqli_select_db($KPI,$database_KPI);
                                                                    $numsql="SELECT COUNT(staff_id) AS Number FROM team T RIGHT JOIN staff S ON T.team_id=S.team_id WHERE S.team_id='$tid' GROUP BY S.team_id";
                                                                    $numquery = mysqli_query($KPI,$numsql) or die(mysqli_error($KPI));
                                                                    $num = mysqli_fetch_assoc($numquery);
                                                                    //#################THE PROJECT ONLY ASSIGN TO THE TEAM THAT HAVE MEMBER
                                                                    if($deletedS==0 && $teamS==1 && $num['Number']>0)
                                                                    {
                                                        ?>
                                                        <option value="<?php echo $tid;?>"><?php echo $teamDisplay;?></option>
                                                        <?php 
                                                                    } 
                                                                }while($row_team=mysqli_fetch_assoc($team));
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="form-btn-div">
                                                    <a href="<?php echo $base_url_dashboard; ?>/manage-project.php?uid=<?php echo $uid?>" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                                                    <input type="hidden" name="add" value="add_form">
                                                    <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-fill"></i>Save Changes</button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- FORM SECTION -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- END OF SECTION -->
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