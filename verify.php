<?php
session_start();
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;

if ($_SERVER['REQUEST_METHOD'] == 'GET') 
{
	if(!isset($_GET['vcode'])){
		
    	header('location: login.php');
    	exit();
	}
	
	if(empty($_GET['vcode'])){
		
    	header('location: login.php');
    	exit();
	}
	
	$vCode = $_GET['vcode'];
	if(empty($vCode)){
		
    	header('location: login.php');
    	exit();
	}
	
$data_to_store = filter_input_array(INPUT_POST);
	
	//Get DB instance. function is defined in config.php
    $db = getDbInstance();
    $db->where("vcode", $vCode);
    $row = $db->get('admin_accounts');
    //print_r($row); die;

    if ($db->count == 0) {
		header('location: login.php');
    	exit();
	}
	$emailVerify = $row[0]['email_verify'];
	$email = $row[0]['email'];
	if($emailVerify=='Y') {
		header('location: login.php');
    	exit();
	}
	
	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$personal = $web3->personal;
	
	$personal->newAccount($email.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM', function ($err, $account) use (&$newAccount) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			
		}
		else {
			$newAccount = $account;
		}
	});
	
	
	$db = getDbInstance();
	$db->where("vcode", $vCode);
	$last_id = $db->update('admin_accounts', ['email_verify'=>"Y",'wallet_address'=>$newAccount]);	
		
	$_SESSION['success'] = "Email Verify Successfully. Now You can login!";
	header('location: login.php');
	exit();
	 


	
}

?>