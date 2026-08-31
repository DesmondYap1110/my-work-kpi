<?php
require_once('script.php');

if(isset($_POST['a']))
{
    $email = mysqli_real_escape_string($KPI, $_POST['a']);
    mysqli_select_db($KPI,$database_KPI);
    $query_check = "SELECT * FROM `staff` WHERE email='$email'";
    $check = mysqli_query($KPI, $query_check) or die(mysqli_error($KPI));
    $row_check=mysqli_fetch_assoc($check);
    $totalrow_check = mysqli_num_rows($check);
    
    if($totalrow_check > 0)
    {
        echo "1";
    }
    else
    {
        echo "0";
    }
}


?>