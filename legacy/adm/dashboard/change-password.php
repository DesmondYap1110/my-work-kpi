<?php
    require_once('script.php');
    $pg = "setting";
    $subpg = "pass";

    //Reset Password
    if(isset($_POST['reset']) && ($_POST['reset']=="reset_form"))
    {
        $pid = mysqli_real_escape_string($KPI, $_POST['pid']);
        $edittime = date("Y-m-d H:i:s");
        $cpw = mysqli_real_escape_string($KPI , $_POST['cpw']);
        $key = mysqli_real_escape_string($KPI , $_POST['key']);
        $pw = mysqli_real_escape_string($KPI , $_POST['pw']);
        $npw = mysqli_real_escape_string($KPI , $_POST['npw']);
        
        $staffsql = "SELECT * FROM `staff` WHERE staff_id='$user_ID'";
        $staffq = mysqli_query($KPI , $staffsql) or die(mysqli_error($KPI));
        $row_staff = mysqli_fetch_assoc($staffq);
        $totalRows_staff = mysqli_num_rows($staffq);
    
        if($totalRows_staff == 1)
        {
            $check_pword = $row_staff['password'];
            $check_key = $row_staff['licensek'];
            
            if(password_verify($cpw,$check_pword))
            {
                if($key==$check_key)
                {
                    if($pw==$npw)
                    {
                        $password = password_hash($npw,PASSWORD_DEFAULT);
                        $epwSQL = "UPDATE `staff` SET  createddate='$edittime' , password='$password' WHERE staff_id='$user_ID'";
                        mysqli_select_db($KPI,$database_KPI);
                        $epwQuery = mysqli_query($KPI,$epwSQL) or die(mysqli_error($KPI));

                        $togo = "change-password.php?pass=succ";
                        header(sprintf("Location: %s",$togo));
                    }
                    else
                    {
                        $togo = "change-password.php?pass=false2";
                        header(sprintf("Location: %s",$togo));
                    }
                    
                }
                else
                {
                    $togo = "change-password.php?pass=false3";
                    header(sprintf("Location: %s",$togo));
                    
                }
                
            }
            else
            {
                $togo = "change-password.php?pass=false";
                header(sprintf("Location: %s",$togo));
                
            }
        }
   
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Change Password | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->
    <!-- COLOR THEME SECTION -->
    <?php include "inc/color-dark-theme.php"; ?>
    <!-- END OF COLOR THEME SECTION -->
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
                    Settings
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    <span id="bc-active">Change Password</span>
                </div>
            </section>
            <!-- END OF BREADCRUMB SECTION -->
            <div class="page-content">
                <!-- GENERAL SECTION -->
                <section id="general-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6">
                                <div id="form-box" class="general-box">
                                    <form name="reset_form" method="POST" action="">
                                        <div id="form-div" style="margin-bottom: 0;">
                                            <p id="form-sub-title">Change Password</p>
                                            <?php if(isset($_GET['pass'])){ $pass = $_GET['pass']; if($pass == "false"){ ?>
                                            <div class="alert alert-danger" role="alert">
                                                <p class="alert-heading">Current Password Incorrect. Please Try Again.</p>
                                            </div>
                                            <?php } }?>
                                            <?php if(isset($_GET['pass'])){ $pass = $_GET['pass']; if($pass == "false2"){ ?>
                                            <div class="alert alert-danger" role="alert">
                                                <p class="alert-heading">New Password and Confirm Password Does Not Match. Please Try Again.</p>
                                            </div>
                                            <?php } }?>
                                            <?php if(isset($_GET['pass'])){ $pass = $_GET['pass']; if($pass == "false3"){ ?>
                                            <div class="alert alert-danger" role="alert">
                                                <p class="alert-heading">License Key Incorrect</p>
                                            </div>
                                            <?php } }?>
                                            <?php if(isset($_GET['pass'])){ $pass = $_GET['pass']; if($pass == "succ"){ ?>
                                            <div class="alert alert-success" role="alert">
                                                <p class="alert-heading">Password has been Reset.</p>
                                            </div>
                                            <?php } }?>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <label>Current Password<span>*</span></label>
                                                        <input type="password" class="form-control" id="cpw" name="cpw" max="30" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <label>License Key<span>*</span></label>
                                                        <input type="password" class="form-control" id="key" name="key" maxlength="15" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <label>New Password<span>*</span></label>
                                                        <input type="password" class="form-control" id="pw" name="pw" maxlength="30" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <label>Confirm New Password<span>*</span></label>
                                                        <input type="password" class="form-control" id="npw" name="npw" maxlength="30" required>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div id="form-btn-div">
                                                        <input type="hidden" name="reset" value="reset_form">
                                                        <button id="general-btn" class="btn1" type="submit">Continue</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- END OF GENERAL SECTION -->
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