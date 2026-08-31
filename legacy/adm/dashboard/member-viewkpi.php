<?php
    require_once('script.php');
    $pg = "member";

 
    if(isset($_GET['uid']) && $_GET['uid']!=NULL)
    {
        $uid = $_GET['uid'];
        mysqli_select_db($KPI,$database_KPI);
        $staffq="SELECT c.team_name, b.position_name, b.job_scope, a.staff_name, a.staffimg, a.gender, a.ic, a.dob , a.contact , a.email, a.staff_address, a.postcode, a.city, a.states, a.datejointeam , a.datejoincompany , a.staffstatus , a.position_id , a.team_id FROM `staff` a LEFT JOIN `staff_position` b ON a.position_id = b.position_ID LEFT JOIN `team` c ON a.team_id = c.team_id WHERE a.staff_id='$uid'";
        $staff= mysqli_query($KPI,$staffq) or die(mysqli_error($KPI));
        $row_staff = mysqli_fetch_assoc($staff);
    }

    if(isset($_POST['filter']) && ($_POST['filter']=="filter_form"))
    {
        //fp - filter project
        $fp = mysqli_real_escape_string($KPI,$_POST['fproject']);
        
        $togo="member-viewkpi.php?uid=$uid&pid=$fp";
        header(sprintf("location: %s",$togo));
    }

    if(isset($_GET['pid']) && $_GET['pid']!=NULL)
    {
        $fprojectid = mysqli_real_escape_string($KPI,$_GET['pid']);
        if($fprojectid == "")
        {
            $fproject="";
        }
        else
        {
            $fproject = "AND a.project_id='$fprojectid'";
        }
        
        mysqli_select_db($KPI,$database_KPI);
        $pkpi0sql="SELECT a.mark, a.status, a.createddate, d.kojbInfo_title FROM `project_kpi` a LEFT JOIN `project` b ON a.project_id = b.project_id LEFT JOIN `kpi_objective` c ON a.kpi_id = c.kpi_ID  AND  a.kojbInfo_id = c.kojbInfo_id LEFT JOIN `kpi_objective_info` d ON a.kojbInfo_id = d.kojbInfo_id WHERE b.p_status = '2' AND b.complete_date IS NOT NULL AND c.obj_type = '0' AND a.staff_id='$uid ' $fproject";
        $pkpi0= mysqli_query($KPI,$pkpi0sql) or die(mysqli_error($KPI));
        $row_pkpi0 = mysqli_fetch_assoc($pkpi0);
        $totalrow_pkpi0 = mysqli_num_rows($pkpi0);
        
        mysqli_select_db($KPI,$database_KPI);
        $pkpi1sql="SELECT a.mark, a.status, a.createddate, d.kojbInfo_title FROM `project_kpi` a LEFT JOIN `project` b ON a.project_id = b.project_id LEFT JOIN `kpi_objective` c ON a.kpi_id = c.kpi_ID  AND  a.kojbInfo_id = c.kojbInfo_id LEFT JOIN `kpi_objective_info` d ON a.kojbInfo_id = d.kojbInfo_id WHERE b.p_status = '2' AND b.complete_date IS NOT NULL AND c.obj_type = '1' AND a.staff_id='$uid ' $fproject";
        $pkpi1= mysqli_query($KPI,$pkpi1sql) or die(mysqli_error($KPI));
        $row_pkpi1 = mysqli_fetch_assoc($pkpi1);
        $totalrow_pkpi1 = mysqli_num_rows($pkpi1);
    }




    /* -----------------------------------------------------------------------
                        Total Mark KPI Calculation

    --------------------------------------------------------------------- */
    $memberpos=$row_staff['position_id'];

    $whatkpisql = "SELECT kpi_id FROM `kpi` WHERE position_ID='$memberpos'";
    $whatkpi = mysqli_query($KPI,$whatkpisql) or die(mysqli_error($KPI));
    $row_whatkpi = mysqli_fetch_assoc($whatkpi);
    $totalrow_whatkpi = mysqli_num_rows($whatkpi);

    $totalmarkkpi = 0;
    $averagekpi = 0;

    if($totalrow_whatkpi>0)
    {
        $kpimemberassign=$row_whatkpi['kpi_id'];

        $kpiobjmembersql = "SELECT * FROM `kpi_objective` WHERE kpi_ID='$kpimemberassign' AND deleted='0' AND kojbInfo_id !='4' ";
        $kpiobjmember = mysqli_query($KPI,$kpiobjmembersql) or die(mysqli_error($KPI));
        $row_kpiobjmember = mysqli_fetch_assoc($kpiobjmember);
        $totalrow_kpiobjmember = mysqli_num_rows($kpiobjmember);



        if($totalrow_kpiobjmember>0)
        {
            $totalm=0;

            do
            {
                $memobj_id = $row_kpiobjmember['obj_id'];
                $maxmarksql = "SELECT * FROM `kpi_objective_mark` WHERE obj_id='$memobj_id' AND objmk_2='1'";
                $maxmark = mysqli_query($KPI,$maxmarksql) or die(mysqli_error($KPI));
                $row_maxmark = mysqli_fetch_assoc($maxmark);
                $totalrow_maxmark = mysqli_num_rows($maxmark);

                if($totalrow_maxmark>0)
                {
                    $totalm += 2; 
                }
                else
                {
                    $maxmarkis1sql = "SELECT * FROM `kpi_objective_mark` WHERE obj_id='$memobj_id' AND objmk_1='1'";
                    $maxmarkis1 = mysqli_query($KPI,$maxmarkis1sql) or die(mysqli_error($KPI));
                    $row_maxmarkis1 = mysqli_fetch_assoc($maxmarkis1);
                    $totalrow_maxmarkis1 = mysqli_num_rows($maxmarkis1);

                    if($totalrow_maxmarkis1>0)
                    {
                        $totalm += 1;
                    }
                    else
                    {
                        $totalm += 0;
                    }
                }

            }
            while($row_kpiobjmember = mysqli_fetch_assoc($kpiobjmember));
            

            $kpiobj4existsql = "SELECT * FROM `kpi_objective` WHERE kpi_ID='$kpimemberassign' AND deleted='0' AND kojbInfo_id ='4'";
            $kpiobj4exist = mysqli_query($KPI,$kpiobj4existsql) or die(mysqli_error($KPI));
            $row_kpiobj4exist = mysqli_fetch_assoc($kpiobj4exist);
            $totalrow_kpiobj4exist = mysqli_num_rows($kpiobj4exist);

            if($totalrow_kpiobj4exist>0)
            {
                $totalm += 2;
            }
        }

    }

    $thismemberteam = $row_staff['team_id'];
    $apsql = "SELECT * FROM `project` WHERE team_id='$thismemberteam ' AND deleted='0' AND p_status='2'"; 
    $ap = mysqli_query($KPI,$apsql) or die(mysqli_error($KPI));
    $row_ap = mysqli_fetch_assoc($ap);
    $totalrow_ap = mysqli_num_rows($ap);

    if($totalrow_ap>0)
    {
       do
       {
           $mpcid=$row_ap['project_id'];
           $memmarksql = "SELECT SUM(mark) AS mark FROM `project_kpi` WHERE staff_id='$uid' AND project_id='$mpcid' AND status IS NOT NULL";
           $memmark = mysqli_query($KPI,$memmarksql) or die(mysqli_error($KPI));
           $row_memmark = mysqli_fetch_assoc($memmark);
           $totalrow_memmark = mysqli_num_rows($memmark);

           if($totalrow_memmark>0)
           {
               $totalmarkkpi = $totalmarkkpi+$row_memmark['mark'];
               
               $averagekpi=$averagekpi+($row_memmark['mark']/$totalm);
           }

       }
        while($row_ap = mysqli_fetch_assoc($ap));
    } 
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Member KPI | <?php echo $base_name; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- HEADER CONS SECTION -->
    <?php include "inc/header-cons.php"; ?>
    <!-- END OF HEADER CONS SECTION -->
    <!-- COLOR THEME SECTION -->
    <?php include "inc/color-dark-theme.php"; ?>
    <!-- END OF COLOR THEME SECTION -->
    <style type="text/css">
        /*BACKGROUND SECTION*/
        #bg-section {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        #bg-box {
            border-radius: 5px;
            padding: 25px 15px;
        }
        #bg-left-div {
            display: flex;
            align-items: center;
        }
        #bg-img-div {
            margin-right: 15px;
        }
        #bg-img-div img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
        }
        #bg-name {
            font-size: 24px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 3px;
            color: #1896BD;
        }
        #bg-info-p {
            display: flex;
            color: #888;
            align-items: center;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 3px;
        }
        #bg-info-p:last-child {
            margin-bottom: 0;
        }
        #bg-info-p i {
            margin-right: 3px;
        }
        #bg-since {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 0;
        }
        #bg-btn-div {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            justify-content: end;
        }
        #bg-btn-div a:last-child {
            margin-right: 0;
        }
        #bg-btn-div a {
            margin-right: 15px;
        }
        #bg-ft-div {
            width: 100%;
            align-items: center;
            display: inline-flex;
            justify-content: flex-end;
        }
        #bg-ft-box {
            display: flex;
            overflow: hidden;
            position: relative;
            align-items: center;
            padding: 0 0 15px 40px;
        }
        #bg-ft-title {
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 0;
            color: #000000;
        }
        #bg-ft-p {
            font-size: 21px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0;
            color: #1896BD;
        }
        #bg-ft-icon-div {
            margin-right: 10px;
            
        }
        #bg-ft-icon {
            color:#fff;
            width: 50px;
            height: 50px;
            font-size: 20px;
            line-height: 50px;
            text-align: center;
            border-radius: 50%;
            box-shadow: 0 5px 10px #4be8d340;
            background: linear-gradient(to bottom right, #4be8d4 0%, #129bd2 100%);
        }
        .profile-nav.nav-pills .nav-link {
            border-radius: unset;
            padding: 0 15px 15px 15px;
        }
        #att-div {
            overflow: auto;
            height: 150px;
        }
        #att-box {
            margin-bottom: 30px;
        }
        #att-box:last-child {
            margin-bottom: 0;
        }
        #att-name {
            font-size: 15px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0;
        }
        #att-time {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 5px;
        }
        #att-p {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 0;
        }
        .dropdown .dropdown-toggle {
            padding-right: 35px !important;
        }
        #form-div:last-child {
            margin-bottom: 0;
        }
        #tb-sub-til
        {
            background: #01216140 !important;
            color: #000;
            font-weight: 600;
            font-size: 13px;
        }
        @media (max-width: 991px){
            #bg-left-div {
                display: block;
                align-items: unset;
                text-align: center;
            }
            #bg-img-div {
                margin-right: 0;
                margin-bottom: 15px;
            }
            #bg-img-div img {
                width: 125px;
                height: 125px;
            }
            #bg-rank-icon {
                width: 45px;
            }
            #bg-name {
                font-size: 18px;
                margin-bottom: 2px;
            }
            #bg-position-div span {
                font-size: 11px;
            }
            #bg-since {
                font-size: 9px;
            }
            #bg-ft-div {
                width: unset;
                margin-top: 30px;
                align-items: unset;
                display: -webkit-box;
                justify-content: unset;
            }
            #bg-ft-box {
                display: block;
                padding: 0 15px;
                text-align: center;
            }
            #bg-ft-icon-div {
                margin-right: 0;
                margin-bottom: 15px;
            }
            #bg-ft-icon {
                margin: 0 auto;
            }
            #bg-ft-title {
                font-size: 11px;
                margin-bottom: 2px;
            }
            #bg-ft-p {
                font-size: 18px;
            }
            #bg-ul {
                justify-content: space-between;
            }
            #bg-btn-div {
                margin-bottom: 0;
                margin-top: 15px;
                justify-content: center;
            }
            #att-name {
                font-size: 13px;
            }
            #att-time {
                font-size: 9px;
            }
            #att-p {
                font-size: 10px;
            }
        }

        /*FORM SECTION*/
        .form-check .form-check-input {
            float: none;
        }
        .form-check {
            text-align: right;
        }
        .form-check-input {
            width: 45px !important;
            height: 20px;
            margin: 0;
        }
        @media (max-width: 991px){
            .form-check-input {
                width: 35px !important;
            }
        }
    </style>
    <!-- COUNTRY PHONE CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/intlTelInput.css">
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
                    <span id="bc-active">View Member KPI</span>
                </div>
            </section>
            <!-- END OF BREADCRUMB SECTION -->
            <div class="page-content">
                <!-- BACKGROUND SECTION -->
                <section id="bg-section">
                    <div class="container-fluid">
                        <div id="bg-box" class="general-box">
                            <div class="row align-items-center">
                                <div class="col-lg-5">
                                    <div id="bg-left-div">
                                        <div id="bg-img-div" class="profile-user position-relative">
                                            <img src="<?php echo $real_url; ?>/my_asset/img/<?php echo $row_staff['staffimg']?>" alt="Profile" title="Profile">
                                        </div>
                                        <div id="bg-info-div">
                                            <p id="bg-name"><?php echo $row_staff['staff_name'];?></p>
                                            <p id="bg-info-p"><?php echo $row_staff['position_name'];?></p>
                                            <p id="bg-info-p"><i class="ri-team-line"></i><?php echo $row_staff['team_name'];?></p>
                                            <p id="bg-info-p"><i class="ri-calendar-fill"></i><?php $rdate= date_create($row_staff['datejoincompany']); echo date_format($rdate,"d M Y");?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-7">
                                    <div id="bg-btn-div" class="dropdown">
                                        <a href="<?php echo $base_url_dashboard; ?>/member.php" id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
                                        <a href="<?php echo $base_url_dashboard; ?>/member-edit.php?uid=<?php echo $uid;?>" id="general-btn" class="btn1"><i class="ri-edit-2-line"></i>Edit Member</a>
                                    </div>
                                    <div id="bg-ft-div">
                                        <div id="bg-ft-box">
                                            <div id="bg-ft-icon-div">
                                                <div id="bg-ft-icon" class="bg-ft-icon-1">
                                                    <i class="ri-percent-line"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p id="bg-ft-title">Total Score</p>
                                                <p id="bg-ft-p"><?php echo ($averagekpi/$totalrow_ap)*100; ?> %</p>
                                            </div>
                                        </div>
                                        <div id="bg-ft-box">
                                            <div id="bg-ft-icon-div">
                                                <div id="bg-ft-icon" class="bg-ft-icon-3">
                                                    <i class="ri-bar-chart-line"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p id="bg-ft-title">Total KPI Point</p>
                                                <p id="bg-ft-p"><?php echo $totalmarkkpi;?>/<?php echo $totalm*$totalrow_ap; ?></p>
                                            </div>
                                        </div>
                                        <div id="bg-ft-box">
                                            <div id="bg-ft-icon-div">
                                                <div id="bg-ft-icon" class="bg-ft-icon-3">
                                                    <i class="ri-clipboard-line"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p id="bg-ft-title">Total Project</p>
                                                <p id="bg-ft-p"><?php echo $totalrow_ap; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- END OF BACKGROUND SECTION -->
                <!-- GENERAL SECTION -->
                <section id="general-section">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6">
                                <div id="form-box" class="general-box">
                                    <form name="profile_form" method="POST" action="">
                                        <div id="form-div">
                                            <p id="form-sub-title">Personal Information</p>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                   <div class="input-group">
                                                       <label>IC No</label>
                                                       <input type="text" class="form-control" id="ic" name="ic" maxlength="30" read onKeyPress="return validateHP(this, event);" value="<?php echo $row_staff['ic']; ?>" data-slots="_" readonly="readonly">
                                                   </div> 
                                                </div>
                                                
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>Gender</label>
                                                        <input type="text" class="form-control" id="gender" name="gender" maxlength="6"  value="<?php echo $row_staff['gender']; ?>" readonly="readonly">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>Birth Date</label>
                                                        <input type="date" class="form-control" id="dob" name="dob" readonly="readonly" value="<?php echo $row_staff['dob']; ?>">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <div class="input-group">
                                                            <label>Join Team Date</label>
                                                            <input type="date" class="form-control" id="jtdate" name="jtdate" readonly="readonly" value="<?php echo $row_staff['datejointeam']; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>Contact No.</label>
                                                        <input type="tel" class="form-control" id="phone" name="phone" maxlength="30" readonly="readonly" value="<?php echo $row_staff['contact']; ?>" onKeyPress="return validateHP(this, event);">
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>Email Address</label>
                                                        <input type="mail" class="form-control" id="email" name="email" maxlength="200" readonly="readonly" value="<?php echo $row_staff['email']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="form-div">
                                            <p id="form-sub-title">Address Information</p>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <label>Address Details</label>
                                                        <input type="text" class="form-control" id="add_details" name="detail" maxlength="250" readonly="readonly" value="<?php echo $row_staff['staff_address']; ?>" >
                                                    </div>
                                                </div>
                                            
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>Postcode</label>
                                                        <input type="text" class="form-control" id="postcode" name="pcode" maxlength="50" readonly="readonly" value="<?php echo $row_staff['postcode']; ?>" onKeyPress="return validateHP(this, event);">
                                                    </div>
                                                </div>
                                            
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>City</label>
                                                        <input type="text" class="form-control" id="city" name="city" maxlength="150" value="<?php echo $row_staff['city']; ?>" readonly="readonly">
                                                    </div>
                                                </div>
                                            
                                                <div class="col-lg-6">
                                                    <div class="input-group">
                                                        <label>State</label>
                                                        <input type="text" class="form-control" id="state" name="state" maxlength="30" value="<?php echo $row_staff['states']; ?>" readonly="readonly">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div id="form-box" class="general-box">
                                    <p id="form-sub-title">KPI Record</p>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div style="margin-bottom: 15px;">
                                                <form name="filter_form" method="POST" action="">
                                                    <div class="row align-items-center">
                                                       <div class="col-lg-6">
                                                            <div class="input-group">
                                                                <label>Project</label>
                                                                <select class="form-control js-example-basic-single" id="fproject" name="fproject" required>
                                                                    <option selected disabled>Select Project</option>
                                                                    <?php
                                                                    $mteamid = $row_staff['team_id'];
                                                                    mysqli_select_db($KPI,$database_KPI);
                                                                    $query_project = "SELECT * FROM `project` WHERE team_id ='$mteamid' AND deleted='0' AND p_status='2'";
                                                                    $fproject = mysqli_query($KPI,$query_project) or die(mysqli_error($KPI));
                                                                    $frow_project = mysqli_fetch_assoc($fproject);
                                                                    $ftotalrow_project = mysqli_num_rows($fproject);


                                                                    if($ftotalrow_project>0)
                                                                    {
                                                                        do
                                                                        {
                                                                    ?>
                                                                    <option value="<?php echo $frow_project['project_id']; ?>" <?php if(isset($_GET['pid'])){ $projid = mysqli_real_escape_string($KPI, $_GET['pid']); if($projid == $frow_project['project_id']){ echo "selected";}}?>><?php echo $frow_project['p_Title'];?></option>
                                                                    <?php
                                                                        }while($frow_project=mysqli_fetch_assoc($fproject));
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                       <div class="col-lg-6">
                                                            <div id="filter-btn-div">
                                                                <input type="hidden" name="filter" value="filter_form">
                                                                <button type="submit" id="general-btn" class="btn1"><i class="ri-filter-2-line"></i>Filter</button>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div id="form-div">
                                                <div id="table-div">
                                                    <table class="table table-bordered dt-responsive nowrap align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th>Objective</th>
                                                                <th class="text-center">Marks</th>
                                                                <th class="text-center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td colspan="3" id="tb-sub-til">
                                                                    <?php echo $row_staff['job_scope']; ?>
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            if($totalrow_pkpi0>0)
                                                            {

                                                                do{
                                                            ?>
                                                             <tr>
                                                                <td><?php echo $row_pkpi0['kojbInfo_title'];?></td>
                                                                <td class="text-center">
                                                                    <?php 

                                                                        echo $row_pkpi0['mark'];
                                                                    ?>
                                                                 </td>
                                                                <td class="text-center">
                                                                <?php
                                                                    if($row_pkpi0['createddate']==NULL && $row_pkpi0['status']==NULL)
                                                                    {
                                                                        echo "-";
                                                                    }
                                                                    else if($row_pkpi0['createddate']!=NULL && $row_pkpi0['status']==NULL)
                                                                    {
                                                                ?>
                                                                     <span class="tb-status" id="tb-status-3">Pending</span>
                                                                <?php
                                                                    }
                                                                    else if($row_pkpi0['createddate']!=NULL && $row_pkpi0['status']==1)
                                                                    {
                                                                ?>
                                                                    <span class="tb-status" id="tb-status-1">Approved</span>
                                                                <?php
                                                                    }
                                                                    else if($row_pkpi0['createddate']!=NULL && $row_pkpi0['status']==2)
                                                                    {
                                                                ?>
                                                                    <span class="tb-status" id="tb-status-2">Reject</span>
                                                                <?php
                                                                    }
                                                                ?>
                                                                </td>
                                                            </tr>

                                                            <?php
                                                                }while($row_pkpi0 = mysqli_fetch_assoc($pkpi0));
                                                            }
                                                            ?> 
                                                            <tr>
                                                                <td colspan="3" id="tb-sub-til">
                                                                    Extra Point
                                                                </td>
                                                            </tr>
                                                            <?php
                                                            if($totalrow_pkpi1>0)
                                                            {
                                                                do{
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $row_pkpi1['kojbInfo_title'];?></td>
                                                                <td class="text-center">
                                                                <?php

                                                                    echo $row_pkpi1['mark'];
                                                                ?>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php
                                                                    if($row_pkpi1['createddate']==NULL && $row_pkpi1['status']==NULL)
                                                                    {
                                                                        echo "-";
                                                                    }
                                                                    else if($row_pkpi1['createddate']!=NULL && $row_pkpi1['status']==NULL)
                                                                    {
                                                                ?>
                                                                     <span class="tb-status" id="tb-status-3">Pending</span>
                                                                <?php
                                                                    }
                                                                    else if($row_pkpi1['createddate']!=NULL && $row_pkpi1['status']==1)
                                                                    {
                                                                ?>
                                                                    <span class="tb-status" id="tb-status-1">Approved</span>
                                                                <?php
                                                                    }
                                                                    else if($row_pkpi1['createddate']!=NULL && $row_pkpi1['status']==2)
                                                                    {
                                                                ?>
                                                                    <span class="tb-status" id="tb-status-2">Reject</span>
                                                                <?php
                                                                    }
                                                                ?>
                                                                </td>
                                                            </tr>
                                                             <?php
                                                                }while($row_pkpi1 = mysqli_fetch_assoc($pkpi1));
                                                            }
                                                            ?> 
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div id="form-div">
                                                <div id="table-div">
                                                    <table class="table table-bordered dt-responsive nowrap align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center">Total</th>
                                                                <th class="text-center">Self-Add</th>
                                                                <th class="text-center">KPI Score</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="text-center">
                                                                    <?php
                                                                    $cmkpisql = "SELECT SUM(mark) AS mark FROM `project_kpi` WHERE staff_id='$uid' AND project_id='$fprojectid' AND status IS NOT NULL";
                                                                    $cmkpi = mysqli_query($KPI,$cmkpisql) or die(mysqli_error($KPI));
                                                                    $row_cmkpi = mysqli_fetch_assoc($cmkpi);
                                                                    $totalrow_cmkpi = mysqli_num_rows($cmkpi);

                                                                    if($totalrow_cmkpi>0)
                                                                    {
                                                                        $markkpi = $row_cmkpi['mark'];
                                                                    }
                                                                    ?>

                                                                    <?php echo  $markkpi; ?>/<?php echo $totalm; ?>
                                                                </td>
                                                                <td class="text-center"><?php echo  $markkpi; ?></td>
                                                                <td class="text-center">
                                                                    <?php echo $markkpi/$totalm * 100;?> %
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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