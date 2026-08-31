<?php
    require_once('script.php');
    $pg = "team";
    
    mysqli_select_db($KPI,$database_KPI);
    $SQL_team = "SELECT * FROM `team`";
    $team = mysqli_query($KPI,$SQL_team) or die(mysqli_error($KPI));
    $row_team = mysqli_fetch_assoc($team);
    $totalrow_team = mysqli_num_rows($team);

//add team
    if(isset($_POST['add']) && $_POST['add']=="add_form")
    {
        $teamname = mysqli_real_escape_string($KPI,$_POST['name']);
        $currentdate = date("Y-m-d H:i:s");
        $teamstatus = 1;
        
        mysqli_select_db($KPI,$database_KPI);
        $addSQL = "INSERT INTO team (team_name, createddate, team_status) VALUES ('$teamname','$currentdate', '$teamstatus')";
        $addQuery = mysqli_query($KPI,$addSQL) or die(mysqli_error($KPI));
        $togo = "team.php?add=succ";
        header(sprintf("Location: %s",$togo));
        
    }

//edit team
    if(isset($_POST['edit']) && ($_POST['edit']=="edit_form"))
    {
        $tid = mysqli_real_escape_string($KPI, $_POST['tid']);
        $nameedit = mysqli_real_escape_string($KPI, $_POST['name']);
        $statusedit = mysqli_real_escape_string($KPI, $_POST['df'])?1:0;
        $edittime = date("Y-m-d H:i:s");
        $editteamSQL = "UPDATE `team` SET team_name='$nameedit', createddate='$edittime', team_status='$statusedit' WHERE team_id='$tid'";
        mysqli_select_db($KPI,$database_KPI);
        $editteamQuery = mysqli_query($KPI,$editteamSQL) or die(mysqli_error($KPI));
        
        $togo = "team.php?edit=succ";
        header(sprintf("Location: %s",$togo));
   
    }

//block team
    if(isset($_POST['block']) && $_POST['block']=="block_form")
    {
        
        $currenttid = mysqli_real_escape_string($KPI,$_POST['tid']);
        
        mysqli_select_db($KPI,$database_KPI);
        $checkstSQL = "SELECT * FROM `team` WHERE team_id ='$currenttid'";
        $checkQuery = mysqli_query($KPI,$checkstSQL) or die(mysqli_error($KPI));
        $row_check = mysqli_fetch_assoc($checkQuery);
        $totalrow_check = mysqli_num_rows($checkQuery);
        
        $blocktime=date("Y-m-d H:i:s");
        
        if($totalrow_check>0)
        {
            $status=$row_check['team_status'];
            if($status==1)
            {
                $updateSt=0;

            }
            elseif($status==0)
            {
                $updateSt=1;
            }
            mysqli_select_db($KPI,$database_KPI);
            $blockSQL = "UPDATE `team` SET team_status='$updateSt' , createddate=' $blocktime' WHERE team_id ='$currenttid'";
            $blockQuery = mysqli_query($KPI,$blockSQL) or die(mysqli_error($KPI));
            $togo = "team.php?edit=succ";
            header(sprintf("Location: %s",$togo));
            
        }
        
    }

//Delete Position
    if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $tid = mysqli_real_escape_string($KPI, $_POST['tid']);
        $edittime = date("Y-m-d H:i:s");
        $setdelete= 1;
      
        $deleteteamSQL = "UPDATE `team` SET  createddate='$edittime' , deleted=' $setdelete' WHERE team_id='$tid'";
        mysqli_select_db($KPI,$database_KPI);
        $editpositionQuery = mysqli_query($KPI,$deleteteamSQL) or die(mysqli_error($KPI));
        
        $togo = "team.php?delete=succ";
        header(sprintf("Location: %s",$togo));
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Team | <?php echo $base_name; ?></title>
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
                width: 150% !important;
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
                    <span id="bc-active">Team</span>
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
                                    <a href="javascript:void(0);" id="general-btn"  class="btn1" data-bs-toggle="modal" data-bs-target="#ADDModal"><i class="ri-add-fill"></i>Add Team</a>
                                </div>
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">Team List</p>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Team Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Team Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Team Edited Successfully.</p>
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
                                                        <th>Name</th>
                                                        <th class="text-center">Created Date</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    //team_id, team_name, createddate, team_status
                                                    if($totalrow_team>0)
                                                    {
                                                        $number = 0;
                                                        do
                                                        {
                                                            $tid = $row_team['team_id'];
                                                            $teamDisplay = $row_team['team_name'];
                                                            $dateDisplay = $row_team['createddate'];
                                                            $date = date("d M Y",strtotime($dateDisplay));
                                                            $time = date("H:i:s",strtotime($dateDisplay));
                                                            $statusDisplay = $row_team['team_status'];
                                                            $deletedS = $row_team['deleted'];  
                                                     ?>
                                                     <?php
                                                            if($deletedS==0)
                                                            {
                                                     ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $number=$number+1?></td>
                                                        <td><?php echo $teamDisplay ?></td>
                                                        <td class="text-center"><?php echo $date; ?><span id="tb-time-p"><?php echo $time; ?></span></td>
                                                        <td class="text-center">
                                                            <?php 
                                                            if($statusDisplay == 1)
                                                            {
                                                            ?>
                                                            <div id="tb-status-btn-div">
                                                                <button type="button" id="fiveth-btn" class="btn3" data-bs-toggle="modal" data-bs-target="#BLOCKModal<?php echo $tid;?>">
                                                                    Active
                                                                </button>
                                                            </div>
                                                            <?php 
                                                            }
                                                            elseif($statusDisplay == 0)
                                                            {?>
                                                            <div id="tb-status-btn-div">
                                                                <button type="button" id="fiveth-btn" class="btn4" data-bs-toggle="modal" data-bs-target="#BLOCKModal<?php echo $tid;?>">
                                                                    Inactive
                                                                </button>
                                                            </div>
                                                            <?php 
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <div id="tb-act-btn-div">
                                                                <a href="<?php echo $base_url_dashboard; ?>/member.php?pid=&teamid=<?php echo $tid;?>" class="tb-ac-btn" id="tb-ac-btn-7" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Member List"><i class="ri-briefcase-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-1" data-bs-toggle="modal" data-bs-target="#EDITModal<?php echo $tid;?>"><i class="ri-edit-2-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-2" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $tid;?>" data-bs-original-title="Delete Team" data-bs-toggle="tooltip" data-bs-placement="top"><i class="ri-delete-bin-6-line"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- EDIT MODAL -->
                                                    <div id="EDITModal<?php echo $tid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="edit_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Edit Team</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                                                                                    <?php 
                                                                                    if($statusDisplay==1)
                                                                                    {
                                                                                    ?> 
                                                                                    <input class="form-check-input" type="checkbox" role="switch" id="defaultbox" name="df" checked > 
                                                                                    <?php
                                                                                    }
                                                                                    else
                                                                                    {
                                                                                    ?>
                                                                                    <input class="form-check-input" type="checkbox" role="switch" id="defaultbox" name="df" > 
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                    <label class="form-check-label" for="defaultbox">Status</label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Name<span>*</span></label>
                                                                                    <input class="form-control" type="text" name="name" id="name" maxlength="150" value="<?php echo $teamDisplay;?>"required>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="tid" value="<?php echo $tid;?>">
                                                                        <input type="hidden" name="edit" value="edit_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF EDIT MODAL -->
                                                    <!-- SET BLOCK MODAL -->
                                                    <div id="BLOCKModal<?php echo $tid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="block_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <?php
                                                                        if($statusDisplay==1)
                                                                        {
                                                                        ?>
                                                                        <p id="modal-title">Deactivate Team</p>
                                                                        <?php
                                                                        }
                                                                        elseif($statusDisplay==0)
                                                                        {
                                                                        ?>
                                                                        <p id="modal-title">Reactivate Team</p>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <?php
                                                                        if($statusDisplay==1)
                                                                        {
                                                                        ?>
                                                                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to deactivate this team?</p>
                                                                        <?php
                                                                        }
                                                                        elseif($statusDisplay==0)
                                                                        {
                                                                        ?>
                                                                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to reactivate this team?</p>
                                                                        <?php
                                                                        }
                                                                        ?>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="tid" value="<?php echo $tid;?>">
                                                                        <input type="hidden" name="block" value="block_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF SET BLOCK MODAL -->
                                                    <!-- DELETE MODAL -->
                                                    <div id="DELETEModal<?php echo $tid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="delete_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Delete Team</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this team?</p>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="tid" value="<?php echo $tid;?>">
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
                                                        }while($row_team =mysqli_fetch_assoc($team));
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
                                    <p id="modal-title">Add Team</p>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div id="modal-div">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Name<span>*</span></label>
                                                <input class="form-control" type="text" name="name" id="name" maxlength="150" required>
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
    <script src="<?php echo $base_url; ?>/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>