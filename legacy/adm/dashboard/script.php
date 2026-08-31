<?php 

require_once('../../cons/KPIDB.php');
require_once('../php/class/crud.class.php');
require_once('../php/class/ajaxdatatable.class.php');

if (!isset($_SESSION)) 
{
  session_start();
}

$real_url = "https://memberportal.winnefy.xyz/";
$base_url = "https://memberportal.winnefy.xyz/adm";
$base_url_dashboard = "https://memberportal.winnefy.xyz/adm/dashboard";
$base_name = "Winnefy";

// ------------------------------------------------------------------------------
// 								Admin Login Details
// ------------------------------------------------------------------------------
if(isset($_SESSION['MM_Username_Adm']))
{
    $colname_Logged_In_User_Adm = "-1";
    if(isset($_SESSION['MM_Username_Adm']))
    {
        $colname_Logged_In_User_Adm = mysqli_real_escape_string($KPI,$_SESSION['MM_Username_Adm']);
    }

    $query_Logged_In_User = "SELECT * FROM `staff` WHERE staff_id='$colname_Logged_In_User_Adm'";
    $Logged_In_User = mysqli_query($KPI, $query_Logged_In_User) or die(mysqli_error($KPI));
    $row_Logged_In_User = mysqli_fetch_assoc($Logged_In_User);
    $totalRows_Logged_In_User = mysqli_num_rows($Logged_In_User);

    $ori_user_ID = $row_Logged_In_User['staff_id'];
    $user_session_ID = $row_Logged_In_User['session_ID'];

    $user_ID = $row_Logged_In_User['staff_id'];
    
/* --------------------------------------------------
                        Logout Script
    -------------------------------------------------- */
    $logoutAction = $_SERVER['PHP_SELF']."?doLogout=true";
    if((isset($_SERVER['QUERY_STRING'])) && ($_SERVER['QUERY_STRING']!=""))
    {
        $logoutAction .="&". htmlentities($_SERVER['QUERY_STRING']);
    }

    if((isset($_GET['doLogout'])) && ($_GET['doLogout']=="true"))
    {
        //to fully log out a visitor we need to clear the session variable
        $_SESSION['MM_Username_Adm'] = NULL;
        $_SESSION['MM_UserGroup_Adm'] = NULL;
        $_SESSION['PrevUrl'] = NULL;
        $_SESSION['MM_NewSession_Adm'] = NULL;
        $_SESSION['FirstLoginSession'] = NULL;

        unset($_SESSION['MM_Username_Adm']);
        unset($_SESSION['MM_UserGroup_Adm']);
        unset($_SESSION['PrevUrl']);
        unset($_SESSION['MM_NewSession_Adm']);
        unset($_SESSION['FirstLoginSession']);

        setcookie("staylog","",time()-3600);

        $updateSQL = "UPDATE `staff` SET session_ID=NULL, last_activity=NULL WHERE staff_id='$ori_user_ID'";
        mysqli_select_db($KPI,$database_KPI);
        $Result1= mysqli_query($KPI,$updateSQL) or die(mysqli_error($KPI));

        //Insert User Logs
        $user_IP = $_SERVER['REMOTE_ADDR'];
        $latest_time = date("Y-m-d H:i:s");
        $access_type = 0;

        $insertSQL = "INSERT INTO user_logs (user_IP, access_date, access_type, staff_id) VALUES ('$user_IP', '$latest_time', '$access_type', '$ori_user_ID')";
        $Result2 = mysqli_query($KPI, $insertSQL) or die(mysqli_error($KPI));

        $logoutGoTo = "$base_url/index.php?logout=true";
    
        if($logoutGoTo)
        {
            header("Location: $logoutGoTo");
            exit;
        }
    }
}

if(!isset($_SESSION['MM_Username_Adm']))
{
    $hometogo = "$base_url/index.php";
    
    header("Location: $hometogo");
}

?>