<?php
require_once('script.php');
require_once('testing.function.php');

if(isset($_GET['tid']))
{
    $rid = mysqli_real_escape_string($KPI,$_GET['tid']);
    
    $sql = "SELECT * FROM `service` WHERE service_ID = '$rid'";
    $query = mysqli_query($KPI,$sql) or mysqli_error($KPI);
    $row = mysqli_fetch_assoc($query);
    $total = mysqli_num_rows($query);
    
    if($total>0)
    {
        $commission=Commission($row['amount']);

        $totalCommission=bonus($row['cus_rating'],$commission);
        $myrating=Cbonus($row['cus_rating'],$commission);
        $rating=$row['cus_rating'];
        echo 
        '
        <br>My Sale (RM): '.$commission.'
        <br>My Rating Gain: '.$rating.'
        <br>My Rating (RM) : '.$myrating.'
        <br>My Commission for total Sales (RM): '.$totalCommission.'
        ';
    }
    else
    {
        
        $togo = "testing.php";
        header(sprintf("Location: %s",$togo));
    }

}
else
{
    $togo = "testing.php";
    header(sprintf("Location: %s",$togo));
    
}
?>

