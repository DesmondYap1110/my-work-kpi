<?php
function managestatus($ID,$issetID,$status)
{
	if($status==1)
	{
			return 
			'
			<a href="status.php?'.$issetID.'='.$ID.'&status=0" id="general-btn" class="btn ripple btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Click to Block">Active</a>
			';
	}
	elseif($status==0)
	{
			return
			'
			<a href="status.php?'.$issetID.'='.$ID.'&status=1" id="general-btn" class="btn ripple btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Click to Active">Block</a>
			';
	}
}

function set_status($get_ID,$tbname,$togo)
{
	global $KPI;

	if (isset($_GET[$get_ID])) 
	{
		$ID = mysqli_real_escape_string($KPI, $_GET[$get_ID]);
		$status = mysqli_real_escape_string($KPI, $_GET['status']);
		if($status == 0 || $status == 1)
		{
			$updateSQL = "UPDATE `$tbname` SET status='$status' WHERE ID='$ID'";

			mysqli_query($KPI, $updateSQL) or die(mysqli_error($KPI));
			header(sprintf("Location: %s", $togo));
		} 
		else 
		{
			$insertGoTo = $togo."?process=invalid";
			header(sprintf("Location: %s", $insertGoTo));
		}	
	}
}

function ischecked($status)
{
	$display="";
	if($status==1)
	{
		$display="checked";
	}
	return $display;

}


function deleteInfo($GET_ID,$tbname,$colname,$togo)
{
	global $KPI;
	
	if ((isset($_GET[$GET_ID])) && ($_GET[$GET_ID] != "")) 
	{
		$ID = mysqli_real_escape_string($KPI, $_GET[$GET_ID]);

		$sql = "SELECT * FROM `$tbname` WHERE $colname='$ID'";
		$query = mysqli_query($KPI, $sql) or die(mysqli_error($KPI));
		$totalRows = mysqli_num_rows($query);
		
		if ($totalRows > 0){
			$deleteSQL = "DELETE FROM `$tbname` WHERE $colname='$ID'";
			mysqli_query($KPI, $deleteSQL) or die(mysqli_error($KPI));
			
			$updateGoTo = "$togo?delete=success";
			header(sprintf("Location: %s", $updateGoTo));
		} else {
			$page_not_found = "$togo?process=invalid";
			header(sprintf("Location: %s", $page_not_found));
		}
	}

}


?>