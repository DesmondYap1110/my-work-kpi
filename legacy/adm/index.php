<?php
require_once('script.php');
/* --------------------------------------------------
					Stay Login Script
 -------------------------------------------------- */
if(isset($_COOKIE['staylog']))
{
    if(isset($_GET['logout']))
    {
        setcookie("staylog", "", time()-3600);
    }
    else
    {
        $staylog=$_COOKIE['staylog'];
        //Get user account if user ID match
        mysqli_select_db($KPI,$database_KPI);
        $query_member = "SELECT * FROM `staff` WHERE staff_id = '$staylog' ";
        $member = mysqli_query($KPI,$query_member) or die(mysqli_error($KPI));
        $row_member = mysqli_fetch_assoc($member);
        $totalRows_member = mysqli_num_rows($member);
        
        if($totalRows_member > 0)
        {
            $user_ID = $row_member['staff_id'];
            $status = $row_member['staffstatus'];
            $position = $row_member['position_id'];
            
            if($status == 0)
            {
                $FailedGoTo = $base_url. "/adm/index.php?status=inactive";
                header("Location: ". $FailedGoTo);
            }
            else
            {
                $loginStrGroup = "";
                $session_id = session_id();
                $latest_time = date("Y-m-d H:i:s");
                $user_IP = $_SERVER['REMOTE_ADDR'];
                $access_type = 1;
                
                $updateSQL = "UPDATE `staff` SET session_ID='$session_id',last_activity='$latest_time' WHERE staff_id='$user_ID' ";
                mysqli_select_db($KPI,$database_KPI);
                $Result1 =mysqli_query($KPI,$updateSQL) or die(mysqli_error($KPI));
                
                
                $insertSQL = "INSERT INTO user_logs (user_IP, access_date, access_type, staff_id) VALUES ('$user_IP','$latest_time','$access_type','$user_ID')";
                mysqli_select_db($KPI,$database_KPI);
                $Result2 =mysqli_query($KPI,$insertSQL) or die(mysqli_error($KPI));
                
                $successGoTo = $base_url."/dashboard";
                header("Location: ".$successGoTo);
            }
        }
        else
        {
            $FailedGoTo = $base_url."/adm/";
            header("Location: ". $FailedGoTo);
        }
    }
}


/* --------------------------------------------------
						Login Script
 -------------------------------------------------- */

if((isset($_POST['submit'])) && ($_POST['submit']=="login_form"))
{
    $staylog = ($_POST['stay_login'])?1:0;
    $loginusername = mysqli_real_escape_string($KPI, $_POST['uname']);
    $password = mysqli_real_escape_string($KPI, $_POST['pw']);
    
    // Get user account if username match
    mysqli_select_db($KPI,$database_KPI);
    $query_user = "SELECT * FROM `staff` WHERE email='$loginusername'";
    $user = mysqli_query($KPI , $query_user) or die(mysqli_error($KPI));
    $row_user = mysqli_fetch_assoc($user);
    $totalRows_user = mysqli_num_rows($user);
    
    if($totalRows_user > 0)
    {
        $check_pw = $row_user['password'];
        $user_ID = $row_user['staff_id'];
        $pID = $row_user['position_id'];
        
        if($pID==1)
        {
            if(password_verify($password,$check_pw))
            {
                // If login success, goes to...
                $MM_redirectLoginSuccess = $base_url."/adm/dashboard/";

                // If login fail, goes to...
                $MM_redirectLoginFailed2 = "index.php?status=inactive";
                $MM_redirectLoginFailed = "index.php?login=fail";
                $MM_redirecttoReferrer = true;

                mysqli_select_db($KPI, $database_KPI);
                $LoginRS_query = "SELECT * FROM `staff` WHERE email='$loginusername'";
                $LoginRS = mysqli_query($KPI,$LoginRS_query) or die(mysqli_error($KPI));
                $row_LoginRS = mysqli_fetch_assoc($LoginRS);
                $loginFoundUser=mysqli_num_rows($LoginRS);

                $status = $row_LoginRS['staffstatus'];
                $position = $row_LoginRS['position_id'];

                if($loginFoundUser)
                {
                    $loginStrGroup ="";
                    if($status==1)
                    {
                        if(PHP_VERSION>=5.1)
                        {
                            session_regenerate_id(true);
                        }
                        else
                        {
                            session_regenerate_id();
                        }

                        $session_id = session_id();
                        $latest_time = date("Y-m-d H:i:s");
                        $user_IP = $_SERVER['REMOTE_ADDR'];
                        $access_type = 1;

                        if($staylog == 1)
                        {
                            setcookie('staylog', $user_ID, time() + (86400*365));
                        }

                        // Clear and reset session
                        $_SESSION['MM_Username_Adm'] = NULL;


                        // Declare two session variables and assign them
                        $_SESSION['MM_Username_Adm'] = $row_LoginRS['staff_id'];

                        // Clear Lockout History
                        $updateSQL = "UPDATE `staff` SET session_ID='$session_id',last_activity='$latest_time' WHERE staff_id='$user_ID' ";
                        mysqli_select_db($KPI,$database_KPI);
                        $Result1 = mysqli_query($KPI,$updateSQL) or die(mysqli_error($KPI));

                        $insertSQL = "INSERT INTO user_logs (user_IP,access_date,access_type,staff_id) VALUES ('$user_IP','$latest_time','$access_type','$user_ID')";
                        mysqli_select_db($KPI,$database_KPI);
                        $Result2 = mysqli_query($KPI,$insertSQL) or die(mysqli_error($KPI));

                        $successGoTo = $base_url."/dashboard";
                        header("Location: ". $successGoTo);
                    }
                    else if($status==0)
                    {
                        header("Location:  %s",$MM_redirectLoginFailed2);
                    }
                    else
                    {
                        header("Location:  %s", $MM_redirectLoginFailed );
                    }
                }
                else
                {
                    $FailedGoTo = "index.php?login=fail2";
                    header("Location: %s", $FailedGoTo);
                }
            }
            else
            {
                $FailedGoTo = "index.php?login=fail";
                header(sprintf("Location: %s", $FailedGoTo));
            }
        }
        else
        {
             $FailedGoTo = "index.php?login=fail";
             header(sprintf("Location: %s", $FailedGoTo));
        }
    }
    else
    {
        $togo = "index.php?login=fail";
        header(sprintf("Location: %s",$togo));
    }
}

?>

<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Login | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->

</head>
<body>

    <!-- FORM SECTION -->
    <section id="form-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div id="logo-div">
                        <img src="<?php echo $base_url; ?>/img/logo.png" alt="<?php echo $base_name; ?>" title="<?php echo $base_name; ?>">
                    </div>
                    <form name="login_form" method="POST" action="">
                        <div id="form-div">
                            <div id="form-title-div">
                                <p id="form-title">Welcome Back</p>
                                <p id="form-p">Login to manage your account.</p>
                            </div>
                            <?php if (isset($_GET['pw'])) { $register = $_GET['pw']; if ($register == 'success') { ?>
                            <div class="alert alert-success" role="alert">
                                <p class="alert-heading">Password Reset Successfully.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['reset'])) { $reset = $_GET['reset']; if ($reset == 'sent') { ?>
                            <div class="alert alert-success" role="alert">
                                <p class="alert-heading">The instructions to reset your password has been sent to your email. Please check your email.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['reset'])) { $reset = $_GET['reset']; if ($reset == 'success') { ?>
                            <div class="alert alert-success" role="alert">
                                <p class="alert-heading">Your password has been reset successfully. Please login with your new password.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['login'])) { $login = $_GET['login']; if ($login == 'fail') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Invalid login credentials. Please try to login again.</p>
                            </div>
                            <?php } } ?>
                            <?php if (isset($_GET['login'])) { $login = $_GET['login']; if ($login == 'pending') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Your account has not been verified yet. Please try again later or contact our support team regards to this issue.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['login'])) { $login = $_GET['login']; if ($login == 'block') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Your account has been temporarily disabled. Please contact our support team regards to this issue.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['process'])) { $process = $_GET['process']; if ($process == 'invalid') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Invalid Process.</p>
                            </div>
                            <?php } } ?>
                            <div class="input-div">
                                <label>Username</label>
                                <input type="text" class="form-control" id="username" name="uname" maxlength="150" required>
                            </div>
                            <div class="input-div">
                                <label>Password</label>
                                <input type="password" class="form-control" id="pw" name="pw" maxlength="30" required>
                            </div>
                            <div id="check-div" style="margin-bottom: 15px;">
                                <input class="form-check-input" type="checkbox" name="stay_login" id="stay_login">
                                <label class="form-check-label" for="stay_login">Stay Login</label>
                            </div>
                            <div id="input-btn-div">
                                <input type="hidden" name="submit" value="login_form">
                                <button id="general-btn" class="btn1">Login</button>
                            </div>
                            <div id="link-div" style="margin-top: 10px; margin-bottom: 0; text-align: center;">
                                <p id="link-p">
                                    <a href="<?php echo $base_url; ?>/forgot-password.php">Forgot Password?</a>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- END OF FORM SECTION -->

    <!-- FOOTER CONS SECTION -->
    <?php include "inc/footer-cons.php"; ?>
    <!-- END OF FOOTER CONS SECTION -->

</body>
</html>