<?php
function upload_img($fileID,$tbname,$dbconn,$colname,$condition,$failurl)
{
	//Profile Picture START
	$target = "../my_asset/";
	$temp1 = explode(".",$_FILES[$fileID]['name']);
	$pimg1_1 = $fileID."_".time().'.'.end($temp1);
	$pimg1_2 = $target.$pimg1_1;

	if($_FILES[$fileID]['size'] > 0)
	{
		$upload = 1;
		$valid_extensions = array('jpeg', 'jpg', 'png');
		$file_name_filepond_pp = $_FILES[$fileID]['name'];
		$file_size_filepond_pp = $_FILES[$fileID]['size'];
		$file_tmp_filepond_pp = $_FILES[$fileID]['tmp_name'];
		$file_ext_filepond_pp = strtolower(pathinfo($file_name_filepond_pp, PATHINFO_EXTENSION));

		// Check File Type
		if(!in_array($file_ext_filepond_pp, $valid_extensions)){
				$upload = 0;
		}

		$allowed_types = array('image/jpeg', 'image/jpg', 'image/png');
		if (!in_array($_FILES[$fileID]['type'], $allowed_types)) {
				$upload = 0;
		}

		// Check File Size
		if($file_size_filepond_pp > 5000000){
				$upload = 0;
		}
	}

	if($upload == 1)
	{
			if(move_uploaded_file($file_tmp_filepond_pp, $pimg1_2))
			{
					chmod($pimg1_2, 0644);

					$myimg = new crud($tbname,$dbconn);

					$info = array
					(
						$colname => $pimg1_1
					);
					$myimg->updateonlyc($info,$condition);
			}

	}
	else
	{
		if($_FILES[$fileID]['size'] > 0)
		{
			echo '<script>alert("Invalid file size or type. Maximum File Upload Size: 5MB")</script>';
			echo '<script>window.location.href="'.$failurl.'";</script>';
			exit;
		}
	}


}





?>