<?php
    //admin forgot password page
    require_once('script.php');
    $positionid = 1;
    $sstaff = 1;  

    if(isset($_POST['submit']) && ($_POST['submit'] == "forgot_psw_form"))
    {
        $email = mysqli_real_escape_string($KPI, $_POST['email']);
        mysqli_select_db($KPI, $database_KPI);
        $query_user = "SELECT * FROM `staff` WHERE email = '$email' AND position_id='$positionid' AND staffstatus='$sstaff'";
        $user = mysqli_query($KPI,$query_user) or die(mysqli_error($KPI));
        $row_user = mysqli_fetch_assoc($user);
        $totalrow_user = mysqli_num_rows($user);
        
        if($totalrow_user>0)
        {
            $togo = "forgot_email.php?email=$email";
            header(sprintf("Location: %s",$togo));
        }
        else
        {
            $togo = "forgot-password.php?acc=invalid";
            header(sprintf("Location: %s",$togo));
        }
    }
?>

<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Forgot Password | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->

  
    <style type="text/css">
        #secure-btn-div button {
            width: 100%;
        }
    </style>
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
                    <form name="forgot_psw_form" method="POST" action="">
                        <div id="form-div">
                            <div id="form-title-div">
                                <p id="form-title">Forgot Password</p>
                                <p id="form-p">Please insert the email address that registered with us.</p>
                            </div>
                            <?php if (isset($_GET['acc'])) { $acc = $_GET['acc']; if ($acc == 'invalid') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">The email you entered does not belongs to any account.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['email'])) { $email = $_GET['email']; if ($email == 'fail') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Sorry, we are currently facing technical issues. Please try again later or you may contact our support team with regards to this issue.</p>
                            </div>
                            <?php } } ?>
                            <div class="input-div">
                                <label>Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="150" required>
                            </div>
                            <div id="link-div">
                                <p id="link-p">
                                    Remember your password? <a href="<?php echo $base_url; ?>">Back to Login</a>
                                </p>
                            </div>
                            <div id="secure-btn-div">
                                <input type="hidden" name="submit" value="forgot_psw_form">
                                <button id="general-btn" class="btn1">Submit</button>
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