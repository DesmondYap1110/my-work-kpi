<?php
    require_once('script.php');
    $pg = "project";
    $subpg = "mpp";

    if(isset($_POST['filter']) && ($_POST['filter']=="filter_form"))
    {
        //fp - filter project
        $fp = mysqli_real_escape_string($KPI,$_POST['fproject']);
        //ft - filter team
        $ft = mysqli_real_escape_string($KPI,$_POST['fteam']);
        //fs - filter status
        $fs = mysqli_real_escape_string($KPI,$_POST['fstatus']);
        
        $togo="manage-projectphase.php?pid=$fp&tid=$ft&status=$fs";
        header(sprintf("location: %s",$togo));
        
    }

     if(isset($_GET['pid']) || isset($_GET['tid']) || isset($_GET['status']))
    {
        $fprojectid = mysqli_real_escape_string($KPI,$_GET['pid']);
        $fteamid = mysqli_real_escape_string($KPI,$_GET['tid']);
        $fstatus = mysqli_real_escape_string($KPI,$_GET['status']);
         
        if($fprojectid == "")
        {
            $fproject = "";
        }
        else
        {
            $fproject = "AND a.p_id='$fprojectid'";
        }
        
        if($fteamid == "")
        {
            $fteam = "";
        }
        else
        {
            $fteam= " AND b.team_id='$fteamid'";
        }
        
        if($fstatus == "")
        {
            $fst = "";
        }
        else
        {
            $fst = " AND a.p_ppstatus='$fstatus'";
        }
         
        mysqli_select_db($KPI,$database_KPI);
        $SQL_pp = "SELECT a.*, b.p_Title, b.p_status FROM project_phase a LEFT JOIN project b ON a.p_ID=b.project_id WHERE b.deleted='0' AND a.deleted ='0' $fproject $fteam $fst";
        $pproject = mysqli_query($KPI,$SQL_pp) or die(mysqli_error($KPI));
        $row_pproject = mysqli_fetch_assoc($pproject); 
        $totalrow_pproject = mysqli_num_rows($pproject);

        mysqli_select_db($KPI,$database_KPI);
        $SQL_pp1 = "SELECT a.*, b.p_Title, b.p_status FROM project_phase a LEFT JOIN project b ON a.p_ID=b.project_id WHERE b.deleted='0' AND a.deleted ='0' $fproject $fteam $fst";
        $pproject1 = mysqli_query($KPI,$SQL_pp1) or die(mysqli_error($KPI));
        $row_pproject1 = mysqli_fetch_assoc($pproject1); 
        $totalrow_pproject1 = mysqli_num_rows($pproject1);
    }
    else
    {
        
        mysqli_select_db($KPI,$database_KPI);
        $SQL_pp = "SELECT a.*, b.p_Title, b.p_status FROM project_phase a LEFT JOIN project b ON a.p_ID=b.project_id WHERE b.deleted='0' AND a.deleted ='0' ";
        $pproject = mysqli_query($KPI,$SQL_pp) or die(mysqli_error($KPI));
        $row_pproject = mysqli_fetch_assoc($pproject); 
        $totalrow_pproject = mysqli_num_rows($pproject);

        mysqli_select_db($KPI,$database_KPI);
        $SQL_pp1 = "SELECT a.*, b.p_Title, b.p_status FROM project_phase a LEFT JOIN project b ON a.p_ID=b.project_id WHERE b.deleted='0' AND a.deleted ='0' ";
        $pproject1 = mysqli_query($KPI,$SQL_pp1) or die(mysqli_error($KPI));
        $row_pproject1 = mysqli_fetch_assoc($pproject1); 
        $totalrow_pproject1 = mysqli_num_rows($pproject1);
    }

    


     if(isset($_POST['approve']) && ($_POST['approve']=="approve_form"))
    {
        $ppid = mysqli_real_escape_string($KPI, $_POST['ppid']);
        $setapprove = 2;
        $setcomplete = 3;
        $asql = "UPDATE `project_phase` SET p_ppstatus='$setcomplete', p_Status ='$setapprove' WHERE p_PID ='$ppid'";
        $approve = mysqli_query($KPI,$asql) or die(mysqli_error($KPI));
         
        $togo = "manage-projectphase.php?ppid=$ppid&edit=succ";
        header(sprintf("Location: %s",$togo));
    }

    if(isset($_POST['reject']) && ($_POST['reject']=="reject_form"))
    {
        $ppid = mysqli_real_escape_string($KPI, $_POST['ppid']);
        $setreject = 3;
        $setcomplete = 3;
        $rsql = "UPDATE `project_phase` SET p_ppstatus='$setcomplete', p_Status ='$setreject' WHERE p_PID ='$ppid'";
        $reject = mysqli_query($KPI,$rsql) or die(mysqli_error($KPI));
        
        $togo = "manage-projectphase.php?ppid=$ppid&edit=succ";
        header(sprintf("Location: %s",$togo));

    }

    if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $ppid = mysqli_real_escape_string($KPI, $_POST['ppid']);
        $setdelete = 1;
        $delsql = "UPDATE `project_phase` SET deleted='$setdelete' WHERE p_PID ='$ppid'";
        $del = mysqli_query($KPI,$delsql) or die(mysqli_error($KPI));
        
        $checkpsql = "SELECT p_ID FROM `project_phase` WHERE p_PID='$ppid'";
        $checkp = mysqli_query($KPI,$checkpsql) or die(mysqli_error($KPI));
        $row_checkp = mysqli_fetch_assoc($checkp);
        $pid = $row_checkp['p_ID'];
        
        $checkppsql = "SELECT COUNT(p_PID) AS 'num' FROM `project_phase` WHERE p_ID='$pid' AND deleted='0'";
        $checkpp = mysqli_query($KPI,$checkppsql) or die(mysqli_error($KPI));
        $row_checkpp = mysqli_fetch_assoc($checkpp);
        $num = $row_checkpp['num'];
        
        if($num==0)
        {
            $setpstatus=1;
            $sbsql = "UPDATE `project` SET p_status='$setpstatus' WHERE project_id ='$pid'";
            $sb = mysqli_query($KPI,$sbsql) or die(mysqli_error($KPI));
        }
        $togo = "manage-projectphase.php?ppid=$ppid&delete=succ";
        header(sprintf("Location: %s",$togo));
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Project Phase | <?php echo $base_name; ?></title>
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
        #tb-title 
        {
            margin-bottom: 5px;
        }
        iframe 
        {
            width: 100%;
            height: 700px;
        }
        @media (max-width: 991px)
        {
            #tb-title 
            {
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
                    <span id="bc-active">Manage Project Phase</span>
                </div>
            </section>
            <!-- END OF BREADCRUMB SECTION -->
            <div class="page-content">
                <!-- SECTION -->
                <section id="general-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">Project Phase List</p>
                                            <form name="filter_form" method="POST" action="">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Project</label>
                                                            <select class="form-control js-example-basic-single" id="fproject" name="fproject">
                                                                <option selected disabled>Select Project</option>
                                                                <?php
                                                                    mysqli_select_db($KPI,$database_KPI);
                                                                    $query_project = "SELECT * FROM `project`";
                                                                    $fproject = mysqli_query($KPI,$query_project) or die(mysqli_error($KPI));
                                                                    $frow_project = mysqli_fetch_assoc($fproject);
                                                                    $ftotalrow_project = mysqli_num_rows($fproject);
                                                                
                                                                     if($ftotalrow_project>0)
                                                                     {
                                                                            do
                                                                            {
                                                                                if($frow_project['deleted']==0)
                                                                                {
                                                                ?>
                                                                <option value="<?php echo $frow_project['project_id'];?>" <?php if(isset($_GET['pid'])){if($projectid == $frow_project['project_id']){echo "selected";}}?>><?php echo $frow_project['p_Title'];?></option>
                                                                <?php
                                                                                }
                                                                            }while($frow_project=mysqli_fetch_assoc($fproject));
                                                                     }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Team</label>
                                                            <select class="form-control js-example-basic-single" id="fteam" name="fteam">
                                                                <option selected disabled>Select Team</option>
                                                                <?php 
                                                                    if($totalrow_team>0)
                                                                    {
                                                                        do
                                                                        {
                                                                            $tid = $row_team['team_id'];
                                                                            $teamDisplay = $row_team['team_name'];
                                                                            $deletedS = $row_team['deleted'];
                                                                            $teamS = $row_team['team_status'];
                                                                            if($deletedS==0 && $teamS==1)
                                                                            {
                                                                ?>
                                                                <option value="<?php echo $tid;?>" <?php if(isset($_GET['tid'])){ $teamid = mysqli_real_escape_string($KPI, $_GET['tid']); if($teamid == $tid){ echo "selected";}}?>><?php echo $teamDisplay;?></option>
                                                                <?php 
                                                                            } 
                                                                        }while($row_team=mysqli_fetch_assoc($team));
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Status</label>
                                                            <select class="form-control js-example-basic-single" id="fstatus" name="fstatus">
                                                                <option selected disabled>Select Status</option>
                                                                <option value="1" <?php if(isset($_GET['status'])){ $fstatus = mysqli_real_escape_string($KPI, $_GET['status']); if($fstatus == "1"){ echo "selected";}}?>>Progress</option>
                                                                <option value="2" <?php if(isset($_GET['status'])){ $fstatus = mysqli_real_escape_string($KPI, $_GET['status']); if($fstatus == "2"){ echo "selected";}}?>>On-Hold</option>
                                                                <option value="3" <?php if(isset($_GET['status'])){ $fstatus = mysqli_real_escape_string($KPI, $_GET['status']); if($fstatus == "3"){ echo "selected";}}?>>Complete</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div id="filter-btn-div">
                                                            <a id="general-btn" href="<?php echo $base_url_dashboard;?>/manage-projectphase.php" class="btn2"><i class="ri-refresh-line" ></i>Reset</a>
                                                            <input type="hidden" name="filter" value="filter_form">
                                                            <button type="submit" id="general-btn" class="btn1"><i class="ri-filter-2-line"></i>Filter</button>
                                                        </div>
                                                    </div>
                                                </div>
                                             </form>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Project Phase Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Project Phase Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Project Phase Edited Successfully.</p>
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
                                                        <th> Project </th>
                                                        <th> Title </th>
                                                        <th> Project Type </th>
                                                        <th class="text-center">Duration</th>
                                                        <th class="text-center">Submit Date</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Progress Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                        <?php
                                                        //database[ p_PID , P.p_Title , P.p_addDate , P.p_SDate , P.p_EDate , T.team_name , P.date_assign , P.p_status]

                                                        if($totalrow_pproject>0)
                                                        {
                                                            $number = 0;
                                                            do
                                                            {
                                                                $ppid = $row_pproject['p_PID'];
                                                                $title= $row_pproject['p_PTitle'];
                                                                $type=$row_pproject['p_Type'];
                                                                $sdate =ftime($row_pproject['p_SDate']);
                                                                $ddate=ftime($row_pproject['p_DDate']);
                                                                $remark=$row_pproject['p_Remark'];
                                                                $subdate=$row_pproject['p_SubmitDate'];
                                                                $status=$row_pproject['p_Status'];
                                                                $inv=$row_pproject['p_Invoice'];
                                                                $pstatus=$row_pproject['p_ppstatus'];
                                                                $proj = $row_pproject['p_Title'];
                                                                $delete =$row_pproject['deleted'];
                                                                if($delete==0)
                                                                {
                                                        ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $number=$number+1;?></td>
                                                        <td><?php echo $proj;?></td>

                                                        <td class="tb-btn-rel">
                                                            <?php echo $title;?>
                                                            <a href="<?php echo $real_url; ?>/my_asset/<?php echo $remark; ?>" target="_blank" class="tb-in-ac-btn" id="tb-in-ac-btn-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Remark"><i class="ri-article-line"></i></a>
                                                        </td>
                                                        <?php
                                                        if($type==0)
                                                        {
                                                        ?>
                                                        <td> Standard Phase </td>
                                                        <?php
                                                        }           
                                                        else
                                                        {
                                                        ?>
                                                        <td> Amendment Phase </td>
                                                        <?php
                                                        }
                                                        ?>
                                                        <td class="text-center"><?php echo $sdate." - ".$ddate?></td>
                                                        <?php
                                                        if($subdate!=NULL)
                                                        {
                                                            $date = date("d M Y",strtotime($subdate));
                                                            $time = date("H:i:s",strtotime($subdate));
                                                        ?>
                                                        <td class="text-center"><?php echo $date; ?><span id="tb-time-p"><?php echo $time; ?></span></td>
                                                        <?php
                                                        }
                                                        else
                                                        {         
                                                        ?>
                                                        <td class="text-center">-</td>
                                                        <?php
                                                        }
                                                        ?>
                                                        <?php
                                                        if($pstatus==1)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            <span class="tb-status" id="tb-status-4">In-Progress</span>
                                                        </td>
                                                        <?php
                                                        }
                                                        elseif($pstatus==2)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            <span class="tb-status" id="tb-status-3">On Hold</span>
                                                        </td>
                                                        <?php
                                                        }
                                                        elseif($pstatus==3)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            <span class="tb-status" id="tb-status-1">Completed</span>
                                                        </td>
                                                        <?php
                                                        }
                                                                    
                                                        if($status==0)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            -
                                                        </td>
                                                        <?php
                                                        }
                                                        elseif($status==1)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            <span class="tb-status" id="tb-status-4">Pending</span>
                                                        </td>
                                                        <?php
                                                        }
                                                        elseif($status==2)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            <span class="tb-status" id="tb-status-1">Approved</span>
                                                        </td>
                                                        <?php
                                                        }
                                                        elseif($status==3)
                                                        {
                                                        ?>
                                                        <td class="text-center">
                                                            <span class="tb-status" id="tb-status-5">Rejected</span>
                                                        </td>
                                                        <?php
                                                        }
                                                        ?>
                                                        <td class="text-center">
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-fill align-middle"></i></button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <?php
                                                                    if($status==1)
                                                                    {
                                                                    ?>
                                                                    <li><a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#APPROVEModal<?php echo $ppid;?>"><i class="ri-check-line align-bottom me-2"></i>Approve</a></li>

                                                                    <li><a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#REJECTModal<?php echo $ppid;?>"><i class="ri-close-line align-bottom me-2"></i>Reject</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    if($pstatus!=2)
                                                                    {
                                                                    ?>
                                                                    <li><a href="<?php echo $real_url; ?>/my_asset/<?php echo $inv; ?>" target="_blank" class="dropdown-item"><i class="ri-folder-upload-line me-2"></i>Invoice</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    if($status!=0)
                                                                    {
                                                                    ?>
                                                                    <li><a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#HISTORYModal<?php echo $ppid;?>"><i class="ri-history-line align-bottom me-2"></i>Attachment</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    if($pstatus==2)
                                                                    {
                                                                    ?>
                                                                    <li><a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $ppid;?>"><i class="ri-delete-bin-6-line align-bottom me-2"></i>Delete</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    if($pstatus!=3 && ($status!=2 || $status!=3))
                                                                    {
                                                                    ?>
                                                                    <li><a href="<?php echo $base_url_dashboard;?>/projectphase-edit.php?ppid=<?php echo $ppid;?>" class="dropdown-item"><i class="ri-edit-2-line align-bottom me-2"></i>Edit</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                        <!-- APPROVE MODAL -->
                                                        <div id="APPROVEModal<?php echo $ppid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <form action="" method="POST" name="approve_form">
                                                                        <div class="modal-header">
                                                                            <p id="modal-title">Approve Project Phase</p>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div id="modal-div">
                                                                            <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to approve this project phase?</p>
                                                                        </div>
                                                                        <div id="modal-btn-div">
                                                                            <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                            <input type="hidden" name="ppid" value="<?php echo $ppid;?>">
                                                                            <input type="hidden" name="approve" value="approve_form">
                                                                            <button id="general-btn" class="btn1">Confirm</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- END OF APPROVE MODAL -->

                                                        <!-- REJECT MODAL -->
                                                        <div id="REJECTModal<?php echo $ppid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <form action="" method="POST" name="reject_form">
                                                                        <div class="modal-header">
                                                                            <p id="modal-title">Reject Project Phase</p>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div id="modal-div">
                                                                            <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to reject this project phase?</p>
                                                                        </div>
                                                                        <div id="modal-btn-div">
                                                                            <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                            <input type="hidden" name="ppid" value="<?php echo $ppid;?>">
                                                                            <input type="hidden" name="reject" value="reject_form">
                                                                            <button id="general-btn" class="btn1">Confirm</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- END OF REJECT MODAL -->

                                                        <!-- DELETE MODAL -->
                                                        <div id="DELETEModal<?php echo $ppid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <form action="" method="POST" name="delete_form">
                                                                        <div class="modal-header">
                                                                            <p id="modal-title">Delete Project Phase</p>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div id="modal-div">
                                                                            <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this project phase?</p>
                                                                        </div>
                                                                        <div id="modal-btn-div">
                                                                            <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                            <input type="hidden" name="ppid" value="<?php echo $ppid;?>">
                                                                            <input type="hidden" name="delete" value="delete_form">
                                                                            <button id="general-btn" class="btn1">Confirm</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- END OF DELETE MODAL -->
                                                        
                                                    <?php    
                                                            }
                                                        }while($row_pproject = mysqli_fetch_assoc($pproject));
                                                    }
                                                    ?>  
                                                </tr>
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
                <?php
                //database[ p_PID , P.p_Title , P.p_addDate , P.p_SDate , P.p_EDate , T.team_name , P.date_assign , P.p_status]

                if($totalrow_pproject1>0)
                {
                    $number = 0;
                    do
                    {
                        $ppid = $row_pproject1['p_PID'];
                        $delete =$row_pproject1['deleted'];
                        if($delete==0)
                        {
                ?>
                
                <!-- ATTACHMENT MODAL -->
                <div id="HISTORYModal<?php echo $ppid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                    <div class="modal-dialog modal-dialog-centered" id="tb-dialog">
                        <div class="modal-content general-box" id="tb-content">
                            <div class="modal-header">
                                <p id="modal-title">Attachment Project Phase</p>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div id="modal-div">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div id="table-div">
                                            <table class="table table-bordered nowrap table-striped align-middle">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">No</th>
                                                        <th>Name</th>
                                                        <th class="text-center">Submit Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                        mysqli_select_db($KPI,$database_KPI);
                                                        $SQL_ppf = "SELECT PPfilename, PPdatetime , staff_ID FROM `project_PhaseFile` WHERE p_pID='$ppid' AND deleted = '0';";
                                                        $ppfproject = mysqli_query($KPI,$SQL_ppf) or die(mysqli_error($KPI));
                                                        $row_ppfproject = mysqli_fetch_assoc($ppfproject); 
                                                        $totalrow_ppfproject = mysqli_num_rows($ppfproject);
                        
                                                       if($totalrow_ppfproject>0)
                                                       {
                                                           $num=0;
                                                        do
                                                          {
                                                            $submitdate = date("d M Y",strtotime($row_ppfproject['PPdatetime']));
                                                            $submittime = date("H:i:s",strtotime($row_ppfproject['PPdatetime']));
                                                    ?>
                                                    <tr>
                                                        
                                                        <td class="text-center"><?php echo  $num=$num+1;?></td>
                                                        <td>
                                                            <a Style="text-decoration: underline !important; color:#1896BD;" href="<?php echo $real_url;?>/my_asset/<?php echo $row_ppfproject['staff_ID'];?>/<?php echo $row_ppfproject['PPfilename'];?>" target="_blank"><?php echo $row_ppfproject['PPfilename'];?>
                                                            </a>
                                                        </td>
                                                        <td class="text-center"><?php echo $submitdate; ?><span id="tb-time-p"><?php echo $submittime; ?></span></td>
                                                    </tr>
                                                    <?php 
                                                            }while($row_ppfproject = mysqli_fetch_assoc($ppfproject));
                                                       }
                                                    ?>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="modal-btn-div">
                                <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Close</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END OF ATTACHMENT MODAL -->
                <?php    
                        }
                    }while($row_pproject1 = mysqli_fetch_assoc($pproject1));
                }
                ?>  
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