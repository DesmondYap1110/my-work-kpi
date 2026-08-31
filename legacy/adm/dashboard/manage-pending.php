<?php
    require_once('script.php');
    $pg = "kpi";
    $subpg = "mkpi";

    //Display Pending List
    mysqli_select_db($KPI,$database_KPI);
    $projkpisql = "SELECT b.staff_name, c.position_name, d.p_Title, f.kojbInfo_title ,a.kpiproject_id, a.kojbInfo_id, a.kpi_id, a.createddate, a.mark FROM `project_kpi` a LEFT JOIN `staff` b ON a.staff_id = b.staff_id LEFT JOIN `staff_position` c ON b.position_id= c.position_ID LEFT JOIN `project` d ON a.project_id = d.project_id LEFT JOIN `kpi` e ON a.kpi_id = e.kpi_id LEFT JOIN `kpi_objective_info` f ON a.kojbInfo_id = f.kojbInfo_id WHERE a.status IS NULL AND a.createddate IS NOT NULL";
    $projkpi = mysqli_query($KPI,$projkpisql) or die(mysqli_error($KPI));
    $row_projkpi = mysqli_fetch_assoc($projkpi);
    $totalrow_projkpi = mysqli_num_rows($projkpi);


    //Approve Member KPI Objective
    if(isset($_POST['approve']) && ($_POST['approve']=="approve_form"))
    {
        $approvepkid = mysqli_real_escape_string($KPI, $_POST['pkid']);
        $edittime = date("Y-m-d H:i:s");
        $setapprove = 1;
      
        $approveSQL = "UPDATE `project_kpi` SET  createddate='$edittime' , status='$setapprove' WHERE kpiproject_id='$approvepkid '";
        mysqli_select_db($KPI,$database_KPI);
        $approveQuery = mysqli_query($KPI,$approveSQL ) or die(mysqli_error($KPI));
        
        $togo = "manage-pending.php?pkid=$approvepkid&approve=succ";
        header(sprintf("Location: %s",$togo));
    }

    //Reject Member KPI Objective
    if(isset($_POST['reject']) && ($_POST['reject']=="reject_form"))
    {
        $rejectpkid = mysqli_real_escape_string($KPI, $_POST['pkid']);
        $emark = mysqli_real_escape_string($KPI, $_POST['emark']);
        $edittime = date("Y-m-d H:i:s");
        $setreject = 2;
      
        $rejectSQL = "UPDATE `project_kpi` SET  createddate='$edittime' , mark='$emark', status='$setreject' WHERE kpiproject_id='$rejectpkid'";
        mysqli_select_db($KPI,$database_KPI);
        $rejectQuery = mysqli_query($KPI,$rejectSQL) or die(mysqli_error($KPI));
        
        $togo = "manage-pending.php?pkid=$rejectpkid&reject=succ";
        header(sprintf("Location: %s",$togo));
    }

    

    
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Manage Extra Point | <?php echo $base_name; ?></title>
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
        @media (max-width: 991px)
        {
            div.dataTables_wrapper div.dataTables_paginate ul.pagination 
            {
                justify-content: flex-start !important;
            }
            div.dataTables_wrapper div.dataTables_length, div.dataTables_wrapper div.dataTables_filter, div.dataTables_wrapper div.dataTables_info, div.dataTables_wrapper div.dataTables_paginate 
            {
                text-align: left;
            }
            #tb-title 
            {
                margin-bottom: 15px;
            }
            #table-div table 
            {
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
                    <span id="bc-active">Manage Pending</span>
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
                                            <p id="tb-title">Pending List</p>
                                            <?php if (isset($_GET['approve'])) { $register = $_GET['approve']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Approved Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['reject'])) { $register = $_GET['reject']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Reject Successfully.</p>
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
                                                        <th>Project</th>
                                                        <th>Objective</th>
                                                        <th class="text-center">Mark</th>
                                                        <th class="text-center">Date</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                    <?php 
                                                    if($totalrow_projkpi>0)
                                                    {
                                                        $number = 0;
                                                        do
                                                        {
                                                            $pkid = $row_projkpi['kpiproject_id'];
                                                            $ppkpi =$row_projkpi['kpi_id'];
                                                            $oinfo = $row_projkpi['kojbInfo_id'];
                                                            $pkdate = $row_projkpi['createddate'];
                                                            $date = date("d M Y",strtotime($pkdate));
                                                            $time = date("H:i:s",strtotime($pkdate));
                                                            $sname = $row_projkpi['staff_name'];
                                                            $pname = $row_projkpi['position_name'];
                                                            $projn = $row_projkpi['p_Title'];
                                                            $kinfo = $row_projkpi['kojbInfo_title'];
                                                            $pmark = $row_projkpi['mark'];
                                                    ?>
                                                   
                                                        <td class="text-center"><?php echo $number=$number+1; ?></td>
                                                        <td><?php echo $sname; ?></td>
                                                        <td><?php echo $pname; ?></td>
                                                        <td><?php echo $projn; ?></td>
                                                        <td><?php echo $kinfo; ?></td>
                                                        <td class="text-center"><?php if($pmark>0){echo "+".$pmark;}else{echo $pmark;} ?></td>
                                                        <td class="text-center"><?php echo $date; ?><span id="tb-time-p"><?php echo $time; ?></span></td>
                                                        <td class="text-center">
                                                            <div id="tb-act-btn-div">  
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-6" data-bs-toggle="modal" data-bs-target="#APPROVEModal<?php echo $pkid;?>"><i class="ri-check-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-3" data-bs-toggle="modal" data-bs-target="#REJECTModal<?php echo $pkid;?>"><i class="ri-close-line"></i></a>
                                                            </div>
                                                        </td>
                                                        <!-- APPROVE MODAL -->
                                                        <div id="APPROVEModal<?php echo $pkid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                            <form action="" method="POST" name="approve_form">
                                                                <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                    <div class="modal-content general-box" id="md-content">
                                                                        <div class="modal-header">
                                                                            <p id="modal-title">Approve KPI</p>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div id="modal-div">
                                                                            <p id="modal-p" style="margin-bottom: 5px;">
                                                                                Are you sure you want to approve this?
                                                                            </p>
                                                                        </div>
                                                                        <div id="modal-btn-div">
                                                                            <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                            <input type="hidden" name="pkid" value="<?php echo $pkid;?>">
                                                                            <input type="hidden" name="approve" value="approve_form">
                                                                            <button id="general-btn" class="btn1">Confirm</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <!-- END OF APPROVE MODAL -->
                                                        <!-- REJECT MODAL -->
                                                        <div id="REJECTModal<?php echo $pkid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                            <form action="" method="POST" name="reject_form">
                                                                <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                    <div class="modal-content general-box" id="md-content">
                                                                        <div class="modal-header">
                                                                            <p id="modal-title">Reject KPI</p>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div id="modal-div">
                                                                            <p id="modal-p" style="margin-bottom: 5px;">
                                                                                Are you sure you want to reject assign <?php echo $pmark;?> mark to <?php echo $sname;?>?
                                                                            </p>
                                                                            <div class="row">
                                                                                <div class="col-lg-6">
                                                                                    <div class="input-group">
                                                                                        <label>Mark<span>*</span></label>
                                                                                        
                                                                                        <?php 
                                                                                        //Display Mark
                                                                                        mysqli_select_db($KPI,$database_KPI);
                                                                                        $rmarksql="SELECT d.objmk_2, d.objmk_1, d.objmk_0, d.objmk_n1, d.objmk_n2 FROM `kpi` a LEFT JOIN `kpi_objective` b ON a.kpi_id = b.kpi_id LEFT JOIN `kpi_objective_info` c ON b.kojbInfo_id = c.kojbInfo_id LEFT JOIN `kpi_objective_mark` d ON b.obj_id = d.obj_id WHERE a.kpi_id='$ppkpi' AND b.kojbInfo_id='$oinfo'";
                                                            
                                                            
                                                                                        $rmark = mysqli_query($KPI,$rmarksql) or die(mysqli_error($KPI));
                                                                                        $row_rmark = mysqli_fetch_assoc($rmark);
                                                                                        $totalrow_rmark = mysqli_num_rows($rmark);
                                                                                        ?>
                                                                                        
                                                                                        <select class="form-control js-example-basic-single" name="emark" required>
                                                                                            <option value="" selected disabled>Select Mark</option>
                                                                                            <?php 
                                                                                            if($totalrow_rmark>0)
                                                                                            {
                                                                                                if($row_rmark['objmk_2']==1 && $pmark!=2)
                                                                                                {
                                                                                            ?>
                                                                                             <option value="2">+2</option>
                                                                                            <?php
                                                                                                }
                                                                                                if($row_rmark['objmk_1']==1 && $pmark!=1)
                                                                                                {
                                                                                            ?>
                                                                                              <option value="1">+1</option>
                                                                                            <?php
                                                                                                }
                                                                                                if($row_rmark['objmk_0']==1 && $pmark!=0)
                                                                                                {
                                                                                            ?>
                                                                                              <option value="0">0</option>
                                                                                            <?php
                                                                                                }
                                                                                                if($row_rmark['objmk_n1']==1 && $pmark!=-1)
                                                                                                {
                                                                                            ?>
                                                                                              <option value="-1">-1</option>
                                                                                            <?php
                                                                                                }
                                                                                                if($row_rmark['objmk_n2']==1 && $pmark!=-2)
                                                                                                {
                                                                                            ?>
                                                                                              <option value="-2">-2</option>
                                                                                            <?php
                                                                                                }
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div id="modal-btn-div">
                                                                            <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close">
                                                                                <i class="ri-close-fill"></i>Cancel
                                                                            </a>
                                                                            <input type="hidden" name="pkid" value="<?php echo $pkid;?>">
                                                                            <input type="hidden" name="reject" value="reject_form">
                                                                            <button id="general-btn" class="btn1">Confirm</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                        <!-- END OF REJECT MODAL -->
                                                    </tr>
                                                    <?php             
                                                        }while($row_projkpi=mysqli_fetch_assoc($projkpi));
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
</body>
</html>