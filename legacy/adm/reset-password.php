<?php
    require_once('script.php');

    if(isset($_GET['email']) && isset($_GET['token']))
    {
        $email = mysqli_real_escape_string($KPI, $_GET['email']);
        $token = mysqli_real_escape_string($KPI, $_GET['token']);
        
        mysqli_select_db($KPI,$database_KPI);
        $query_user = "SELECT * FROM `staff` WHERE email='$email' AND token='$token'";
        $user = mysqli_query($KPI,$query_user) or die(mysqli_error($KPI));
        $row_user = mysqli_fetch_assoc($user);
        $totalrow_user = mysqli_num_rows($user);
        
        if(!$totalrow_user)
        {
            $togo = "index.php";
            header(sprintf("Location: %s", $togo));
        }
        
    }
    else
    {
        $togo ="index.php";
        header(sprintf("Location: %s",$togo));
    }

    
    if(isset($_POST['submit']) && ($_POST['submit'] == "reset_psw_form"))
    {
        $pw = mysqli_real_escape_string($KPI , $_POST['pw']);
        $cpw = mysqli_real_escape_string($KPI , $_POST['cpw']);
        
        if($pw == $cpw)
        {
            $pwhash = password_hash($pw, PASSWORD_DEFAULT);
            $update = "UPDATE `staff` SET password = '$pwhash', token=NULL WHERE email='$email'";
            mysqli_select_db($KPI,$database_KPI);
            $result = mysqli_query($KPI,$update) or die (mysqli_error($KPI));
            
            $togo = "index.php?reset=success";
            header(sprintf("Location: %s", $togo));
            
        }
        else
        {
            $togo = "reset-password.php?email=$email&token=$token&pw=unmatch";
            header(sprintf("Location: %s",$togo));
            
        }
    }
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Reset Password | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->


    <style type="text/css">
        #password-contain {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 0.5rem;
            background: linear-gradient(58deg, #151b24, #1d2634);
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
                    <form name="reset_psw_form" method="POST" action="">
                        <div id="form-div">
                            <div id="form-title-div">
                                <p id="form-title">Reset Password</p>
                                <p id="form-p">Please insert your new login password.</p>
                            </div>
                            <?php if (isset($_GET['pw'])) { $pw = $_GET['pw']; if ($pw == 'unmatch') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Sorry, passwords not match. Please try again.</p>
                            </div>
                            <?php } } ?>

                            <?php if (isset($_GET['pw'])) { $pw = $_GET['pw']; if ($pw == 'invalid') { ?>
                            <div class="alert alert-danger" role="alert">
                                <p class="alert-heading">Your password must contain at least 8 characters, 1 number, 1 capital letter and 1 lowercase letter.</p>
                            </div>
                            <?php } } ?>
                            <div class="input-div">
                                <label>Password</label>
                                <div id="psw-div">
                                    <input type="password" class="form-control" id="psw" name="pw" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                    <span id="psw-visable-icon" onclick="pw1()"><i class="ri-eye-fill" id="open-eye1"></i><i class="ri-eye-off-fill" id="close-eye1"></i></span>
                                </div>
                                <span id="note-p" class="note-color-1">Must be at least 8 characters.</span>
                            </div>
                            <div id="password_contain">
                                <p id="pw-ct-title">Password Must Contain :</p>
                                <p id="length" class="invalid">Minimum <b>8 characters</b></p>
                                <p id="letter" class="invalid">At <b>lowercase</b> letter (a-z)</p>
                                <p id="capital" class="invalid">At least <b>uppercase</b> letter (A-Z)</p>
                                <p id="number" class="invalid">A least <b>number</b> (0-9)</p>
                            </div>
                            <div class="input-div">
                                <label>Confirm Password</label>
                                <div id="psw-div">
                                    <input type="password" class="form-control" id="c_psw" name="cpw" required>
                                    <span id="psw-visable-icon" onclick="pw()"><i class="ri-eye-fill" id="open-eye"></i><i class="ri-eye-off-fill" id="close-eye"></i></span>
                                </div>
                            </div>
                            <div id="input-btn-div">
                                <input type="hidden" name="submit" value="reset_psw_form">
                                <button id="general-btn" class="btn1">Reset Password</button>
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

    <script type="text/javascript">
        /*PASSWORD VALITATION*/
        var myInput = document.getElementById("psw");
        var letter = document.getElementById("letter");
        var capital = document.getElementById("capital");
        var number = document.getElementById("number");
        var length = document.getElementById("length");

        // When the user clicks on the password field, show the message box
        myInput.onfocus = function() {
            document.getElementById("password_contain").style.display = "block";
        }

        // When the user clicks outside of the password field, hide the message box
        myInput.onblur = function() {
            document.getElementById("password_contain").style.display = "none";
        }

        // When the user starts to type something inside the password field
        myInput.onkeyup = function() {
            // Validate lowercase letters
            var lowerCaseLetters = /[a-z]/g;
            if(myInput.value.match(lowerCaseLetters)) {  
                letter.classList.remove("invalid");
                letter.classList.add("valid");
            } else {
                letter.classList.remove("valid");
                letter.classList.add("invalid");
            }
          
            // Validate capital letters
            var upperCaseLetters = /[A-Z]/g;
              if(myInput.value.match(upperCaseLetters)) {  
                capital.classList.remove("invalid");
                capital.classList.add("valid");
            } else {
                capital.classList.remove("valid");
                capital.classList.add("invalid");
            }

            // Validate numbers
            var numbers = /[0-9]/g;
            if(myInput.value.match(numbers)) {  
                number.classList.remove("invalid");
                number.classList.add("valid");
            } else {
                number.classList.remove("valid");
                number.classList.add("invalid");
            }
          
            // Validate length
            if(myInput.value.length >= 8) {
                length.classList.remove("invalid");
                length.classList.add("valid");
            } else {
                length.classList.remove("valid");
                length.classList.add("invalid");
            }
        }
    </script>

    <script>
        var oe = document.getElementById("open-eye");
        var ce = document.getElementById("close-eye");
        var input = document.getElementById("c_psw");
        oe.style.display = "none";
        function pw(){
            if(input.type === "password")
            {
                input.type = "text";
                oe.style.display = "block";
                ce.style.display = "none";
            }
            else
            {
                input.type = "password";
                oe.style.display = "none";
                ce.style.display = "block";
            }
        }

        var oe1 = document.getElementById("open-eye1");
        var ce1 = document.getElementById("close-eye1");
        var input1 = document.getElementById("psw");
        oe1.style.display = "none";
        function pw1(){
            if(input1.type === "password")
            {
                input1.type = "text";
                oe1.style.display = "block";
                ce1.style.display = "none";
            }
            else
            {
                input1.type = "password";
                oe1.style.display = "none";
                ce1.style.display = "block";
            }
        }
    </script>
</body>
</html>