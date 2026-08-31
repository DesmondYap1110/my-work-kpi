<?php
require_once('script.php');

if(isset($_POST['insert']) && ($_POST['insert'] == "insert_form"))
{
     $name = mysqli_real_escape_string($KPI, $_POST['name']);
     $password = mysqli_real_escape_string($KPI, $_POST['password']);

     $hash = password_hash($password, PASSWORD_DEFAULT);

     $insert = "INSERT INTO `staff` (email, password) VALUES ('$name', '$hash')";
     mysqli_select_db($KPI, $database_KPI);
     $result = mysqli_query($KPI, $insert) or die(mysqli_error($KPI));

     if($result)
     {
          $togo = "https://kpiproject.winnefy.site/adm/insert.php?insert=succ";
          header(sprintf("Location: %s",$togo));
     }
     else
     {
          $togo = "https://kpiproject.winnefy.site/adm/insert.php?insert=fail";
          header(sprintf("Location: %s",$togo));
     }
}
?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>

<form action="" method="POST" name="insert_form">
     <input type="text" name="name">
     <input type="text" name="password">

     <input type="hidden" name="insert" value="insert_form">
     <button type="submit">Submit</button>
</form>
</body>
</html>
