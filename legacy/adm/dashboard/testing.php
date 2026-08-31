<?php

    require_once('script.php');
    require_once('testing.function.php');
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Sending Testing | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->
    <!-- COLOR THEME SECTION -->
    <?php include "inc/color-dark-theme.php"; ?>
    <!-- END OF COLOR THEME SECTION -->
    <!-- DATATABLE CSS -->
    <?php include "inc/datatable-css.php"; ?>
    <!-- END OF DATATABLE CSS -->
</head>

<body>
    <div class="page-content">
        <!-- SECTION -->
        <section id="general-section">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div id="tb-box" class="general-box">
                            <div id="tb-border-line">
                                <div id="tb-title-div">
                                    <p id="tb-title">Commission Pay</p>
                                    
                                </div>
                            </div>
                            <div id="table-padding">
                                <div id="table-div">
                                    <table id="example" class="table table-bordered nowrap table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Service ID</th>
                                                <th class="text-center">Service Date</th>
                                                <th>Service Type</th>
                                                <th class="text-center">Amount</th>
                                                <th>Member</th>
                                                <th class="text-center">Rating</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            displaydate($KPI);
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="tb-border-line">
                                <div id="tb-title-div">
                                    <p id="tb-title">
                                    <?php 
                                        countCommisionMain($KPI);
                                    ?>
                                    </p>
                                </div>
                            </div>
                            <div id="tb-border-line">
                                <div id="tb-title-div">
                                    <p id="tb-title">
                                    <?php 
                                      countleaderCommisionMain($KPI);
                                    ?>
                                    </p>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- END OF SECTION -->
    </div>
    <!-- FOOTER CONS SECTION -->
    <?php include "inc/footer-cons.php"; ?>
    <!-- END OF FOOTER CONS SECTION -->
    
    <!-- DATATABLE JS -->
    <?php include "inc/datatable-js.php"; ?>
    <!-- END OF DATATABLE JS -->
</body>
</html>
<?php


function displaydate($dbconn)
{
   
    $sql = "SELECT a.*, b.staff_name FROM `service` a LEFT JOIN `staff` b ON b.staff_id = a.member_ID";
    $query = mysqli_query($dbconn,$sql) or mysqli_error($dbconn);
    $totalrow = mysqli_num_rows($query);

    if($totalrow>0)
    {
        while($row = mysqli_fetch_assoc($query))
        {
            $row['service']=rservicetype($row['service']);
            echo
            '                     
            <tr>
                <td class="text-center">'.$row['service_ID'].'</td>
                <td class="text-center">'.$row['service_date'].'</td>
                <td>'.$row['service'].'</td>
                <td class="text-center">'.$row['amount'].'</td>
                <td>'.$row['member_ID'].'<br>'.$row['staff_name'].'</td>
                <td class="text-center">'.$row['cus_rating'].'</td>
                <td class="text-center">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-fill align-middle"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a href="'.$GLOBALS['base_url_dashboard'].'/result.php?tid='.$row['service_ID'].'" class="dropdown-item"><i class="ri-eye-line me-2"></i>View Comission</a></li>
                        </ul>
                    </div>
                </td>
            </tr>
            ';
                                           
        }
    }
}

function rservicetype($type)
{
    if($type==1)
    {
        $type='Tint';
    }
    elseif($type==2)
    {
        $type='Coating';
    }
    
    return $type;
    
}

function countCommisionMain($dbconn)
{
    $sql ="SELECT * FROM `service` GROUP BY member_ID";
    
    $query = mysqli_query($dbconn,$sql) or mysqli_error($dbconn);
    $totalrow = mysqli_num_rows($query);
    
    if($totalrow>0)
    {

        echo"<br><br> Result:<br>";
        countCommision1($query,$dbconn);

    }
    else
    {
        echo 'Nothing in Database';
    }   
}

function countCommision1($QUERY,$dbconn)
{

    while($row=mysqli_fetch_assoc($QUERY))
    {
        $userid=$row['member_ID'];
            
        $sql = 
        " 
        SELECT a.* , b.* , c.*
        FROM `service` a  
        LEFT JOIN `staff` b 
        ON a.member_ID = b.staff_id  
        LEFT JOIN `leader` c
        ON b.leader_id = c.leader_id
        WHERE a.member_ID='$userid' AND a.service_date BETWEEN '2023-01-01' AND '2023-03-31'
        ";
        $query = mysqli_query($dbconn,$sql) or mysqli_error($dbconn);
        $totalrow = mysqli_num_rows($query);
        
  
        echo "<br>Member ID: ".$userid."  &nbsp;&nbsp;Total Commision: ".countCommision2($query,$dbconn)."<br>";

    }
    
}

function countCommision2($QUERY,$dbconn)
{
    $total=0;
    

    while($row=mysqli_fetch_assoc($QUERY))
    {
        $leader = $row['leader_id'];
        
        $commission=Commission($row['amount']);

        $totalCommission=bonus($row['cus_rating'],$commission);
        $total=$total+$totalCommission;
    }
    
    
    return $total;
}

function countleaderCommisionMain($dbconn)
{
    $sql= "SELECT * FROM `leader`";
    $query= mysqli_query($dbconn,$sql);
    $total = mysqli_num_rows($query);
    
    echo"<br><br> Result:<br>";
    if($total>0)
    {
        while($row=mysqli_fetch_assoc($query))
        {
            echo "<br>".$row['leader_name']." : ".leadercommission($dbconn,$row['leader_id'])."<br>";
        }
    }
    
}

function leadercommission($dbconn,$thisleader)
{
    $sql= 
    "
    SELECT x.leader_name, x.leader_id,  y.member_ID, y.total_sales, y.total_amount
    
    FROM `leader` x
    
    LEFT JOIN 
    
    (SELECT a.member_ID AS member_ID, COUNT(a.service_ID) AS total_sales , SUM(a.amount) AS total_amount , b.leader_id 
    
    FROM `service` a 
    
    LEFT JOIN `staff` b  
    
    ON a.member_ID = b.staff_id 
    
    WHERE a.service_date BETWEEN '2023-01-01' AND '2023-03-31' GROUP BY a.member_ID) y
    
    ON x.leader_id = y.leader_id
    
    WHERE x.leader_id = '$thisleader'
    
    ";
    
    $query =mysqli_query($dbconn,$sql) or die (mysqli_error($dbconn));
    $total = mysqli_num_rows($query);
    
    if($total>0)
    {
        $commission=0;
        
        while($row=mysqli_fetch_assoc($query))
        {
            if($row['total_sales']>=35 && $row['total_amount']>=3000)
            {
                $commission = $commission + 800;
            }
            else
            {
                $commission = $commission + 0;
            }
        }
    }
    
    return $commission;
}


?>
