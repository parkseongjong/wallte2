<?php
require_once './config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{

	$countrycode = str_replace(" ","",$_POST['countrycode']);
	$phone =  str_replace("-","",$_POST['phone']);
	$phone =  str_replace(" ","",$phone);

	$firstnumber = substr($phone, 0, 1);

	if($firstnumber == '0')
	{
		$phone =  substr($phone, 1);
	}

	$checkphone =  "+";
	$checkphone .=  $countrycode;
	$checkphone .=  $phone;

	if(!empty($checkphone)) {
		$db = getDbInstance();
		$db->where("phone", $checkphone);
		$row = $db->get('admin_accounts');
		 //print_r($row); die;

		if ($db->count > 0) {
			echo "true";

			exit();
		}
		else
		{
			echo "false";
			exit();

		}
	}
}


?>