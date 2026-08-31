<?php
    require_once('script.php');
    $pg = "member";

    //Filter Member Section
    if(isset($_POST['filter']) && ($_POST['filter']=="filter_form"))
    {
        $fposition = mysqli_real_escape_string($KPI,$_POST['position']);
        $fteam = mysqli_real_escape_string($KPI,$_POST['team']);
        $togo="member.php?pid=$fposition&teamid=$fteam";
        header(sprintf("location: %s",$togo));
    }

    if(isset($_GET['teamid']) || isset($_GET['pid']))
    {
        $teamid = mysqli_real_escape_string($KPI,$_GET['teamid']);
        $pid = mysqli_real_escape_string($KPI,$_GET['pid']);
        
        //Filter Team of Member (Team Interface)
        if($teamid=="")
        {
            $teamf="";
        }
        else
        {
            $teamf=" AND S.team_id='$teamid'";

        }
    
        //Filter Position of Member (Position Interface)
        if($pid=="" )
        {
            $posf="";
        }
        else
        {
            $posf=" AND S.position_id='$pid'";
        }
        mysqli_select_db($KPI,$database_KPI);
        $SQL_member = "SELECT S.*, P.position_name, T.team_id, T.team_name FROM `staff` S LEFT JOIN `staff_position` P ON S.position_id=P.position_ID LEFT JOIN `team` T ON S.team_id=T.team_id WHERE S.staffstatus = '1' AND S.deleted = '0' AND  S.position_id != '1' $teamf $posf";
        $member = mysqli_query($KPI,$SQL_member) or die(mysqli_error($KPI));
        $row_member = mysqli_fetch_assoc($member); 
        $totalrow_member = mysqli_num_rows($member);
 
       
    }
    else
    {
        mysqli_select_db($KPI,$database_KPI);
        $SQL_member = "SELECT S.*, P.position_name, T.team_id, T.team_name FROM `staff` S LEFT JOIN `staff_position` P ON S.position_id=P.position_ID LEFT JOIN `team` T ON S.team_id=T.team_id WHERE S.staffstatus = '1' AND S.deleted = '0' AND S.position_id != '1'";
        $member = mysqli_query($KPI,$SQL_member) or die(mysqli_error($KPI));
        $row_member = mysqli_fetch_assoc($member); 
        $totalrow_member = mysqli_num_rows($member);
    }

    //Block Member Section
    if(isset($_POST['block']) && $_POST['block']=="block_form")
    {
        
        $currenttid = mysqli_real_escape_string($KPI,$_POST['mid']);
        
        mysqli_select_db($KPI,$database_KPI);
        $checkstSQL = "SELECT * FROM `staff` WHERE staff_id ='$currenttid'";
        $checkQuery = mysqli_query($KPI,$checkstSQL) or die(mysqli_error($KPI));
        $row_check = mysqli_fetch_assoc($checkQuery);
        $totalrow_check = mysqli_num_rows($checkQuery);
        
        $blocktime=date("Y-m-d H:i:s");
        
        if($totalrow_check>0)
        {
            $status=$row_check['staffstatus'];
            if($status==1)
            {
                $updateSt=0;

            }
            elseif($status==0)
            {
                $updateSt=1;
            }
            mysqli_select_db($KPI,$database_KPI);
            $blockSQL = "UPDATE `staff` SET staffstatus='$updateSt' , createddate=' $blocktime' WHERE staff_id ='$currenttid'";
            $blockQuery = mysqli_query($KPI,$blockSQL) or die(mysqli_error($KPI));
            $togo = "member.php?edit=succ";
            header(sprintf("Location: %s",$togo));
        }
    }

    //Delete Position
    if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $mid = mysqli_real_escape_string($KPI, $_POST['mid']);
        $edittime = date("Y-m-d H:i:s");
        $setdelete= 1;
      
        $deleteSQL = "UPDATE `staff` SET  createddate='$edittime' , deleted='$setdelete' WHERE staff_id='$mid'";
        mysqli_select_db($KPI,$database_KPI);
        $editQuery = mysqli_query($KPI,$deleteSQL) or die(mysqli_error($KPI));
        
        $togo = "member.php?delete=succ";
        header(sprintf("Location: %s",$togo));
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Member | <?php echo $base_name; ?></title>
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
                    <span id="bc-active">Member</span>
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
                                    <a href="<?php echo $base_url_dashboard; ?>/member-add.php" id="general-btn" class="btn1">
                                        <i class="ri-add-fill"></i>
                                         Add Member
                                    </a>
                                </div>
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">Member List</p>
                                             <form name="filter_form" method="POST" action="">
                                                <div class="row align-items-center">
                                                   <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Position</label>
                                                            <select class="form-control js-example-basic-single" id="position" name="position">
                                                                <option selected disabled>Select Position</option>
                                                                <?php 
                                                                    if($totalrow_position>0)
                                                                    {
                                                                        do
                                                                        {
                                                                            $posid = $row_position['position_ID'];
                                                                            $positionDisplay = $row_position['position_name'];
                                                                            $deletedS = $row_position['deleted'];
                                                                            if($deletedS==0 )
                                                                            {
                                                                ?>
                                                                <option value="<?php echo $posid; ?>" <?php if(isset($_GET['pid'])){ $positionid = mysqli_real_escape_string($KPI, $_GET['pid']); if($positionid ==$posid){ echo "selected";}} ?>><?php echo $positionDisplay;?></option>
                                                                <?php 
                                                                            } 
                                                                        }while($row_position=mysqli_fetch_assoc($position));
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Team</label>
                                                            <select class="form-control js-example-basic-single" id="team" name="team">
                                                                <option selected disabled>Select Team</option>
                                                                <?php 
                                                                    if($totalrow_position>0)
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
                                                                <option value="<?php echo $tid;?>" <?php if(isset($_GET['teamid'])){ $teamid = mysqli_real_escape_string($KPI, $_GET['teamid']); if($teamid ==$tid){ echo "selected";}} ?>><?php echo $teamDisplay;?></option>
                                                                <?php 
                                                                            } 
                                                                        }while($row_team=mysqli_fetch_assoc($team));
                                                                    }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div id="filter-btn-div">
                                                            <a id="general-btn" href="<?php echo $base_url_dashboard;?>/member.php" class="btn2"><i class="ri-refresh-line" ></i>
                                                                Reset
                                                            </a>
                                                            <input type="hidden" name="filter" value="filter_form">
                                                            <button type="submit" id="general-btn" class="btn1"><i class="ri-filter-2-line"></i>Filter</button>
                                                        </div>
                                                    </div>
                                                </div>
                                             </form>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Member Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Member Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Member Edited Successfully.</p>
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
                                                        <th>Position</th>
                                                        <th class="text-center">Team</th>
                                                        <th class="text-center">Contact</th>
                                                        <th>Email</th>
                                                        <th class="text-center">Join Date</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    //database[ staff_id, staff_name , position_id , team_id , contact , email , datejoincompany , staffstatus]
                                                    if($totalrow_member>0)
                                                    {
                                                        $number = 0;
                                                        do
                                                        {
                                                            $mid = $row_member['staff_id'];
                                                            $membername= $row_member['staff_name'];
                                                            $position=$row_member['position_name'];
                                                            $mteamid = $row_member['team_id'];
                                                            $team =$row_member['team_name'];
                                                            $contact=$row_member['contact'];
                                                            $email=$row_member['email'];
                                                            $djc=$row_member['datejoincompany'];
                                                            $staffstatus=$row_member['staffstatus'];
                                                            $deleteS =$row_member['deleted'];
                                                            if($deleteS==0)
                                                            {
                                                        
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $number=$number+1;?></td>
                                                        <td><span id="break-word"><?php echo $membername;?></span></td>
                                                        <td><?php echo $position;?></td>
                                                        <td class="text-center"><?php echo $team;?></td>
                                                        <td class="text-center"><?php echo $contact;?></td>
                                                        <td><?php echo $email;?></td>
                                                        <td class="text-center"><?php $rdate=date_create($djc); echo date_format($rdate,"d M Y");?></td>
                                                        <td class="text-center">
                                                            <?php 
                                                            if($staffstatus == 1)
                                                            {
                                                            ?>
                                                            <div id="tb-status-btn-div">
                                                                <button type="button" id="fiveth-btn" class="btn3" data-bs-toggle="modal" data-bs-target="#BLOCKModal<?php echo $mid?>">
                                                                    Active
                                                                </button>
                                                            </div>
                                                            <?php 
                                                            }
                                                            elseif($staffstatus == 0)
                                                            {
                                                            ?>
                                                            <div id="tb-status-btn-div">
                                                                <button type="button" id="fiveth-btn" class="btn4" data-bs-toggle="modal" data-bs-target="#BLOCKModal<?php echo $mid?>">
                                                                    Inactive
                                                                </button>
                                                            </div>
                                                            <?php 
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php 
                                                            //lp - lastest project. It is use to get the lastest project KPI in member-viewkpi.php
                                                                mysqli_select_db($KPI,$database_KPI);
                                                                $lp_sql = "SELECT b.project_id FROM `project_kpi` a LEFT JOIN `project` b ON a.project_id = b.project_id WHERE a.staff_id = '$mid' AND b.team_id = '$mteamid' AND b.p_status = '2' AND b.complete_date = (SELECT MAX(b.complete_date) FROM `project_kpi` a LEFT JOIN `project` b ON a.project_id = b.project_id WHERE a.staff_id = '$mid' AND b.team_id = '$mteamid' AND b.p_status = '2' AND b.complete_date IS NOT NULL) AND b.complete_date IS NOT NULL ORDER BY b.complete_date DESC LIMIT 1";
                                                                
                                                                $lp = mysqli_query($KPI,$lp_sql) or die(mysqli_error($KPI));
                                                                $row_lp = mysqli_fetch_assoc($lp); 
                                                                $totalrow_lp = mysqli_num_rows($lp);
                                                                
                                                                if($totalrow_lp>0)
                                                                {
                                                                    
                                                                    $lpid = $row_lp['project_id'];
                                                                    
                                                                }
                                                                else
                                                                {
                                                                    $lpid="";
                                                                }
                                                            ?>
                                                            <div id="tb-act-btn-div">
                                                                <a href="<?php echo $base_url_dashboard; ?>/member-viewkpi.php?uid=<?php echo $mid;?>&pid=<?php echo $lpid;?>" class="tb-ac-btn" id="tb-ac-btn-4" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Member KPI"><i class="ri-eye-line"></i></a>
                                                                <a href="<?php echo $base_url_dashboard; ?>/member-edit.php?uid=<?php echo $mid;?>" class="tb-ac-btn" id="tb-ac-btn-1" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit Member"><i class="ri-edit-2-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-2" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $mid?>"><i class="ri-delete-bin-6-line"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- DELETE MODAL -->
                                                    <div id="DELETEModal<?php echo $mid?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                    <form action="" method="POST" name="delete_form">
                                                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                            <div class="modal-content general-box" id="md-content">
                                                                <div class="modal-header">
                                                                    <p id="modal-title">Delete Member</p>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div id="modal-div">
                                                                    <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this member?</p>
                                                                </div>
                                                                <div id="modal-btn-div">
                                                                    <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                    <input type="hidden" name="mid" value="<?php echo $mid;?>">
                                                                    <input type="hidden" name="delete" value="delete_form">
                                                                    <button id="general-btn" class="btn1">Confirm</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    </div>
                                                    <!-- END OF DELETE MODAL -->
                                                    <!-- SET BLOCK MODAL -->
                                                    <div id="BLOCKModal<?php echo $mid?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                    <form action="" method="POST" name="block_form">
                                                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                            <div class="modal-content general-box" id="md-content">
                                                                <div class="modal-header">
                                                                    <?php
                                                                    if($staffstatus == 1)
                                                                    {
                                                                    ?>
                                                                    <p id="modal-title">Deactivate Member</p>
                                                                    <?php
                                                                    }
                                                                    elseif($staffstatus == 0)
                                                                    {
                                                                    ?>
                                                                    <p id="modal-title">Reactivate Member</p>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div id="modal-div">
                                                                    <?php
                                                                    if($staffstatus == 1)
                                                                    {
                                                                    ?>
                                                                    <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to deactivate this member?</p>
                                                                    <?php
                                                                    }
                                                                    elseif($staffstatus == 0)
                                                                    {
                                                                    ?>
                                                                    <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to reactivate this member?</p>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div id="modal-btn-div">
                                                                    <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                    <input type="hidden" name="mid" value="<?php echo $mid;?>">
                                                                    <input type="hidden" name="block" value="block_form">
                                                                    <button id="general-btn" class="btn1">Confirm</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    </div>
                                                    <!-- END OF SET BLOCK MODAL -->
                                                     <?php    
                                                            }
                                                        }while($row_member =mysqli_fetch_assoc($member));
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
    <script type="text/javascript">
        document.getElementById('date_range').readOnly = false;
    </script>
</body>
</html>