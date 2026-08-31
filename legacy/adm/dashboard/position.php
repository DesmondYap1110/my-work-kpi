<?php
    require_once('script.php');
    $pg = "position";

    mysqli_select_db($KPI,$database_KPI);
    $query_position = "SELECT * FROM `staff_position`";
    $position = mysqli_query($KPI,$query_position) or die(mysqli_error($KPI));
    $row_position = mysqli_fetch_assoc($position);
    $totalrow_position = mysqli_num_rows($position);
    
//Add Position
    if(isset($_POST['add']) && ($_POST['add']=="add_form"))
    {
        $title = mysqli_real_escape_string($KPI, $_POST['title']);
        $jobscope = mysqli_real_escape_string($KPI, $_POST['jobscope']);
        $currenttime = date("Y-m-d H:i:s");
        
        
        $addpositionSQL = "INSERT INTO staff_position (position_name, job_scope, pcreatedate) VALUES ('$title','$jobscope','$currenttime')";
        mysqli_select_db($KPI,$database_KPI);
        $addpositionQuery = mysqli_query($KPI,$addpositionSQL) or die(mysqli_error($KPI));
        
        $togo = "position.php?add=succ";
        header(sprintf("Location: %s",$togo));
   
    }

//Edit Position
    if(isset($_POST['edit']) && ($_POST['edit']=="edit_form"))
    {
        $pid = mysqli_real_escape_string($KPI, $_POST['pid']);
        $titleedit = mysqli_real_escape_string($KPI, $_POST['title']);
        $jobscopeedit = mysqli_real_escape_string($KPI, $_POST['jobscope']);
        $edittime = date("Y-m-d H:i:s");
      
        $editpositionSQL = "UPDATE `staff_position` SET position_name='$titleedit', job_scope='$jobscopeedit', pcreatedate='$edittime' WHERE position_ID='$pid'";
        mysqli_select_db($KPI,$database_KPI);
        $editpositionQuery = mysqli_query($KPI,$editpositionSQL) or die(mysqli_error($KPI));
        
        $togo = "position.php?edit=succ";
        header(sprintf("Location: %s",$togo));
   
    }

//Delete Position
if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $pid = mysqli_real_escape_string($KPI, $_POST['pid']);
        $edittime = date("Y-m-d H:i:s");
        $setdelete= 1;
      
        $deletepositionSQL = "UPDATE `staff_position` SET  pcreatedate='$edittime' , deleted=' $setdelete' WHERE position_ID='$pid'";
        mysqli_select_db($KPI,$database_KPI);
        $editpositionQuery = mysqli_query($KPI,$deletepositionSQL) or die(mysqli_error($KPI));
        
        $togo = "position.php?delete=succ";
        header(sprintf("Location: %s",$togo));
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Position | <?php echo $base_name; ?></title>
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
                    <span id="bc-active">Position</span>
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
                                    <a href="javascript:void(0);" id="general-btn"  class="btn1" data-bs-toggle="modal" data-bs-target="#ADDModal"><i class="ri-add-fill"></i>Add Position</a>
                                </div>
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">Position List</p>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Position Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Position Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Position Edited Successfully.</p>
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
                                                        <th>Job Scope</th>
                                                        <th class="text-center">Date</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    //position_ID position_name job_scope pcreatedate
                                                        if($totalrow_position>0)
                                                        {
                                                            $number = 0;
                                                            do
                                                            {
                                                                $pid = $row_position['position_ID'];
                                                                $positionDisplay = $row_position['position_name'];
                                                                $jobscopeDisplay = $row_position['job_scope'];
                                                                $dateCreateDisplay = $row_position['pcreatedate'];
                                                                $date = date("d M Y",strtotime($dateCreateDisplay));
                                                                $time = date("H:i:s",strtotime($dateCreateDisplay));
                                                                $deletedS = $row_position['deleted'];   
                                                     ?>
                                                    <?php
                                                                if($deletedS==0)
                                                                {
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $number=$number+1?></td>
                                                        <td><?php echo $positionDisplay?></td>
                                                        <td><span id="break-word"><?php echo $jobscopeDisplay;?></span></td>
                                                        <td class="text-center"><?php echo $date; ?><span id="tb-time-p"><?php echo $time; ?></span></td>
                                                        <td class="text-center">
                                                            <div id="tb-act-btn-div">
                                                                <a href="<?php echo $base_url_dashboard; ?>/member.php?pid=<?php echo $pid;?>&teamid=" class="tb-ac-btn" id="tb-ac-btn-7" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Member List"><i class="ri-briefcase-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-1" data-bs-toggle="modal" data-bs-target="#EDITModal<?php echo $pid;?>"><i class="ri-edit-2-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-2" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $pid;?>"><i class="ri-delete-bin-6-line"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- EDIT MODAL -->
                                                    <div id="EDITModal<?php echo $pid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="edit_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Edit Position</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Title<span>*</span></label>
                                                                                    <input class="form-control" type="text" name="title" id="title" maxlength="150" value="<?php echo $positionDisplay;?>" required>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Job Scope<span>*</span></label>
                                                                                    <textarea class="form-control" name="jobscope" id="jobscope" rows="8" required style="height: unset !important; padding-top: 10px;"><?php echo $jobscopeDisplay;?></textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                                                        <input type="hidden" name="edit" value="edit_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF EDIT MODAL -->
                                                    <!--DELETE MODAL -->
                                                    <div id="DELETEModal<?php echo $pid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                    <form action="" method="POST" name="delete_form">
                                                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                            <div class="modal-content general-box" id="md-content">
                                                                <div class="modal-header">
                                                                    <p id="modal-title">Delete Position</p>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div id="modal-div">
                                                                    <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this position?</p>
                                                                </div>
                                                                <div id="modal-btn-div">
                                                                    <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                    <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                                                    <input type="hidden" name="delete" value="delete_form">
                                                                    <button id="general-btn" class="btn1">Confirm</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    </div>
                                                    <!--END OF DELETE MODAL -->
                                                    <?php            
                                                                }
                                                            }while($row_position =mysqli_fetch_assoc($position));
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
                <!-- ADD MODAL -->
                <div id="ADDModal" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                    <form action="" method="POST" name="add_form">
                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                            <div class="modal-content general-box" id="md-content">
                                <div class="modal-header">
                                    <p id="modal-title">Add Position</p>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div id="modal-div">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Title<span>*</span></label>
                                                <input class="form-control" type="text" name="title" id="title" maxlength="150" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Job Scope<span>*</span></label>
                                                <textarea class="form-control" name="jobscope" id="jobscope" rows="8" required style="height: unset !important; padding-top: 10px;"></textarea>
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