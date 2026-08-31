<?php
    require_once('script.php');
    $pg = "project";
    $subpg = "mp";

    if(isset($_POST['filter']) && ($_POST['filter']=="filter_form"))
    {
        //fp - filter project
        $fp = mysqli_real_escape_string($KPI,$_POST['fproject']);
        //fs - filter status
        $fs = mysqli_real_escape_string($KPI,$_POST['fstatus']);
        //fd - filter date
        $fd = mysqli_real_escape_string($KPI,$_POST['date_range']);
        
         if($fd==NULL || $fd=="")
        {
            $stdate ="";
            $eddate ="";
        }
         else
        {
            $drange = explode(" to ",$fd);
            $sd = $drange[0];
            $ed = $drange[1];
            if ($ed == "" || $ed == NULL)
            {
                $ed=$sd;
            }
            $stdate =  date("Y-m-d", strtotime($sd));
		    $eddate =  date("Y-m-d", strtotime($ed));
        }
        $togo="manage-project.php?fp=$fp&fs=$fs&stdate=$stdate&eddate=$eddate";
        header(sprintf("location: %s",$togo));
    }

     if(isset($_GET['fp']) || isset($_GET['fs']) || isset($_GET['stdate']) || isset($_GET['eddate']))
    {
        $fp = mysqli_real_escape_string($KPI,$_GET['fp']);
        $fs = mysqli_real_escape_string($KPI,$_GET['fs']);
        $stdate =  mysqli_real_escape_string($KPI,$_GET['stdate']);
        $eddate = mysqli_real_escape_string($KPI,$_GET['eddate']);
        
        if($fp == "")
        {
            $fp1 = "";
        }
        else
        {
            $fp1 = " AND P.project_id='$fp'";
        }
        
        if($fs == "")
        {
            $fs1 = "";
        }
        else
        {
            $fs1 = " AND P.p_status = '$fs'";
        }
        
        if($stdate == "")
        {
            $sdate1 = "";
        }
        else
        {
            $sdate1 = " AND P.p_SDate <= '$stdate' AND P.p_EDate >= '$eddate'";
        }

        mysqli_select_db($KPI,$database_KPI);
        $SQL_p = "SELECT P.*, T.team_name FROM project P LEFT JOIN team T ON P.team_id=T.team_id WHERE P.deleted = 0 $fp1 $fs1 $sdate1";
        $project = mysqli_query($KPI,$SQL_p) or die(mysqli_error($KPI));
        $row_project = mysqli_fetch_assoc($project); 
        $totalrow_project = mysqli_num_rows($project);
    }
    else
    {
        mysqli_select_db($KPI,$database_KPI);
        $SQL_p = "SELECT P.*, T.team_name FROM project P LEFT JOIN team T ON P.team_id=T.team_id WHERE P.deleted = 0";
        $project = mysqli_query($KPI,$SQL_p) or die(mysqli_error($KPI));
        $row_project = mysqli_fetch_assoc($project); 
        $totalrow_project = mysqli_num_rows($project);
    }

    //Cancel Project
    if(isset($_POST['cancel']) && ($_POST['cancel']=="cancel_form"))
    {
        $pid = mysqli_real_escape_string($KPI, $_POST['pid']);
        $setcancel = 4;
      
        $CSQL = "UPDATE `project` SET p_status = '$setcancel' WHERE project_id='$pid'";
        mysqli_select_db($KPI,$database_KPI);
        $editQuery = mysqli_query($KPI,$CSQL) or die(mysqli_error($KPI));
        
        $togo = "manage-project.php?edit=succ";
        header(sprintf("Location: %s",$togo));
    }

   //Delete Project
    if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $pid = mysqli_real_escape_string($KPI, $_POST['pid']);
        $setdelete= 1;
      
        $deleteSQL = "UPDATE `project` SET  deleted='$setdelete' WHERE project_id='$pid'";
        mysqli_select_db($KPI,$database_KPI);
        $editQuery = mysqli_query($KPI,$deleteSQL) or die(mysqli_error($KPI));
        
        $togo = "manage-project.php?delete=succ";
        header(sprintf("Location: %s",$togo));
    }

?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Manage Project | <?php echo $base_name; ?></title>
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
                    project
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Manage Project</span>
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
                                     <a href="<?php echo $base_url_dashboard; ?>/project-add.php" id="general-btn" class="btn1"><i class="ri-add-fill"></i>Add Project</a> 
                                </div>
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">Project List</p>
                                            <form name="filter_form" method="POST" action="">
                                                <div class="row align-items-center">
                                                   <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Project</label>
                                                            <select class="form-control js-example-basic-single" id="fproject" name="fproject">
                                                                <option selected disabled>Select Project</option>
                                                                <?php
                                                                     if($ftotalrow_project>0)
                                                                     {
                                                                            do
                                                                            {
                                                                                if($frow_project['deleted']==0)
                                                                                {
                                                                ?>
                                                                <option value="<?php echo $frow_project['project_id'];?>" <?php if(isset($_GET['fp'])){ $fp = mysqli_real_escape_string($KPI, $_GET['fp']); if($fp == $frow_project['project_id']){ echo "selected";}}?>><?php echo $frow_project['p_Title'];?></option>
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
                                                            <label>Status</label>
                                                            <select class="form-control js-example-basic-single" id="fstatus" name="fstatus">
                                                                <option selected  disabled>Select Status</option>
                                                                <option value="1" <?php if(isset($_GET['fs'])){ $fs = mysqli_real_escape_string($KPI, $_GET['fs']); if($fs == "1"){ echo "selected";}}?>>Active</option>
                                                                <option value="2" <?php if(isset($_GET['fs'])){ $fs = mysqli_real_escape_string($KPI, $_GET['fs']); if($fs == "2"){ echo "selected";}}?>>Completed</option>
                                                                <option value="3" <?php if(isset($_GET['fs'])){ $fs = mysqli_real_escape_string($KPI, $_GET['fs']); if($fs == "3"){ echo "selected";}}?>>In-Progress</option>
                                                                <option value="4" <?php if(isset($_GET['fs'])){ $fs = mysqli_real_escape_string($KPI, $_GET['fs']); if($fs == "4"){ echo "selected";}}?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="input-group">
                                                            <label>Date Range</label>
                                                            <input type="text" class="form-control flatpickr-input" id="date_range" name="date_range" data-provider="flatpickr" data-date-format="d M Y" data-range-date="true" placeholder="Select Date Range" 
                                                            value =
                                                            "<?php 
                                                             if(isset($_GET['stdate']) || isset($_GET['eddate']))
                                                            { 
                                                                $fd1 = mysqli_real_escape_string($KPI, $_GET['stdate']);
                                                                $fd2 = mysqli_real_escape_string($KPI, $_GET['eddate']);

                                                                if($fd1 == "" || $fd1 == NULL)
                                                                {
                                                                    echo "";
                                                                }
                                                                else
                                                                {
                                                                    if($fd1 == $fd2)
                                                                    {
                                                                        echo date("d M Y", strtotime($fd1));
                                                                    }
                                                                    else
                                                                    {
                                                                        echo date("d M Y", strtotime($fd1))." to ".date("d M Y", strtotime($fd2));;
                                                                    }
                                                                }
                                                            } 
                                                             ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div id="filter-btn-div">
                                                            <a id="general-btn" href="<?php echo $base_url_dashboard;?>/manage-project.php" class="btn2"><i class="ri-refresh-line" ></i>Reset</a>
                                                            <input type="hidden" name="filter" value="filter_form">
                                                            <button type="submit" id="general-btn" class="btn1"><i class="ri-filter-2-line"></i>Filter</button>
                                                        </div>
                                                    </div>
                                                </div>
                                             </form>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Project Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Project Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Project Edited Successfully.</p>
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
                                                        <th class="text-center">Team</th>
                                                        <th class="text-center">Created Date</th>
                                                        <th class="text-center">Assign Date</th>
                                                        <th class="text-center">Duration</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    //database[ P.project_id , P.p_Title , P.p_addDate , P.p_SDate , P.p_EDate , T.team_name , P.date_assign , P.p_status]
                                                    
                                                    if($totalrow_project>0)
                                                    {
                                                        $number = 0;
                                                        do
                                                        {
                                                            $pid = $row_project['project_id'];
                                                            $p_Title= $row_project['p_Title'];
                                                            $p_addDate=ftime($row_project['p_addDate']);
                                                            $p_SDate =ftime($row_project['p_SDate']);
                                                            $p_EDate=ftime($row_project['p_EDate']);
                                                            $team_name=$row_project['team_name'];
                                                            $date_assign=ftime($row_project['date_assign']);
                                                            $p_status=$row_project['p_status'];
                                                            $delete =$row_project['deleted'];
                                                            if($delete==0)
                                                            {
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $number=$number+1;?></td>
                                                        <td><?php echo $p_Title;?></td>
                                                        <td class="text-center"><?php echo $team_name;?></td>
                                                        <td class="text-center"><?php echo $p_addDate;?></td>
                                                        <td class="text-center"><?php echo $date_assign;?></td>
                                                        <td class="text-center"><?php echo $p_SDate." - ".$p_EDate;?></td>
                                                        <td class="text-center">
                                                            <?php 
                                                            if($p_status==1)
                                                            {
                                                            ?>
                                                            <span class="tb-status" id="tb-status-5">Active</span>
                                                            <?php 
                                                            }
                                                            elseif($p_status==4)
                                                            {
                                                            ?>
                                                            <span class="tb-status" id="tb-status-2">Cancelled</span>
                                                            <?php
                                                            }
                                                            elseif($p_status==3)
                                                            {
                                                            ?>
                                                            <span class="tb-status" id="tb-status-4">In-Progress</span>
                                                            <?php
                                                            }
                                                            elseif($p_status==2)
                                                            {
                                                            ?>
                                                            <span class="tb-status" id="tb-status-1">Completed</span>
                                                            <?php
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="dropdown d-inline-block">
                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-fill align-middle"></i></button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <?php
                                                                    if($p_status==2 || $p_status==4 || $p_status==3)
                                                                    {
                                                                    ?>
                                                                    <li><a href="<?php echo $base_url_dashboard; ?>/manage-projectphase.php?pid=<?php echo $pid;?>&tid=&status=" class="dropdown-item"><i class="ri-eye-line me-2"></i>View Project Phase</a></li>
                                                                    <?php
                                                                    }
                                                                    if($p_status==3 || $p_status==1)
                                                                    {
                                                                    ?>
                                                                    <li><a href="<?php echo $base_url_dashboard; ?>/projectphase-add.php?pid=<?php echo $pid;?>"  class="dropdown-item"><i class="ri-add-line me-2"></i>Add Project Phase</a></li>
                                                                    <li><a href="<?php echo $base_url_dashboard; ?>/project-edit.php?pid=<?php echo $pid;?>" class="dropdown-item"><i class="ri-edit-2-line align-bottom me-2"></i>Edit Project</a></li>
                                                                    <li><a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#CANCELModal<?php echo $pid;?>"><i class="ri-close-line align-bottom me-2"></i>Cancel</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <?php
                                                                    if($p_status==1)
                                                                    {
                                                                    ?>
                                                                    <li><a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $pid;?>"><i class="ri-delete-bin-6-line align-bottom me-2"></i>Delete</a></li>
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!-- CANCEL MODAL -->
                                                    <div id="CANCELModal<?php echo $pid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="cancel_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Cancel Project</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to cancel this project?</p>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="pid" value="<?php echo $pid;?>">
                                                                        <input type="hidden" name="cancel" value="cancel_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF DELETE MODAL -->                                         
                                                    <!-- DELETE MODAL -->
                                                    <div id="DELETEModal<?php echo $pid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="delete_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Delete Project</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this project?</p>
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
                                                    <!-- END OF DELETE MODAL -->
                                                    <?php    
                                                            }
                                                        }while($row_project =mysqli_fetch_assoc($project));
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