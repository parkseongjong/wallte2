<?php
$connect = mysqli_connect("localhost","web3_hedgevip","web3_hedgevip@453ds#24$","wallet");

require('../includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://127.0.0.1:8545/');



if($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	if($_POST['secret']=='fcc7105c52fd3fce442d5e51176fb956b33e5f7dfcc7105c5fd3fce442d5e51176f')
	{
		$personal = $web3->personal;
		$newAccount = '';
		$getPassword = $_POST['email'].'ZUMBAE54R2507c16VipAjaImmuAM';
		
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
		$returnArr['client_ip'] = $_SERVER['REMOTE_ADDR'];
		$returnArr['success'] = true;
		$returnArr['message'] = "account created successfully";
		$returnArr['data'] = $balanceArr;
	
	}
	else 
	{
		$returnArr = [];
		$returnArr['client_ip'] = $_SERVER['REMOTE_ADDR'];
		$returnArr['success'] = false;
		$returnArr['message'] = "Invalid Request";
		$returnArr['data'] = [];
	}
	
}
$jsonData = json_encode($returnArr);
$selectQry = "INSERT into apirequests SET apiname = 'create_account', returndata = '".$jsonData."'";
$runQry = mysqli_query($connect,$selectQry);										  
echo $jsonData; die;
?>