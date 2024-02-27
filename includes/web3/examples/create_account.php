<?php

require('./config.php');
if($_SERVER['REQUEST_METHOD'] === 'POST') {
	$personal = $web3->personal;
	$newAccount = '';
	$getPassword = 'E54R2507c16VipAjaImmuAM';
	// create account
	$personal->newAccount($getPassword, function ($err, $account) use (&$newAccount) {
		/*  if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return 'fail';
		}  */
		$newAccount = $account;  
		 
	});
	$balanceArr = ['address'=>$newAccount];
	$returnArr = [];
	$returnArr['success'] = true;
	$returnArr['message'] = "account created successfully";
	$returnArr['data'] = $balanceArr;
}
else {
	$returnArr = [];
	$returnArr['success'] = false;
	$returnArr['message'] = "Invalid Request";
	$returnArr['data'] = [];
	
}
echo json_encode($returnArr);
?>