<?php 
require_once('script.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --------------------------------------------------------------------------------------
require ("PHPMailer/src/Exception.php");
require ("PHPMailer/src/PHPMailer.php");
require ("PHPMailer/src/SMTP.php");
$date = date("Y");
$mail = new PHPMailer(true);

if(isset($_GET['email']))
{
     $email = mysqli_real_escape_string($KPI, $_GET['email']);

     mysqli_select_db($KPI, $database_KPI);
     $query_user = "SELECT * FROM `staff` WHERE email='$email'";
     $user = mysqli_query($KPI, $query_user) or die(mysqli_error($KPI));
     $row_user = mysqli_fetch_assoc($user);
     $totalrow_user = mysqli_num_rows($user);

     $rand = md5(rand(1000, 100000));
     $company_name = "Winnefy Enterprise";

     $header1 = "FORGOT PASSWORD | ".$company_name;
     $email_body1 = "<!DOCTYPE html>
     <html xmlns:v=\"urn:schemas-microsoft-com:vml\" xmlns:o=\"urn:schemas-microsoft-com:office:office\">
     <head>
         <meta charset=\"utf8\">
         <meta http-equiv=\"x-ua-compatible\" content=\"ie=edge\">
         <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
         <meta name=\"x-apple-disable-message-reformatting\">
         <title>Winnefy Enterprise</title>
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
             Nice to have you on board, Candy!&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;&zwnj;&#160;
         </div>
         <table class=\"wrapper all-font-sans\" style=\"width: 100%;\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
             <tr>
                 <td align=\"center\" style bgcolor=\"#ffffff\">
                     <table class=\"sm-w-full\" style=\"width: 640px;\" cellpadding=\"0\" cellspacing=\"0\" role=\"presentation\">
                         <tr>
                             <td class=\"sm-px-16 sm-py-24\" style=\"padding-left: 40px; padding-right: 40px; padding-top: 48px; padding-bottom: 48px; text-align: left;\" bgcolor=\"#ffffff\" align=\"left\">
                                 <div  style=\"text-align: center;\">
                                     <a href=\"https://kpiproject.winnefy.site/adm\" style=\"display: block; margin-bottom: 15px;\">
                                         <img src=\"$base_url/img/winnefy_logo.png\" alt=\"Winnefy Enterprise\" title=\"Winnefy Enterprise\" style=\"width: 90px; height: auto;\">
                                     </a>
                                 </div>
                                 <div style=\"text-align: center;\">
                                     <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 0;\">You are receiving this email because you made a request to change your login password.</p>
                                     <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 15px;\">Please click the button below to continue your reset password progress.</p>
                                     <div style=\"margin-bottom: 15px;\">
                                         <a href=\"$base_url/reset-password.php?email=$email&token=$rand\" style=\"color: #fff; background: #1896bd; font-size: 12px; font-weight: 500; line-height: 1.4; padding: 11px 20px;  border-radius: 50px; text-align: center; position: relative; letter-spacing: 1px; align-items: center; display: inline-flex; justify-content: center; transition: all 0.3s linear; text-decoration: unset;\">Click to Reset Password</a>
                                     </div>
                                     <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 0; margin-top: 15px;\">This link only valid for one time password change.</p>
                                     <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 0;\">If you did not make this request, kindly ignore this email, your password has not been changed.</p>
                                     <p style=\"color: #000; margin-top: 0; font-size: 13px; line-height: 1.4; margin-bottom: 0; margin-top: 10px; padding-top: 10px; border-top: 1px solid #000; font-style: italic; font-weight: 700;\">This is an auto-generated email. Please do not reply to this email.</p>
                                 </div>
                             </td>
                     </table>
                 </td>
             </tr>
         </table>
     </body>
     </html>";
     try {
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

          $update = "UPDATE `staff` SET token='$rand' WHERE email='$email'";
          mysqli_select_db($KPI, $database_KPI);
          $result = mysqli_query($KPI, $update) or die(mysqli_error($KPI));
          
          $success = "index.php?reset=sent";
          header(sprintf("Location: %s",$success));

     } catch (Exception $e) {
          // If Email Sent Failed
          // ----------------------------------------------------------------
          // ----------------------------------------------------------------
          // ----------------------------------------------------------------
          $failtogo = "forgot-password.php?email=fail";
          header(sprintf("Location: %s", $failtogo));
     }
}
?>