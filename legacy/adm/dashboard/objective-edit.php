<?php
    require_once('script.php');
    $pg = "kpi";
    $subpg = "mo";
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Edit Objective | <?php echo $base_name; ?></title>
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
                    KPI
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <a href="<?php echo $base_url_dashboard; ?>/manage-objective.php">Manage Objective</a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Edit Objective</span>
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
                                    <form name="add_form" method="POST" action="">
                                        <p id="form-sub-title">Add Objective</p>
                                        
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="defaultbox" name="df" <?php if($df == 1){ echo "checked"; }else{ if($totalrow_fil > 0){ echo "disabled";}}?>>
                                                    <label class="form-check-label" for="defaultbox">Status</label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Title<span>*</span></label>
                                                    <input type="text" class="form-control" id="title" name="title" maxlength="200" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Position<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="position" name="position">
                                                        <option selected disabled>Select Position</option>
                                                        <option value="Project Manager">Project Manager</option>
                                                        <option value="Website Designer">Website Designer</option>
                                                        <option value="Website Developer">Website Developer</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Min<span>*</span></label>
                                                    <input type="number" class="form-control" id="min_mark" name="min_mark" min="-10" max="10" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Max<span>*</span></label>
                                                    <input type="number" class="form-control" id="min_mark" name="min_mark" min="-10" max="10" required>
                                                </div>
                                            </div>
                                        </div>
                                            
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="form-btn-div">
                                                    <a href="<?php echo $base_url_dashboard; ?>/manage-objective.php?uid=<?php echo $uid?>" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                                                    <input type="hidden" name="add" value="add_form">
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