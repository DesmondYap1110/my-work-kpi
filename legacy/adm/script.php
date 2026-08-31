<?php 

    require_once('../cons/KPIDB.php');
	
	if (!isset($_SESSION)) 
    {
	  session_start();
	}
	$base_url = "https://memberportal.winnefy.xyz/adm";
	$base_url_dashboard = "https://memberportal.winnefy.xyz/adm/dashboard";
	$base_name = "Winnefy";

?>