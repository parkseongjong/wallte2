<?php
require_once './config/config.php';

require_once(__DIR__ . '/messente_api/vendor/autoload.php');


session_start();
if (!isset($_SESSION['user_logged_in'])) {
	die("you are at wrong place");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
	
	$userId = $_SESSION['user_id'];
	
	$db = getDbInstance();
	$db->where("id", $userId);
	$row = $db->getOne('admin_accounts');
	$getPvtKey = $row['pvt_key'];
	if(empty($getPvtKey)){
		$userWalletAddress = $row['wallet_address'];
		$userWalletPass = $row['email']."ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_PORT => "3000",
		 CURLOPT_URL => "http://127.0.0.1:3000/getpvtkey",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\n\t\"address\":\"".$userWalletAddress."\",\n\t\"password\":\"".$userWalletPass."\"\t\n}",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/json",
			"postman-token: eb0783a3-f404-9d7c-b9ba-32ebeefe2c65"
		  ),
		));

		$response = curl_exec($curl);
		$decodeResp = json_decode($response,true);
		if(!empty($decodeResp)){
			$getPvtKey = $decodeResp['pvtKey'];
			
			$db = getDbInstance();
			$db->where("id", $_SESSION['user_id']);
			$last_id = $db->update('admin_accounts', ['pvt_key'=>$getPvtKey]);
		}
		//$err = curl_error($curl);
	}
	echo $getPvtKey; die;
	
	
	
}
?>