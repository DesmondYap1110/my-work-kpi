<?php
    require_once('script.php');
    $pg = "member";
    
    if(isset($_GET['uid']) && $_GET['uid']!=NULL)
    {
        $uid = $_GET['uid'];
        mysqli_select_db($KPI,$database_KPI);
        $staffq="SELECT c.team_name, b.position_name, a.staff_name, a.staffimg, a.gender, a.ic, a.dob , a.contact , a.email, a.staff_address, a.postcode, a.city, a.states, a.datejointeam , a.datejoincompany , a.staffstatus , a.position_id , a.team_id FROM `staff` a LEFT JOIN `staff_position` b ON a.position_id = b.position_ID LEFT JOIN `team` c ON a.team_id = c.team_id WHERE a.staff_id='$uid'";
        $staff= mysqli_query($KPI,$staffq) or die(mysqli_error($KPI));
        $row_staff = mysqli_fetch_assoc($staff);
    }

//Edit Member
    if(isset($_POST['edit']) && $_POST['edit']=="edit_form")
    {
        $createddate=date("Y-m-d H:i:s");
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
        $staffstatus = mysqli_real_escape_string($KPI, $_POST['df'])?1:0;
        $position_id = mysqli_real_escape_string($KPI, $_POST['position']);
        $team_id = mysqli_real_escape_string($KPI, $_POST['team']);
        
        
        //---------------------UPLOAD IMAGE-------------------------------------------//
        $target = "../../my_asset/img/";
        $rname = date("dmYHis");
        $temp1 = explode(".",$_FILES['upload']['name']);
        $pimg1_1 = $rname.'.'.end($temp1); //Rename Image File Name
        $pimg1_2 = $target.$pimg1_1;  //Link
        
   
        $type_image1 = strtolower(pathinfo($pimg1_1, PATHINFO_EXTENSION));
        $upload = 1;
     
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

                    mysqli_select_db($KPI,$database_KPI);
                    $editstaffSQL = "UPDATE `staff` SET staff_name = '$name' , staffimg = '$pimg1_1', gender = '$gender' , ic = '$ic' , dob = '$dob', contact = '$contact' , email = '$email' , staff_address ='$staff_address' , postcode='$postcode' , city='$city' , states='$state' , datejointeam ='$datejointeam' , datejoincompany = '$datejoincompany' , staffstatus ='$staffstatus' , position_id ='$position_id' , team_id='$team_id' , createddate='$createddate' WHERE staff_id='$uid'";
                    $editstaffQuery = mysqli_query($KPI,$editstaffSQL) or die(mysqli_error($KPI));

                    $togo = "member.php?edit=succ";
                    header(sprintf("Location: %s",$togo));

                }
                else
                {
                    $togo = $_SERVER["PHP_SELF"]."?uid=$uid&upload=fail";
                    header(sprintf("Location: %s",$togo));
                }
            }
            else
            {
                $togo = $_SERVER["PHP_SELF"]."?uid=$uid&format=fail";
                header(sprintf("Location: %s",$togo));

            }


        }
        else
        {
           
            mysqli_select_db($KPI,$database_KPI);
            $editstaffSQL = "UPDATE `staff` SET staff_name = '$name' , gender = '$gender' , ic = '$ic' , dob = '$dob', contact = '$contact' , email = '$email' , staff_address ='$staff_address' , postcode='$postcode' , city='$city' , states='$state' , datejointeam ='$datejointeam' , datejoincompany = '$datejoincompany' , staffstatus ='$staffstatus' , position_id ='$position_id' , team_id='$team_id' , createddate='$createddate' WHERE staff_id='$uid'";
            $editstaffQuery = mysqli_query($KPI,$editstaffSQL) or die(mysqli_error($KPI));

            $togo = "member.php?edit=succ";
            header(sprintf("Location: %s",$togo));

        }
        
        
        //---------------------END OF UPLOAD IMAGE-------------------------------------------//
        
    }
   


?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-layout-mode="dark">
<head>
    <meta charset="utf-8">
    <title>Edit Member | <?php echo $base_name; ?></title>
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
                    <span id="bc-active">Edit Member</span>
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
                                    <form name="edit_form" method="POST" action="" enctype="multipart/form-data">
                                        <p id="form-sub-title">Edit Member</p>
                                        <?php if (isset($_GET['format'])) { $check = $_GET['format']; if ($check == 'fail') { ?>
                                            <div class="alert alert-danger" role="alert" style="margin-bottom: 15px;">
                                                <p class="alert-heading" style="line-height: 0.8; font-size: 13px;">Only PNG, JPG or JPEG with size of 10 MB are allow to upload!</p>
                                            </div>
                                        <?php } } ?>
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="profile-img-div" class="profile-user">
                                                    <img src="<?php echo $real_url; ?>/my_asset/img/<?php echo $row_staff['staffimg'];?>" alt="Profile" title="Profile"class="user-profile-image">
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
                                            <div class="col-lg-12">
                                                <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                                                    <?php
                                                    if($row_staff['staffstatus']==1)
                                                    {
                                                    ?>
                                                    <input class="form-check-input" type="checkbox" role="switch" id="defaultbox" name="df" checked>
                                                    <?php
                                                    }
                                                    else
                                                    {
                                                    ?>
                                                    <input class="form-check-input" type="checkbox" role="switch" id="defaultbox" name="df">
                                                    <?php
                                                    }
                                                    ?>
                                                    <label class="form-check-label" for="defaultbox">Status</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Name<span>*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name" maxlength="200" value="<?php echo $row_staff['staff_name'];?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>IC No<span>*</span></label>
                                                    <input type="text" class="form-control" id="ic" name="ic" maxlength="30" required onKeyPress="return validateHP(this, event);" placeholder="______-__-____" data-slots="_" value="<?php echo $row_staff['ic'];?>">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Gender<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="gender" name="gender" required>
                                                        <option disabled value="">Select Gender</option>
                                                        <option value="Male" <?php if($row_staff['gender'] == "Male"){ echo "selected";}?>>MALE</option>
                                                        <option value="Female" <?php if($row_staff['gender'] == "Female"){ echo "selected";}?>>FEMALE</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Birth Date<span>*</span></label>
                                                    <input type="date" class="form-control" id="dob" name="dob" value="<?php echo $row_staff['dob'];?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Contact No.<span>*</span></label>
                                                    <input type="tel" class="form-control" id="phone" name="phone" maxlength="30" required onKeyPress="return validateHP(this, event);" value="<?php echo $row_staff['contact'];?>">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Email Address<span>*</span></label>
                                                    <input type="mail" class="form-control" id="email" name="email" maxlength="200" value="<?php echo $row_staff['email'];?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Position<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="position" name="position" required>
                                                        <option disabled value="">Select Position</option>
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
                                                       <option value="<?php echo $pid; ?>"<?php if($row_staff['position_id'] ==  $pid ){ echo "selected";}?>><?php echo $positionDisplay; ?></option>
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
                                                        <input type="date" class="form-control" id="jcdate" name="jcdate" value="<?php echo $row_staff['datejoincompany'];?>" required>
                                                    </div>
                                               </div>
                                            </div> 
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Team<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="team" name="team" required>
                                                        <option disabled value="">Select Team</option>
                                                        <?php 
                                                            if($totalrow_position>0)
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
                                                        <option value="<?php echo $tid;?>" <?php if($row_staff['team_id'] ==  $tid ){ echo "selected";}?>><?php echo $teamDisplay;?></option>
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
                                                        <input type="date" class="form-control" id="jtdate" name="jtdate" value="<?php echo $row_staff['datejointeam'];?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <label>Address Details<span>*</span></label>
                                                    <input type="text" class="form-control" id="Adetails" name="Adetails" maxlength="250" value="<?php echo $row_staff['staff_address'];?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>Postcode<span>*</span></label>
                                                    <input type="text" class="form-control" id="pcode" name="pcode" maxlength="50" required onKeyPress="return validateHP(this, event);" value="<?php echo $row_staff['postcode'];?>"">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>City<span>*</span></label>
                                                    <input type="text" class="form-control" id="city" name="city" maxlength="150" value="<?php echo $row_staff['city'];?>" required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="input-group">
                                                    <label>States<span>*</span></label>
                                                    <select class="form-control js-example-basic-single" id="states" name="states" required>
                                                        <option disabled value="">Select States</option>
                                                        <option value="Johor" <?php if($row_staff['states'] =="Johor"){ echo "selected";}?>>Johor</option>
                                                        <option value="Kedah" <?php if($row_staff['states'] =="Kedah"){ echo "selected";}?>>Kedah</option>
                                                        <option value="Kelantan" <?php if($row_staff['states'] =="Kelantan"){ echo "selected";}?>>Kelantan</option>
                                                        <option value="Malacca" <?php if($row_staff['states'] =="Malacca"){ echo "selected";}?>>Malacca</option>
                                                        <option value="Negeri Sembilan" <?php if($row_staff['states'] =="Negeri Sembilan"){ echo "selected";}?>>Negeri Sembilan</option>
                                                        <option value="Pahang" <?php if($row_staff['states'] =="Pahang"){ echo "selected";}?>>Pahang</option>
                                                        <option value="Penang" <?php if($row_staff['states'] =="Penang"){ echo "selected";}?>>Penang</option>
                                                        <option value="Perak" <?php if($row_staff['states'] =="Perak"){ echo "selected";}?>>Perak</option>
                                                        <option value="Perlis" <?php if($row_staff['states'] =="Perlis"){ echo "selected";}?>>Perlis</option>
                                                        <option value="Sabah" <?php if($row_staff['states'] =="Sabah"){ echo "selected";}?>>Sabah</option>
                                                        <option value="Sarawak" <?php if($row_staff['states'] =="Sarawak"){ echo "selected";}?>>Sarawak</option>
                                                        <option value="Selangor" <?php if($row_staff['states'] =="Selangor"){ echo "selected";}?>>Selangor</option>
                                                        <option value="Terengganu" <?php if($row_staff['states'] =="Terengganu"){ echo "selected";}?>>Terengganu</option>
                                                        <option value="Kuala Lumpur" <?php if($row_staff['states'] =="Kuala Lumpur"){ echo "selected";}?>>Kuala Lumpur</option>
                                                        <option value="Labuan" <?php if($row_staff['states'] =="Labuan"){ echo "selected";}?>>Labuan</option>
                                                        <option value="Putrajaya" <?php if($row_staff['states'] =="Putrajaya"){ echo "selected";}?>>Putrajaya</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>                             
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div id="form-btn-div">
                                                    <a href="<?php echo $base_url_dashboard; ?>/member.php?uid=<?php echo $uid?>" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                                                    <input type="hidden" name="edit" value="edit_form">
                                                    <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-fill"></i>Save Changes</button>
                                                </div>
                                           </div>
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
    
    <!-- PROFILE PICTURES JS -->
    <script src="<?php echo $base_url; ?>/assets/js/pages/profile-setting.init.js"></script>

    <!-- FOOTER CONS SECTION -->
    <?php include "inc/footer-cons.php"; ?>
    <!-- END OF FOOTER CONS SECTION -->

</body>
</html>