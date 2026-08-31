<?php
    require_once('script.php');
    $pg = "project";
    $subpg = "mpp";

    if(isset($_GET['ppid']) && $_GET['ppid']!=NULL)
    {
        $ppid = $_GET['ppid'];
        mysqli_select_db($KPI,$database_KPI);
        $pprojectq="SELECT a.*, b.p_Title, b.p_status , b.p_SDATE , b.p_EDate FROM project_phase a LEFT JOIN project b ON a.p_ID=b.project_id WHERE p_PID='$ppid'";
        $pproject= mysqli_query($KPI,$pprojectq) or die(mysqli_error($KPI));
        $row_pproject = mysqli_fetch_assoc($pproject);

        $csd = $row_pproject['p_SDate']; 
        $ced = $row_pproject['p_EDate'];
    }

    if(isset($_POST['edit']) && $_POST['edit']=="edit_form")
    {
        $atitle = mysqli_real_escape_string($KPI, $_POST['atitle']);
        $aptype = mysqli_real_escape_string($KPI, $_POST['aptype']);
        $sdate = mysqli_real_escape_string($KPI, $_POST['sdate']);
        $ddate = mysqli_real_escape_string($KPI, $_POST['ddate']);
        $remark = mysqli_real_escape_string($KPI, $_POST['remark']);
        $inv = mysqli_real_escape_string($KPI, $_POST['inv']);
        $ps = 1;
        
        
        if($inv!=NULL)
        {
            if($sdate>$ddate)
            {
                $togo = "projectphase-add.php?date=wrong";
                header(sprintf("Location: %s",$togo));
            }
            else
            {
                mysqli_select_db($KPI,$database_KPI);
                $addppSQL = "UPDATE `project_phase` SET p_PTitle='$atitle' , p_Type='$aptype' , p_SDate='$sdate' , p_DDate='$ddate' , p_Remark='$remark' , p_Invoice='$inv', p_ppstatus='$ps'  WHERE p_PID='$ppid'";
                $addppQuery = mysqli_query($KPI,$addppSQL) or die(mysqli_error($KPI));

                $setprogress = 3;
                $ipSQL = "UPDATE `project` SET p_status = '$setprogress' WHERE project_id='$aproject'";
                mysqli_select_db($KPI,$database_KPI);
                $editQuery = mysqli_query($KPI,$ipSQL) or die(mysqli_error($KPI));

                $togo = "manage-projectphase.php?edit=succ";
                header(sprintf("Location: %s",$togo));
            }          
        }
        else
        {
            if($sdate>$ddate)
            {
                $togo = "projectphase-edit.php?date=wrong";
                header(sprintf("Location: %s",$togo));
            }
            else
            {
                $ps = 2;
                mysqli_select_db($KPI,$database_KPI);
                $addppSQL = "UPDATE `project_phase` SET p_PTitle='$atitle' , p_Type='$aptype' , p_SDate='$sdate' , p_DDate='$ddate' , p_Remark='$remark' , p_ppstatus='$ps'  WHERE p_PID='$ppid'";
                $addppQuery = mysqli_query($KPI,$addppSQL) or die(mysqli_error($KPI));

                $togo = "manage-projectphase.php?edit=succ";
                header(sprintf("Location: %s",$togo));
            }
        }
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Edit Project Phase | <?php echo $base_name; ?></title>
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
        /*File Manager*/
        iframe {
            width: 100%;
            height: 500px;
        }
        #file-manager-div {
            display: flex;
        }
        #file-manager-div button {
            width: 12%;
            padding: 0;
            border-radius: 3px 0 0 3px;
        }
        #file-manager-div button:hover {
            transform: unset;
        }
        #file-manager-div input {
            border-radius: 0 3px 3px 0 !important;
        }
        .file-group {
            display: flex;
            align-content: center;
        }
        .file-group button {
            width: 25%;
            display: block !important;
            padding: 0 !important;
            border-radius: 0.25rem 0 0 0.25rem !important;
        }
        .file-group button:hover {
            transform: unset !important;
        }
        .file-group input {
            border-radius: 0 0.25rem 0.25rem 0 !important;
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
                    <a href="<?php echo $base_url_dashboard; ?>/manage-projectphase.php">Manage Project Phase</a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Edit Project Phase</span>
                </div>
            </section>
            <!-- END OF BREADCRUMB SECTION -->
            <div class="page-content">
                <!-- SECTION -->
                <section id="general-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6">
                                <div id="form-box" class="general-box">
                                    <form name="edit_form" method="POST" action="">
                                        <p id="form-sub-title">Edit Project Phase</p>
                                        <?php if (isset($_GET['date'])) { $check = $_GET['date']; if ($check == 'wrong') { ?>
                                            <div class="alert alert-danger" role="alert" style="margin-bottom: 15px;">
                                                <p class="alert-heading" style="line-height: 1.4; margin-bottom: 8px; font-size: 16px; font-weight: 700;"><strong>Invalid Date!</strong></p>
                                                <p class="alert-heading" style="line-height: 0.8; font-size: 13px;">Start date cannot be greater than due date</p>
                                            </div>
                                        <?php } } ?>
                                         <!-- FORM SECTION -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <label>Project</label>
                                                    <input type="text" class="form-control" id="aproject" name="aproject" maxlength="100" value="<?php echo $row_pproject['p_Title']; ?>" readonly="readonly">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Title<span>*</span></label>
                                                    <input type="text" class="form-control" id="atitle" name="atitle" maxlength="200" required value="<?php echo $row_pproject['p_PTitle'];?>" >
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Type<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="aptype" name="aptype" required>
                                                        <option  disabled value="">Select Type</option>
                                                        <option value="0" <?php if($row_pproject['p_pType'] ==  0 ){ echo "selected";}?>>Standard Phase</option>
                                                        <option value="1" <?php if($row_pproject['p_pType'] ==  1 ){ echo "selected";}?>>Amendment Phase</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Start Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="sdate" name="sdate" value="<?php echo $row_pproject['p_SDate'];?>" min="<?php echo $csd?>" max="<?php echo $ced?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Due Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="ddate" name="ddate" value="<?php echo $row_pproject['p_DDate']; ?>" min="<?php echo $csd?>" max="<?php echo $ced?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <label>Project Phase Remark<span>*</span></label>
                                                    <div class="file-group">
                                                        <button id="general-btn"  class="btn1" type="button" data-bs-toggle="modal" data-bs-target="#FILEMNMODAL1">Choose File</button>
                                                        <input type="text" class="form-control" name="remark" id="remark" maxlength="300" value="<?php echo $row_pproject['p_Remark'];?>" required> 
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <label>Invoice</label>
                                                    <div class="file-group">
                                                        <button  id="general-btn"  class="btn1" type="button" data-bs-toggle="modal" data-bs-target="#FILEMNMODAL2">Choose File</button>
                                                        <input type="text" value="<?php echo $row_pproject['p_Invoice'];?>" class="form-control" name="inv" id="inv" maxlength="300" readonly style="border: unset !important;"> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="form-btn-div">
                                                    <a href="<?php echo $base_url_dashboard; ?>/manage-project.php?uid=<?php echo $uid?>" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                                                    <input type="hidden" name="edit" value="edit_form">
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
                <!-- FILE MANAGER REMARK MODAL -->
                <div id="FILEMNMODAL1" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog modal-dialog-centered" id="fmng-dialog">
                        <div class="modal-content general-box" id="fmng-content">
                            <div class="modal-header">
                                <p id="modal-title">Upload Project Phase Remark</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div id="modal-div">
                            <iframe id="modal-iframe" src="filemanager/dialog.php?type=2&field_id=remark&relative_url=1" frameborder="0"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END OF FILE MANAGER REMARK MODAL -->
                <!-- FILE MANAGER INVOICE MODAL -->
                <div id="FILEMNMODAL2" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog modal-dialog-centered" id="fmng-dialog">
                        <div class="modal-content general-box" id="fmng-content">
                            <div class="modal-header">
                                <p id="modal-title">Upload Invoice</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div id="modal-div">
                            <iframe id="modal-iframe" src="filemanager/dialog.php?type=2&field_id=inv&relative_url=1" frameborder="0"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END OF FILE MANAGER INVOICE MODAL -->
            
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