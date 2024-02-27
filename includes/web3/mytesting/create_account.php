<?php

require('./config.php');

$personal = $web3->personal;
$newAccount = '';
$getPassword = trim($_POST['password']);
// create account
$personal->newAccount($getPassword, function ($err, $account) use (&$newAccount) {
	/*  if ($err !== null) {
	    echo 'Error: ' . $err->getMessage();
		return 'fail';
	}  */
	$newAccount = $account;  
	 
});
echo $newAccount;
?>