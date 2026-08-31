<?php
    require_once('script.php');
    $pg = "kpi";
    $subpg = "mo";

    if(isset($_GET['kid']) && $_GET['kid']!=NULL)
    {
        $kid=$_GET['kid'];
    }

    mysqli_select_db($KPI,$database_KPI);
    $kpiobjsql = "SELECT A.*, B.kojbInfo_title, C.* FROM `kpi_objective` A LEFT JOIN `kpi_objective_info` B ON A.kojbInfo_id=B.kojbInfo_id LEFT JOIN `kpi_objective_mark` C ON A.obj_id=C.obj_id WHERE A.kpi_ID='$kid' AND A.deleted='0'";
    $kpiobj = mysqli_query($KPI,$kpiobjsql) or die(mysqli_error($KPI));
    $row_kpiobj = mysqli_fetch_assoc($kpiobj);
    $totalrow_kpiobj = mysqli_num_rows($kpiobj);

    //Add KPI Objective
    if(isset($_POST['add']) && $_POST['add']=="add_form")
    {
        $aobj = mysqli_real_escape_string($KPI, $_POST['addobj']);
        $atype = mysqli_real_escape_string($KPI, $_POST['addtype']);
        $smr1 = mysqli_real_escape_string($KPI, $_POST['mr1'])?1:0;
        $smr2 = mysqli_real_escape_string($KPI, $_POST['mr2'])?1:0;
        $smr3 = mysqli_real_escape_string($KPI, $_POST['mr3'])?1:0;
        $smr4 = mysqli_real_escape_string($KPI, $_POST['mr4'])?1:0;
        $smr5 = mysqli_real_escape_string($KPI, $_POST['mr5'])?1:0;
        $currenttime = date("Y-m-d H:i:s");
        
        $addosql = "INSERT INTO kpi_objective (obj_time, obj_type, kpi_ID, kojbInfo_id) VALUES ('$currenttime','$atype','$kid','$aobj')";
        mysqli_select_db($KPI,$database_KPI);
        $addo = mysqli_query($KPI,$addosql) or die(mysqli_error($KPI));
        
        $objfk = mysqli_insert_id($KPI);
        $addmrsql = "INSERT INTO kpi_objective_mark (objmk_2, objmk_1, objmk_0, objmk_n1, objmk_n2, obj_id) VALUES ('$smr1','$smr2','$smr3','$smr4','$smr5','$objfk')";
        mysqli_select_db($KPI,$database_KPI);
        $addmr=  mysqli_query($KPI,$addmrsql) or die(mysqli_error($KPI));
        
        $togo = "manage-kpiobjective-add.php?kid=$kid&add=succ";
        header(sprintf("Location: %s",$togo));
        
    }

    //Edit KPI Objective
    if(isset($_POST['edit']) && $_POST['edit']=="edit_form")
    {
        
        $koid = mysqli_real_escape_string($KPI, $_POST['koid']);
        $mrid = mysqli_real_escape_string($KPI, $_POST['mrid']);
        $edittype = mysqli_real_escape_string($KPI, $_POST['edittype']);
        $emr1 = mysqli_real_escape_string($KPI, $_POST['emr1'])?1:0;
        $emr2 = mysqli_real_escape_string($KPI, $_POST['emr2'])?1:0;
        $emr3 = mysqli_real_escape_string($KPI, $_POST['emr3'])?1:0;
        $emr4 = mysqli_real_escape_string($KPI, $_POST['emr4'])?1:0;
        $emr5 = mysqli_real_escape_string($KPI, $_POST['emr5'])?1:0;
        $currenttime = date("Y-m-d H:i:s");
        
        $uosql = "UPDATE `kpi_objective` SET obj_type='$edittype' , obj_time='$currenttime' WHERE obj_id ='$koid'";
        mysqli_select_db($KPI,$database_KPI);
        $uo = mysqli_query($KPI,$uosql) or die(mysqli_error($KPI));
        
        $umsql = "UPDATE `kpi_objective_mark` SET objmk_2='$emr1', objmk_1='$emr2', objmk_0='$emr3', objmk_n1='$emr4', objmk_n2='$emr5' WHERE kobjmark_id='$mrid' AND obj_id ='$koid'";
        mysqli_select_db($KPI,$database_KPI);
        $um=  mysqli_query($KPI,$umsql) or die(mysqli_error($KPI));
        
        $togo = "manage-kpiobjective-add.php?kid=$kid&edit=succ";
        header(sprintf("Location: %s",$togo));
        
    }

    //DELETE KPI Objective
    if(isset($_POST['delete']) && ($_POST['delete']=="delete_form"))
    {
        $delkoid = mysqli_real_escape_string($KPI, $_POST['koid']);
        $edittime = date("Y-m-d H:i:s");
        $setdelete = 1;
      
        $deleteSQL = "UPDATE `kpi_objective` SET  obj_time='$edittime' , deleted='$setdelete' WHERE obj_id='$delkoid'";
        mysqli_select_db($KPI,$database_KPI);
        $editQuery = mysqli_query($KPI,$deleteSQL) or die(mysqli_error($KPI));
        
        $togo = "manage-kpiobjective-add.php?kid=$kid&delete=succ";
        header(sprintf("Location: %s",$togo));
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Add KPI Objective | <?php echo $base_name; ?></title>
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
		
		<!-- DATATABLE CSS -->
		<?php include "inc/datatable-css.php"; ?>
		<!-- END OF DATATABLE CSS -->

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <!-- BREADCRUMB SECTION -->
            <section id="bc-section">
                <div id="bc-div">
                    <a href="<?php echo $base_url_dashboard; ?>"><i class="ri-dashboard-2-line"></i></a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    KPI
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <a href="<?php echo $base_url_dashboard; ?>/manage-kpiobjective.php">Manage KPI</a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Add KPI Objective</span>
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
                                    <a href="javascript:void(0);" id="general-btn"  class="btn1" data-bs-toggle="modal" data-bs-target="#ADDModal"><i class="ri-add-fill"></i>Add Objective</a>
                                </div>
                                <div id="tb-box" class="general-box">
                                    <div id="tb-border-line">
                                        <div id="tb-title-div">
                                            <p id="tb-title">Objective List</p>
                                            <?php if (isset($_GET['add'])) { $register = $_GET['add']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Objective Added Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['delete'])) { $register = $_GET['delete']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Objective Deleted Successfully.</p>
                                            </div>
                                            <?php } } ?>
                                            <?php if (isset($_GET['edit'])) { $register = $_GET['edit']; if ($register == 'succ') { ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">KPI Objective Edited Successfully.</p>
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
                                                        <th class="text-center">Type</th>
                                                        <th class="text-center">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                    <?php 
                                                //position_ID position_name job_scope pcreatedate
                                                    if($totalrow_kpiobj>0)
                                                    {
                                                        $number = 0;
                                                        do
                                                        {
                                                            $koid = $row_kpiobj['obj_id'];
                                                            $kodate = $row_kpiobj['obj_time'];
                                                            $date = date("d M Y",strtotime($kodate));
                                                            $time = date("H:i:s",strtotime($kodate));
                                                            $kotype = $row_kpiobj['obj_type'];
                                                            $koinfo = $row_kpiobj['kojbInfo_title'];
                                                            $mrid = $row_kpiobj['kobjmark_id'];
                                                            $omr1 = $row_kpiobj['objmk_2'];
                                                            $omr2 = $row_kpiobj['objmk_1'];
                                                            $omr3 = $row_kpiobj['objmk_0'];
                                                            $omr4 = $row_kpiobj['objmk_n1'];
                                                            $omr5 = $row_kpiobj['objmk_n2'];
                                                            $deletedS = $row_kpiobj['deleted'];   
                                                     ?>
                                                    <?php
                                                            if($deletedS==0)
                                                            {
                                                    ?>
                                                        <td class="text-center"><?php echo $number=$number+1; ?></td>
                                                        <td><?php echo $koinfo; ?></td>
                                                        <td class="text-center"><?php echo $date; ?><span id="tb-time-p"><?php echo $time; ?></span></td>
                                                        <td class="text-center"><?php if($kotype==0){echo "Standard";}else{echo "Extra";}?></td>
                                                        <td class="text-center">
                                                            <div id="tb-act-btn-div">
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-1"  data-bs-toggle="modal" data-bs-target="#EDITModal<?php echo $koid; ?>"><i class="ri-edit-2-line"></i></a>
                                                                <a href="javascript:void(0);" class="tb-ac-btn" id="tb-ac-btn-2" data-bs-toggle="modal" data-bs-target="#DELETEModal<?php echo $koid; ?>"><i class="ri-delete-bin-6-line"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <!--EDIT MODAL -->
                                                    <div id="EDITModal<?php echo $koid; ?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                        <form action="" method="POST" name="edit_form">
                                                            <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                                <div class="modal-content general-box" id="md-content">
                                                                    <div class="modal-header">
                                                                        <p id="modal-title">Edit Objective</p>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div id="modal-div">
                                                                        <div class="row">
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Objective</label>
                                                                                    <input type="text" class="form-control" value="<?php echo $koinfo;?>" maxlength="200" readonly>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Type<span>*</span></label>
                                                                                    <select class="form-control" id="edittype" name="edittype" required>
                                                                                        <option disabled value="">Select Type</option>
                                                                                        <option value="0" <?php if($kotype==0){echo selected;} ?>>Standard</option>
                                                                                        <option value="1" <?php if($kotype==1){echo selected;} ?>>Extra</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-lg-12">
                                                                                <div class="input-group">
                                                                                    <label>Mark Range<span>*</span></label>
                                                                                </div>
                                                                                <div class="form-check mb-3">
                                                                                    <input class="form-check-input" type="checkbox" id="formCheck1" name="emr1" <?php if($omr1==1){echo checked;} ?>>
                                                                                    <label class="form-check-label" for="formCheck6">
                                                                                        +2pts
                                                                                    </label>
                                                                                </div>
                                                                                <div class="form-check mb-3">
                                                                                    <input class="form-check-input" type="checkbox" id="formCheck2" name="emr2" <?php if($omr2==1){echo checked;} ?>>
                                                                                    <label class="form-check-label" for="formCheck6">
                                                                                        +1pts
                                                                                    </label>
                                                                                </div>
                                                                                <div class="form-check mb-3">
                                                                                    <input class="form-check-input" type="checkbox" id="formCheck3" name="emr3" <?php if($omr3==1){echo checked;} ?>>
                                                                                    <label class="form-check-label" for="formCheck6">
                                                                                        0pts
                                                                                    </label>
                                                                                </div>
                                                                                <div class="form-check mb-3">
                                                                                    <input class="form-check-input" type="checkbox" id="formCheck4" name="emr4" <?php if($omr4==1){echo checked;} ?>>
                                                                                    <label class="form-check-label" for="formCheck6">
                                                                                        -1pts
                                                                                    </label>
                                                                                </div>
                                                                                <div class="form-check mb-3">
                                                                                    <input class="form-check-input" type="checkbox" id="formCheck5" name="emr5" <?php if($omr5==1){echo checked;} ?>>
                                                                                    <label class="form-check-label" for="formCheck6"> 
                                                                                        -2pts
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div id="modal-btn-div">
                                                                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                        <input type="hidden" name="mrid" value="<?php echo $mrid; ?>" >
                                                                        <input type="hidden" name="koid" value="<?php echo $koid; ?>" >
                                                                        <input type="hidden" name="edit" value="edit_form">
                                                                        <button id="general-btn" class="btn1">Confirm</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <!-- END OF EDIT MODAL -->
                                                    <!--DELETE MODAL -->
                                                    <div id="DELETEModal<?php echo $koid;?>" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                                                    <form action="" method="POST" name="delete_form">
                                                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                                                            <div class="modal-content general-box" id="md-content">
                                                                <div class="modal-header">
                                                                    <p id="modal-title">Delete KPI Objective</p>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div id="modal-div">
                                                                    <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to delete this KPI Objective?</p>
                                                                </div>
                                                                <div id="modal-btn-div">
                                                                    <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-fill"></i>Cancel</a>
                                                                    <input type="hidden" name="koid" value="<?php echo $koid;?>">
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
                                                        }while($row_kpiobj =mysqli_fetch_assoc($kpiobj));
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
                <div id="ADDModal" class="modal fade flip" tabindex="-1" aria-labelledby="flipModalLabel" aria-hidden="true" style="display: none;">
                    <form action="" method="POST" name="add_form">
                        <div class="modal-dialog modal-dialog-centered" id="md-dialog">
                            <div class="modal-content general-box" id="md-content">
                                <div class="modal-header">
                                    <p id="modal-title">Add Objective</p>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div id="modal-div">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Objective<span>*</span></label>
                                                <select class="form-control" id="addobj" name="addobj" required>
                                                    <option selected disabled value="">Select Objective</option>
                                                     <?php 
                                                        mysqli_select_db($KPI,$database_KPI);
                                                        $infosql = "SELECT * FROM kpi_objective_info";
                                                        $info = mysqli_query($KPI,$infosql) or die(mysqli_error($KPI));
                                                        $row_info = mysqli_fetch_assoc($info);
                                                        $totalrow_info = mysqli_num_rows($info);
                                                    
                                                        if($totalrow_info>0)
                                                        {
                                                            do
                                                            {
                                                                $iid = $row_info['kojbInfo_id'];
                                                                $ititle = $row_info['kojbInfo_title'];
                                                    ?>
                                                    <option value="<?php echo $iid;?>"><?php echo $ititle;?></option>
                                                    <?php 
                                                            }while($row_info=mysqli_fetch_assoc($info));
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
										<div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Type<span>*</span></label>
                                                <select class="form-control" id="addtype" name="addtype" required>
                                                    <option selected disabled value="">Select Type</option>
                                                    <option value="0">Standard</option>
                                                    <option value="1">Extra</option>
                                                </select>
                                            </div>
                                        </div>
										<div class="col-lg-12">
                                            <div class="input-group">
                                                <label>Mark Range<span>*</span></label>
                                            </div>
											<div class="form-check mb-3">
												<input class="form-check-input" value="1" type="checkbox" id="formCheck1" name="mr1" checked>
												<label class="form-check-label" for="formCheck6">
													+2pts
												</label>
											</div>
											<div class="form-check mb-3">
												<input class="form-check-input" value="1" type="checkbox" id="formCheck2" name="mr2" checked>
												<label class="form-check-label" for="formCheck6">
													+1pts
												</label>
											</div>
											<div class="form-check mb-3">
												<input class="form-check-input" value="1" type="checkbox" id="formCheck3" name="mr3" checked>
												<label class="form-check-label" for="formCheck6">
													0pts
												</label>
											</div>
											<div class="form-check mb-3">
												<input class="form-check-input" value="1" type="checkbox" id="formCheck4" name="mr4" checked>
												<label class="form-check-label" for="formCheck6">
													-1pts
												</label>
											</div>
											<div class="form-check mb-3">
												<input class="form-check-input" value="1" type="checkbox" id="formCheck5" name="mr5" checked>
												<label class="form-check-label" for="formCheck6">
													-2pts
												</label>
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