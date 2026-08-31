<?php
    require_once('script.php');
    $pg = "member";
    $createddate=date("Y-m-d H:i:s");

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // --------------------------------------------------------------------------------------
    require ("../PHPMailer/src/Exception.php");
    require ("../PHPMailer/src/PHPMailer.php");
    require ("../PHPMailer/src/SMTP.php");
    $date = date("Y");
    $mail = new PHPMailer(true);



    //Add Position
    if(isset($_POST['add']) && ($_POST['add']=="add_form"))
    {
        $name = mysqli_real_escape_string($KPI, $_POST['name']);
        $gender = mysqli_real_escape_string($KPI, $_POST['gender']);
        $ic = mysqli_real_escape_string($KPI, $_POST['ic']);
        $dob = mysqli_real_escape_string($KPI,$_POST['dob']);
        $contact = mysqli_real_escape_string($KPI, $_POST['phone']);
        $email = mysqli_real_escape_string($KPI, $_POST['email']);
        $staff_address = mysqli_real_escape_string($KPI, $_POST['Adetails']);
        $postcode = mysqli_real_escape_string($KPI, $_POST['pcode']);
        $city = mysqli_real_escape_string($KPI, $_POST['city']);
        $state = mysqli_real_escape_string($KPI, $_POST['states']);
        $datejointeam = mysqli_real_escape_string($KPI,$_POST['jtdate']);;
        $datejoincompany = mysqli_real_escape_string($KPI,$_POST['jcdate']);
        $rand = rand(1000000,10000000);
        $password = password_hash($rand,PASSWORD_DEFAULT);
        $staffstatus = 1 ;
        $position_id = mysqli_real_escape_string($KPI, $_POST['position']);
        $team_id = mysqli_real_escape_string($KPI, $_POST['team']);
        
        //------------------------BACKEND CHECKING EMAIL------------------------------------//
        mysqli_select_db($KPI,$database_KPI);
        $query_check = "SELECT * FROM `staff` WHERE deleted='0' AND email='$email'";
        $check = mysqli_query($KPI, $query_check);
        $row_check = mysqli_fetch_assoc($check);
        $totalrow_check = mysqli_num_rows($check);
        //------------------END OF BACKEND CHECKING EMAIL------------------------------------//
        
        //-------------------------------------Email----------------------------------------//
        $time = time();
        $staffid= "MEM".$time;
        $company_name = "Winnefy Enterprise";
        $header1 = "Account Registration | ".$company_name;
        
        $email_body1="";
        $email_bodypm="<!DOCTYPE html>
        <html xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">
        <head>
           <meta charset=\"utf8\">
           <meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
           <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
           <meta name=\"x-apple-disable-message-reformatting\">
           <title>Winneft Enterprise</title>
           <style>
              .hover-bg-brand-600:hover {
                 background-color: #0047c3 !important;
              }
              .hover-text-brand-700:hover {
                 color: #003ca5 !important;
              }
              .hover-underline:hover {
                 text-decoration: underline !important;
              }
              @media screen {
                 img {
                     max-width: 100%;
                 }
                 .all-font-sans {
                     font-family: -apple-system, \"Segoe UI\", sans-serif !important;
                 }
              }
              @media (max-width: 640px) {
                 u~div .wrapper {
                     min-width: 100vw;
                 }
                 .sm-block {
                     display: block !important;
                 }
                 .sm-h-16 {
                     height: 16px !important;
                 }
                 .sm-mt-16 {
                     margin-top: 16px !important;
                 }
                 .sm-py-16 {
                     padding-top: 16px !important;
                     padding-bottom: 16px !important;
                 }
                 .sm-px-16 {
                     padding-left: 16px !important;
                     padding-right: 16px !important;
                 }
                 .sm-py-24 {
                     padding-top: 24px !important;
                     padding-bottom: 24px !important;
                 }
                 .sm-text-14 {
                     font-size: 14px !important;
                 }
                 .sm-w-full {
                     width: 100% !important;
                 }
              }
           </style>
        </head>
        <body lang=\"en\" style=\"margin: 0; padding: 0; width: 100%; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #ffffff;\">
           <div style=\"display: none; line-height: 0; font-size: 0;\">
              Welcome to Winnefy Enterprise&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;
           </div> 
           <table class=\"wrapper all-font-sans\" style=\"width: 100%;\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
              <tr>
                 <td align=\"center\" style bgcolor=\"#ffffff\">
                     <table class=\"sm-w-full\" style=\"width: 640px;\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
                        <tr>
                           <td class=\"sm-px-16 sm-py-24\" style=\"padding-left: 40px; padding-right: 40px; padding-top: 48px; padding-bottom: 48px; text-align: left;\" bgcolor=\"#ffffff\" align=\"left\">
                              <div  style=\"text-align: center;\">
                                 <a href=\"$real_url/pm/index.php\" style=\"display: block; margin-bottom: 15px;\">
                                     <img src=\"$real_url/adm/img/winnefy_logo.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 120px; height: auto;\">
                                 </a>
                              </div>
                              <div style=\"text-align: center;\">
                                 <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px;\">You are receiving this mail because you have successfully signed up to Winnefy Enterprise.</p>
                                 <div><p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 5px; display: inline-block;\"><img src=\"$real_url/adm/img/user.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 15px; height: auto; margin-right: 5px;\">: <b>$email</b></p></div>
                                 <div><p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px; display: inline-block;\"><img src=\"$real_url/adm/img/key.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 15px; height: auto; margin-right: 5px;\">: <b>$rand</b></p></div>
                                 <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px;\">Please click the button below to login your profile.</p>
                                 <div style=\"margin-bottom: 15px;\">
                                     <a href=\"$real_url/pm\" style=\"color: #fff; background: #1896bd; font-size: 12px; font-weight: 500; line-height: 1.4; padding: 11px 20px;  border-radius: 50px; text-align: center; position: relative; letter-spacing: 1px; align-items: center; display: inline-flex; justify-content: center; transition: all 0.3s linear; text-decoration: unset;\">Click to Login</a>
                                 </div>
                                 <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 0; margin-top: 10px; padding-top: 10px; border-top: 1px solid #000; font-style: italic; font-weight: 700;\">This is an auto-generated email. Please do not reply to this email.</p>
                              </div>
                           </td>
                     </table>
                 </td>
              </tr>
           </table>
        </body>
        </html>";
        
        $email_bodymem=" <!DOCTYPE html>
        <html xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">
        <head>
           <meta charset=\"utf8\">
           <meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
           <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
           <meta name=\"x-apple-disable-message-reformatting\">
           <title>Winneft Enterprise</title>
           <style>
              .hover-bg-brand-600:hover {
                 background-color: #0047c3 !important;
              }
              .hover-text-brand-700:hover {
                 color: #003ca5 !important;
              }
              .hover-underline:hover {
                 text-decoration: underline !important;
              }
              @media screen {
                 img {
                     max-width: 100%;
                 }
                 .all-font-sans {
                     font-family: -apple-system, \"Segoe UI\", sans-serif !important;
                 }
              }
              @media (max-width: 640px) {
                 u~div .wrapper {
                     min-width: 100vw;
                 }
                 .sm-block {
                     display: block !important;
                 }
                 .sm-h-16 {
                     height: 16px !important;
                 }
                 .sm-mt-16 {
                     margin-top: 16px !important;
                 }
                 .sm-py-16 {
                     padding-top: 16px !important;
                     padding-bottom: 16px !important;
                 }
                 .sm-px-16 {
                     padding-left: 16px !important;
                     padding-right: 16px !important;
                 }
                 .sm-py-24 {
                     padding-top: 24px !important;
                     padding-bottom: 24px !important;
                 }
                 .sm-text-14 {
                     font-size: 14px !important;
                 }
                 .sm-w-full {
                     width: 100% !important;
                 }
              }
           </style>
        </head>
        <body lang=\"en\" style=\"margin: 0; padding: 0; width: 100%; word-break: break-word; -webkit-font-smoothing: antialiased; background-color: #ffffff;\">
           <div style=\"display: none; line-height: 0; font-size: 0;\">
              Welcome to Winnefy Enterprise&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;
           </div> 
           <table class=\"wrapper all-font-sans\" style=\"width: 100%;\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
              <tr>
                 <td align=\"center\" style bgcolor=\"#ffffff\">
                     <table class=\"sm-w-full\" style=\"width: 640px;\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
                        <tr>
                           <td class=\"sm-px-16 sm-py-24\" style=\"padding-left: 40px; padding-right: 40px; padding-top: 48px; padding-bottom: 48px; text-align: left;\" bgcolor=\"#ffffff\" align=\"left\">
                              <div  style=\"text-align: center;\">
                                 <a href=\"$real_url/member/index.php\" style=\"display: block; margin-bottom: 15px;\">
                                     <img src=\"$real_url/adm/img/winnefy_logo.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 120px; height: auto;\">
                                 </a>
                              </div>
                              <div style=\"text-align: center;\">
                                 <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px;\">You are receiving this mail because you have successfully signed up to Winnefy Enterprise.</p>
                                 <div><p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 5px; display: inline-block;\"><img src=\"$real_url/adm/img/user.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 15px; height: auto; margin-right: 5px;\">: <b>$email</b></p></div>
                                 <div><p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px; display: inline-block;\"><img src=\"$real_url/adm/img/key.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 15px; height: auto; margin-right: 5px;\">: <b>$rand</b></p></div>
                                 <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px;\">Please click the button below to login your profile.</p>
                                 <div style=\"margin-bottom: 15px;\">
                                     <a href=\"$real_url/member\" style=\"color: #fff; background: #1896bd; font-size: 12px; font-weight: 500; line-height: 1.4; padding: 11px 20px;  border-radius: 50px; text-align: center; position: relative; letter-spacing: 1px; align-items: center; display: inline-flex; justify-content: center; transition: all 0.3s linear; text-decoration: unset;\">Click to Login</a>
                                 </div>
                                 <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 0; margin-top: 10px; padding-top: 10px; border-top: 1px solid #000; font-style: italic; font-weight: 700;\">This is an auto-generated email. Please do not reply to this email.</p>
                              </div>
                           </td>
                     </table>
                 </td>
              </tr>
           </table>
        </body>
        </html>";
        
        
        //-------------------------------------END OF Email Member----------------------------------------//
        
        if($position_id==8)
        {
            $email_body1=$email_bodypm;
        }
        elseif($position_id!=8)
        {
            $email_body1=$email_bodymem;
        }
        
        //---------------------UPLOAD IMAGE-------------------------------------------//
        $target = "../../my_asset/img/";
        $rname = date("dmYHis");
        $temp1 = explode(".",$_FILES['upload']['name']);
        $pimg1_1 = $rname.'.'.end($temp1); //Rename Image File Name
        $pimg1_2 = $target.$pimg1_1;  //Link
        
   
        $type_image1 = strtolower(pathinfo($pimg1_1, PATHINFO_EXTENSION));
        $upload = 1;
        if($totalrow_check)
        {
            $togo = $_SERVER["PHP_SELF"]."?email=fail";
            header(sprintf("Location: %s",$togo));
        }
        else
        {
            if($_FILES['upload']['size']!=0)
            {
                if($_FILES['upload']['size']>10000000)
                {
                    $upload =0;
                }
                else
                {
                    $upload =1;
                }
                
                if($type_image1 !="png" && $type_image1 !="jpg" && $type_image1 !="jpeg")
                {
                    $upload =0;
                }
                else
                {
                    $upload =1;
                }
                
                //Upload Image
                if($upload == 1)
                {
                    if(move_uploaded_file($_FILES['upload']['tmp_name'],$pimg1_2))
                    {
                        try 
                        {
                            //Server settings
                            //$mail->SMTPDebug = 2;
                            $mail->Host = 'smtp.hostinger.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'noreply@kpiproject.winnefy.site';
                            $mail->Password = 'Phplayer@3029288';
                            $mail->SMTPSecure = 'ssl';
                            $mail->Port = 465;
                    
                            //Recipients
                            $mail->setFrom('noreply@kpiproject.winnefy.site', $company_name);
                            $mail->addAddress($email);
                            $mail->addReplyTo('noreply@kpiproject.winnefy.site', $company_name);
                    
                            //Content
                            $mail->isHTML(true);
                            $mail->Subject = $header1;
                            $mail->Body    = $email_body1;
                            $mail->CharSet="UTF-8";
                    
                            $mail->send();
                            // If Email Sent Successfully
                            // ----------------------------------------------------------------
                            
                            mysqli_select_db($KPI,$database_KPI);
                            $addstaffSQL = "INSERT INTO staff (staff_name , staffimg, gender , ic , dob , contact , email , staff_address , postcode , city , states , datejointeam , datejoincompany , password , staffstatus , position_id , team_id , createddate) VALUES ('$name','$pimg1_1','$gender','$ic','$dob','$contact','$email','$staff_address','$postcode','$city','$state','$datejointeam',' $datejoincompany','$password','$staffstatus','$position_id','$team_id' ,'$createddate')";
                            mysqli_select_db($KPI,$database_KPI);
                            $addstaffQuery = mysqli_query($KPI,$addstaffSQL) or die(mysqli_error($KPI));

                            $togo = "member.php?add=succ";
                            header(sprintf("Location: %s",$togo));
                    
                        } 
                        catch (Exception $e) 
                        {
                            // If Email Sent Failed
                            // ----------------------------------------------------------------
                            // ----------------------------------------------------------------
                            // ----------------------------------------------------------------
                            $failtogo = "member-add.php?add=fail";
                            header(sprintf("Location: %s", $failtogo));
                        }
                    }
                    else
                    {
                        $togo = $_SERVER["PHP_SELF"]."?upload=fail";
                        header(sprintf("Location: %s",$togo));
                    }
                }
                else
                {
                    $togo = $_SERVER["PHP_SELF"]."?format=fail";
                    header(sprintf("Location: %s",$togo));
                    
                }
            }
            else
            {
                try 
                {
                    //Server settings
                    //$mail->SMTPDebug = 2;
                    $mail->Host = 'smtp.hostinger.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'noreply@kpiproject.winnefy.site';
                    $mail->Password = 'Phplayer@3029288';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;

                    //Recipients
                    $mail->setFrom('noreply@kpiproject.winnefy.site', $company_name);
                    $mail->addAddress($email);
                    $mail->addReplyTo('noreply@kpiproject.winnefy.site', $company_name);

                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = $header1;
                    $mail->Body    = $email_body1;
                    $mail->CharSet="UTF-8";

                    $mail->send();
                    // If Email Sent Successfully
                    // ----------------------------------------------------------------
                    
                    $defaultimg="default.jpg";

                    mysqli_select_db($KPI,$database_KPI);
                    $addstaffSQL = "INSERT INTO staff (staff_name , staffimg, gender , ic , dob , contact , email , staff_address , postcode , city , states , datejointeam , datejoincompany , password , staffstatus , position_id , team_id , createddate) VALUES ('$name','$defaultimg','$gender','$ic','$dob','$contact','$email','$staff_address','$postcode','$city','$state','$datejointeam',' $datejoincompany','$password','$staffstatus','$position_id','$team_id' ,'$createddate')";
                    mysqli_select_db($KPI,$database_KPI);
                    $addstaffQuery = mysqli_query($KPI,$addstaffSQL) or die(mysqli_error($KPI));

                    $togo = "member.php?add=succ";
                    header(sprintf("Location: %s",$togo));

                } 
                catch (Exception $e) 
                {
                    // If Email Sent Failed
                    // ----------------------------------------------------------------
                    // ----------------------------------------------------------------
                    // ----------------------------------------------------------------
                    $failtogo = "member-add.php?add=fail";
                    header(sprintf("Location: %s", $failtogo));
                }
            }
        }
        
        
        //---------------------END OF UPLOAD IMAGE-------------------------------------------//
        
       
   
    }

?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Add Member | <?php echo $base_name; ?></title>
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
                    
                    <a href="<?php echo $base_url_dashboard; ?>/member.php">Member</a>
                    <span id="bc-arrow"><i class="ri-arrow-right-s-line"></i></span>
                    
                    <span id="bc-active">Add Member</span>
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
                                    <form name="add_form" method="POST" enctype="multipart/form-data" action="">
                                        <p id="form-sub-title">Add Member</p>
                                        <!-- FORM SECTION -->
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="profile-img-div" class="profile-user">
                                                    <img src="<?php echo $real_url; ?>/my_asset/img/default.jpg" alt="Profile" title="Profile"class="user-profile-image">
                                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                                        <input type="file" class="profile-img-file-input" id="profile-img-file-input" name="upload">
                                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                                            <span class="avatar-title rounded-circle text-body">
                                                                <i class="ri-camera-fill"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Name<span>*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name" maxlength="200" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>IC No<span>*</span></label>
                                                    <input type="text" class="form-control" id="ic" name="ic" maxlength="30" required onKeyPress="return validateHP(this, event);" placeholder="______-__-____" data-slots="_">
                                                </div>
                                            </div>
                                           
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Gender<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="gender" name="gender" required>
                                                        <option selected disabled value="">Select Gender</option>
                                                        <option value="Male">MALE</option>
                                                        <option value="Female">FEMALE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Birth Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="dob" name="dob" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Contact No.<span>*</span></label>
                                                    <input type="tel" class="form-control" id="phone" name="phone" maxlength="30" required onKeyPress="return validateHP(this, event);">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>Email Address<span>*</span></label>
                                                        <input type="mail" class="form-control" id="email" name="email" maxlength="200" required>
                                                        <span id="emailcheck"></span>
                                                    </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Position<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="position" name="position" required>
                                                        <option selected disabled value="">Select Position</option>
                                                         <?php 
                                                            if($totalrow_position>0)
                                                            {
                                                                do
                                                                {
                                                                    $pid = $row_position['position_ID'];
                                                                    $positionDisplay = $row_position['position_name'];
                                                                    $deletedS = $row_position['deleted'];
                                                                    if($deletedS==0 )
                                                                    {


                                                        ?>
                                                        <option value="<?php echo $pid; ?>"><?php echo $positionDisplay; ?></option>
                                                        <?php 
                                                
                                                                    } 
                                                                }while($row_position=mysqli_fetch_assoc($position));
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <div class="input-group">
                                                        <label>Join Company Date<span>*</span></label>
                                                        <input type="date" class="form-control" id="jcdate" name="jcdate" required>
                                                    </div>
                                               </div>
                                            </div> 
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Team<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="team" name="team" required>
                                                        <option selected disabled value="">Select Team</option>
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
                                                        <option value="<?php echo $tid;?>"><?php echo $teamDisplay;?></option>
                                                         <?php 
                                                
                                                                    } 
                                                                }while($row_team=mysqli_fetch_assoc($team));
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <div class="input-group">
                                                        <label>Join Team Date<span>*</span></label>
                                                        <input type="date" class="form-control" id="jtdate" name="jtdate" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <label>Address Details<span>*</span></label>
                                                    <input type="text" class="form-control" id="Adetails" name="Adetails" maxlength="250" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Postcode<span>*</span></label>
                                                    <input type="text" class="form-control" id="pcode" name="pcode" maxlength="5" required onKeyPress="return validateHP(this, event);">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>City<span>*</span></label>
                                                    <input type="text" class="form-control" id="city" name="city" maxlength="150" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>States<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="states" name="states" required>
                                                        <option selected disabled value="">Select States</option>
                                                        <option value="Johor">Johor</option>
                                                        <option value="Kedah">Kedah</option>
                                                        <option value="Kelantan">Kelantan</option>
                                                        <option value="Malacca">Malacca</option>
                                                        <option value="Negeri Sembilan">Negeri Sembilan</option>
                                                        <option value="Pahang">Pahang</option>
                                                        <option value="Penang">Penang</option>
                                                        <option value="Perak">Perak</option>
                                                        <option value="Perlis">Perlis</option>
                                                        <option value="Sabah">Sabah</option>
                                                        <option value="Sarawak">Sarawak</option>
                                                        <option value="Selangor">Selangor</option>
                                                        <option value="Terengganu">Terengganu</option>
                                                        <option value="Kuala Lumpur">Kuala Lumpur</option>
                                                        <option value="Labuan">Labuan</option>
                                                        <option value="Putrajaya">Putrajaya</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="form-btn-div">
                                                    <a href="<?php echo $base_url_dashboard; ?>/member.php?uid=<?php echo $uid?>" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                                                    <input type="hidden" name="add" value="add_form">
                                                    <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-fill"></i>Save Changes</button>
                                                </div>
                                            </div>
                                             <!-- FORM SECTION -->
                                        </div>
                                    </form>
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
    
    <!-- PROFILE PICTURES JS -->
    <script src="<?php echo $base_url; ?>/assets/js/pages/profile-setting.init.js"></script>
    
    <!--AJAX CHECKING EMAIL -->
    <script>
        $("#email").on('change',function(){
            
            var email = document.getElementById("email").value;
            $.ajax({
                type: "POST",
                url: "checking.php",
                data:{a: email},
                success:function(data)
                {
                    if(data == 0)
                    {
                        document.getElementById("emailcheck").style.display = "none";
                        document.getElementsByClassName("btn1")[0].disabled = false;
                    }
                    else if(data == 1)
                    {
                        document.getElementById("emailcheck").style.display = "block";
                        $("#emailcheck").html('<em style="color: #fff; border: 1px solid #fd6074; background-color: #fd6074; padding: 3px; display: block; margin-top: 2px; font-style: normal; font-size: 11px; line-height: 15px;">Username is already Exist!</em>');
                        document.getElementsByClassName("btn1")[0].disabled = true;
                    }
                }
                
            })
        })
    </script>
    <!--END OF AJAX CHECKING EMAIl -->
    
    
    
</body>
</html>