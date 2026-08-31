<?php
    require_once('script.php');
    $pg = "project";
    $subpg = "mp";

    if(isset($_GET['pid']) && $_GET['pid']!=NULL)
    {
        $pid = $_GET['pid'];
        mysqli_select_db($KPI,$database_KPI);
        $projectq="SELECT * FROM `project` WHERE project_id='$pid'";
        $project= mysqli_query($KPI,$projectq) or die(mysqli_error($KPI));
        $row_project = mysqli_fetch_assoc($project);
    }

    if(isset($_POST['edit']) && ($_POST['edit']=="edit_form"))
    {
        $p_Title = mysqli_real_escape_string($KPI, $_POST['title']);
        $p_addDate = mysqli_real_escape_string($KPI, $_POST['date']);
        $p_SDate = mysqli_real_escape_string($KPI, $_POST['sdate']);
        $p_EDate = mysqli_real_escape_string($KPI,$_POST['edate']);
        $team_id = mysqli_real_escape_string($KPI, $_POST['team']);
        $p_status = mysqli_real_escape_string($KPI, $_POST['df'])?1:0;
        
        if($p_status==0)
        {
            if($p_addDate>$p_SDate ||$p_addDate>$p_EDate||$p_SDate>$p_EDate)
            {
                $togo = "project-edit.php?pid=$pid&date=wrong";
                header(sprintf("Location: %s",$togo));
            }
            else
            {
                mysqli_select_db($KPI,$database_KPI);
                $addprojectsql = "UPDATE `project` SET p_Title='$p_Title' , p_addDate='$p_addDate' , p_SDate = '$p_SDate' , p_EDate = '$p_EDate' , team_id='$team_id' WHERE project_id='$pid'";
                $addprojectquery = mysqli_query($KPI,$addprojectsql) or die(mysqli_error($KPI));
                $togo = "manage-project.php?edit=succ";
                header(sprintf("Location: %s",$togo));
            }
        }
        elseif($p_status==1)
        {
            $p_status=2;
            if($p_addDate>$p_SDate ||$p_addDate>$p_EDate||$p_SDate>$p_EDate)
            {
                $togo = "project-edit.php?pid=$pid&date=wrong";
                header(sprintf("Location: %s",$togo));
            }
            else
            {
                
               $completedate = date("Y-m-d H:i:s");
               mysqli_select_db($KPI,$database_KPI);
               $addprojectsql = "UPDATE `project` SET p_Title='$p_Title' , p_addDate='$p_addDate' , p_SDate = '$p_SDate' , p_EDate = '$p_EDate' , team_id='$team_id', p_status = '$p_status' , complete_date = '$completedate' WHERE project_id='$pid'";
               $addprojectquery = mysqli_query($KPI,$addprojectsql) or die(mysqli_error($KPI));
                
                
                // ONCE SET COMPLETE STATUS FOR PROJECT. 
                // IT WILL AUTOMATICALLY TRIGGET SELF ADD KPI FORM FOR THE MEMBER THAT INVOLVE IN THIS PROJECT.
                // PRECONDITION: Select staff id , kpi id of the teammember involve in project
                mysqli_select_db($KPI,$database_KPI);
                $memberinvolvesql = "SELECT a.staff_id , d.kpi_id FROM `staff` a LEFT JOIN `team` b ON a.team_id = b.team_id LEFT JOIN `staff_position` c ON a.position_id = c.position_ID LEFT JOIN `kpi` d ON d.position_ID = a.position_id WHERE a.team_id ='$team_id' AND a.deleted ='0' AND a.staffstatus ='1'";
                $memberinvolve = mysqli_query($KPI,$memberinvolvesql) or die(mysqli_error($KPI));
                $row_memberinvolve = mysqli_fetch_assoc($memberinvolve);
                $totalrow_memberinvolve = mysqli_num_rows($memberinvolve);
                
                
                if($totalrow_memberinvolve>0)
                {
                    do
                    {
                        //Use the staff_id and kpi_id to select the kpi_objective 
                        $staff_id = $row_memberinvolve['staff_id'];
                        $kpi_id = $row_memberinvolve['kpi_id'];
        
                        
                        mysqli_select_db($KPI, $database_KPI);
                        $query_obj = "SELECT a.kojbInfo_id FROM `kpi_objective` a LEFT JOIN `kpi_objective_info` b ON a.kojbInfo_id = b.kojbInfo_id WHERE a.kpi_ID='$kpi_id'";
                        $obj = mysqli_query($KPI, $query_obj) or die(mysqli_error($KPI));
                        $row_obj = mysqli_fetch_assoc($obj);
                        $totalrow_obj = mysqli_num_rows($obj);
                        
                        if($totalrow_obj > 0)
                        {
                            do  
                            {
                                $kpiobjinfoid=$row_obj['kojbInfo_id'];
                                mysqli_select_db($KPI,$database_KPI);
                                $selfkpisql = "INSERT INTO `project_kpi` (staff_id , project_id , kpi_id , kojbInfo_id) VALUES ('$staff_id','$pid','$kpi_id',' $kpiobjinfoid')";
                                $selfkpi = mysqli_query($KPI, $selfkpisql) or die(mysqli_error($KPI));
                            }
                            while($row_obj = mysqli_fetch_assoc($obj));
                        }
                    }
                    while($row_memberinvolve = mysqli_fetch_assoc($memberinvolve));  
                }
                
                $togo = "manage-project.php?edit=succ";
                header(sprintf("Location: %s",$togo));
            }
        }
       
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Edit Project | <?php echo $base_name; ?></title>
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
                    <span id="bc-active">Edit Project</span>
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
                                     <!-- FORM SECTION -->
                                    <form name="edit_form" method="POST" action="">
                                        <p id="form-sub-title">Edit Project</p>
                                        <?php if (isset($_GET['date'])) { $check = $_GET['date']; if ($check == 'wrong') { ?>
                                            <div class="alert alert-danger" role="alert" style="margin-bottom: 15px;">
                                                <p class="alert-heading" style="line-height: 1.4; margin-bottom: 8px; font-size: 16px; font-weight: 700;"><strong>Invalid Date!</strong></p>
                                                <p class="alert-heading" style="line-height: 0.8; font-size: 13px;">1. Create date cannot be greater than start date or end date</p> 
                                                <p class="alert-heading" style="line-height: 0.8; font-size: 13px;">2. Start date cannot be greater than end date</p>
                                            </div>
                                        <?php } } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                                                    <?php
                                                    if($row_project['p_status']!=2)
                                                    {
                                                    ?>
                                                    <input class="form-check-input" type="checkbox" role="switch" id="defaultbox" name="df">
                                                    <?php
                                                    }
                                                    ?>
                                                    <label class="form-check-label" for="defaultbox">Status</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Title<span>*</span></label>
                                                    <input type="text" class="form-control" id="title" name="title" maxlength="200" value="<?php echo $row_project['p_Title']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="date" name="date" value="<?php echo $row_project['p_addDate']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Start Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="sdate" name="sdate" value="<?php echo $row_project['p_SDate']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>End Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="edate" name="edate" value="<?php echo $row_project['p_EDate']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Team<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="team" name="team">
                                                        <option  disabled>Select Team</option>
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
                                                        <option value="<?php echo $tid;?>" <?php if($row_project['team_id'] ==  $tid ){ echo "selected";}?> ><?php echo $teamDisplay;?></option>
                                                        <?php 
                                                                    } 
                                                                }while($row_team=mysqli_fetch_assoc($team));
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div id="form-btn-div">
                                                    <a href="<?php echo $base_url_dashboard; ?>/manage-project.php?uid=<?php echo $uid?>" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                                                    <input type="hidden" name="edit" value="edit_form">
                                                    <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-fill"></i>Save Changes</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                     <!-- FORM SECTION -->
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