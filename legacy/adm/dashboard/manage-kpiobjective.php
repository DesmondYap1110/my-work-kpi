<?php
    require_once('script.php');
    $pg = "kpi";
    $subpg = "mo";

    mysqli_select_db($KPI,$database_KPI);
    $kpisql = "SELECT a.*, b.position_name FROM `kpi` a LEFT JOIN staff_position b ON a.position_ID=b.position_ID";
    $kpi = mysqli_query($KPI,$kpisql) or die(mysqli_error($KPI));
    $row_kpi = mysqli_fetch_assoc($kpi);
    $totalrow_kpi = mysqli_num_rows($kpi);
    
    //Add KPI
    if(isset($_POST['add']) && ($_POST['add']=="add_form"))
    {
        $title = mysqli_real_escape_string($KPI, $_POST['title']);
        $addpos = mysqli_real_escape_string($KPI, $_POST['position']);
        $currenttime = date("Y-m-d H:i:s");
        
        $addkpiSQL = "INSERT INTO kpi (kpi_date, kpi_title, position_ID) VALUES ('$currenttime','$title','$addpos')";
        mysqli_select_db($KPI,$database_KPI);
        $addkpiQuery = mysqli_query($KPI,$addkpiSQL) or die(mysqli_error($KPI));
        
        $skpi=1;
        $skpisql= "UPDATE `staff_position` SET kpistatus='$skpi' WHERE position_ID ='$addpos'";
        mysqli_select_db($KPI,$database_KPI);
        $skpiq = mysqli_query($KPI,$skpisql) or die(mysqli_error($KPI));
        
        $togo = "manage-kpiobjective.php?add=succ";
        header(sprintf("Location: %s",$togo));
    }

    //Edit KPI
    if(isset($_POST['edit']) && ($_POST['edit']=="edit_form"))
    {
        $ekid = mysqli_real_escape_string($KPI, $_POST['kid']);
        $etitle = mysqli_real_escape_string($KPI, $_POST['etitle']);
        $etime = date("Y-m-d H:i:s");
        
        $ekpiSQL = "UPDATE `kpi` SET  kpi_date='$etime' , kpi_title='$etitle' WHERE kpi_id='$ekid'";
        mysqli_select_db($KPI,$database_KPI);
        $ekpiQuery = mysqli_query($KPI,$ekpiSQL) or die(mysqli_error($KPI));
        
        $togo = "manage-kpiobjective.php?edit=succ";
        header(sprintf("Location: %s",$togo));
    }

    //Delete KPI
    if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $kiddel = mysqli_real_escape_string($KPI, $_POST['kid']);
        $deltime = date("Y-m-d H:i:s");
        $setdelete= 1;
      
        $deletekpiSQL = "UPDATE `kpi` SET  kpi_date='$deltime' , deleted='$setdelete' WHERE kpi_id='$kiddel'";
        mysqli_select_db($KPI,$database_KPI);
        $delkpiQuery = mysqli_query($KPI,$deletekpiSQL) or die(mysqli_error($KPI));
        
        $rkpi=0;
        $rkpisql= "UPDATE `staff_position` SET kpistatus='$rkpi' WHERE position_ID = (SELECT b.position_ID FROM kpi a LEFT JOIN staff_position b ON a.position_ID=b.position_ID WHERE kpi_id='$kiddel')";
        mysqli_select_db($KPI,$database_KPI);
        $rkpiq = mysqli_query($KPI,$rkpisql) or die(mysqli_error($KPI));
        
        $togo = "manage-kpiobjective.php?delete=succ";
        header(sprintf("Location: %s",$togo));
    }


?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Manage KPI | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->

    <!-- COLOR THEME SECTION -->
    <?php include "inc/color-dark-theme.php"; ?>
    <!-- END OF COLOR THEME SECTION -->
    
    <!-- DATATABLE CSS -->
    <?php include "inc/datatable-css.php"; ?>
    <!-- END OF DATATABLE CSS -->

    <style type="text/css">
        #tb-title {
            margin-bottom: 5px;
        }
        @media (max-width: 991px){
            div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                justify-content: flex-start !important;
            }
            div.dataTables_wrapper div.dataTables_length, div.dataTables_wrapper div.dataTables_filter, div.dataTables_wrapper div.dataTables_info, div.dataTables_wrapper div.dataTables_paginate {
                text-align: left;
            }
            #tb-title {
                margin-bottom: 15px;
            }
            #table-div table {
                width: 100% !important;
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
                    KPI
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Manage KPI</span>
                </div>
            </section>
            <!-- END OF BREADCRUMB SECTION -->
            <div class="page-content">
                <!-- SECTION -->
                <section id="general-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
								<div id="add-btn-div">
                                    <a href="javascript:void(0);" id="general-btn"  class="btn1" data-bs-toggle="modal" data-bs-target="#ADDKModal"><i class="ri-add-fill"></i>Add KPI</a>
                                </div>
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">KPI List</p>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Edited Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                        </div>
                                    </div>
                                    <div id="table-padding">
                                        <div id="table-div">
                                            <table id="example" class="table table-bordered nowrap table-striped align-middle">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">No</th>
                                                        <th>Title</th>
                                                        <th class="text-center">Date</th>
                                                        <th>Position</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    //team_id, team_name, createddate, team_status
                                                    if($totalrow_kpi>0)
                                                    {
                                                        $number = 0;
                                                        do
                                                        {
                                                            $kid = $row_kpi['kpi_id'];
                                                            $kpidate = $row_kpi['kpi_date'];
                                                            $date=date("d M Y",strtotime($kpidate));
                                                            $time=date("H:i:s",strtotime($kpidate));
                                                            $kpititle = $row_kpi['kpi_title'];
                                                            $positionview = $row_kpi['position_name'];
                                                            $deletedS = $row_kpi['deleted'];  
                                                     ?>
                                                     <?php
                                                            if($deletedS==0)
                                                            {
                                                     ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $number=$number+1; ?></td>
                                                        <td><?php echo $kpititle; ?></td>
                                                        <td class="text-center"><?php echo $date; ?><span id="tb-time-p"><?php echo $time; ?></span></td>
                                                        <td><?php echo $positionview; ?></td>
                                                        <td class="text-center">
                                                            <div id="tb-act-btn-div">
																<a href="<?php echo $base_url_dashboard; ?>/manage-kpiobjective-add.php?kid=<?php echo $kid;?>" class="tb-ac-btn" id="tb-ac-btn-3" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Add KPI Objective"><i class="ri-add-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-1"  data-bs-toggle="modal" data-bs-target="#EDITModal<?php echo $kid?>"><i class="ri-edit-2-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-2" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $kid?>"><i class="ri-delete-bin-6-line"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>  
                                                    <!-- EDIT MODAL -->
                                                    <div id="EDITModal<?php echo $kid?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="edit_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Edit KPI</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Title<span>*</span></label>
                                                                                    <input type="text" class="form-control" id="etitle" name="etitle" value="<?php echo $kpititle; ?>" maxlength="200" required>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Position</label>
                                                                                    <input type="text" class="form-control" maxlength="50" value="<?php echo $positionview; ?>" readonly>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="kid" value="<?php echo $kid?>">
                                                                        <input type="hidden" name="edit" value="edit_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF EDIT MODAL -->
                                                    <!-- DELETE MODAL -->
                                                    <div id="DELETEModal<?php echo $kid?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="delete_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Delete KPI</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this KPI</p>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                         <input type="hidden" name="kid" value="<?php echo $kid?>">
                                                                         <input type="hidden" name="delete" value="delete_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF DELETE MODAL -->
                                                    <?php    
                                                            }
                                                        }while($row_kpi =mysqli_fetch_assoc($kpi));
                                                    }
                                                    ?>  
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- END OF SECTION -->
                <!--ADD MODAL -->
                <div id="ADDKModal" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                    <form action="" method="POST" name="addK_form">
                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                            <div class="modal-content general-box" id="md-content">
                                <div class="modal-header">
                                    <p id="modal-title">Add KPI</p>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div id="modal-div">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Title<span>*</span></label>
                                                <input type="text" class="form-control" id="title" name="title" maxlength="200" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Position<span>*</span></label>
                                                <select class="form-control" id="position" name="position" required>
                                                    <option selected disabled value="">Select Position</option>
                                                    <?php 
                                                        if($totalrow_position>0)
                                                        {
                                                            do
                                                            {
                                                                $pid = $row_position['position_ID'];
                                                                $positionDisplay = $row_position['position_name'];
                                                                $issetkpi = $row_position['kpistatus'];
                                                                $deletedS = $row_position['deleted'];
                                                                if($deletedS==0 && $issetkpi==0 )
                                                                {
                                                    ?>
                                                    <option value="<?php echo $pid; ?>"><?php echo $positionDisplay; ?></option>
                                                    <?php 
                                                                } 
                                                            }while($row_position=mysqli_fetch_assoc($position));
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="modal-btn-div">
                                    <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                    <input type="hidden" name="add" value="add_form">
                                    <button id="general-btn" class="btn1">Confirm</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- END OF ADD MODAL -->
            </div>
            <!-- FOOTER SECTION -->
            <?php include "inc/footer.php"; ?>
            <!-- END OF FOOTER SECTION -->
        </div>
    </div>
    <!-- FOOTER CONS SECTION -->
    <?php include "inc/footer-cons.php"; ?>
    <!-- END OF FOOTER CONS SECTION -->
    
    <!-- DATATABLE JS -->
    <?php include "inc/datatable-js.php"; ?>
    <!-- END OF DATATABLE JS -->

</body>
</html>