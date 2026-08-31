<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
date_default_timezone_set('Asia/Kuala_Lumpur');
$hostname_KPI = "localhost";
$database_KPI = "u659082448_kpiproject";
$username_KPI = "u659082448_kpiproject";
$password_KPI = "Phplayer@3029288";

$KPI = mysqli_connect($hostname_KPI, $username_KPI, $password_KPI, $database_KPI);
mysqli_select_db($KPI,$database_KPI);

mysqli_set_charset($KPI,"utf8");
?>