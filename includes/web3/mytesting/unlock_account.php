<?php 
require('./config.php');
use Web3\Web3;
use Web3\Contract;

$personal = $web3->personal;
$password  = $_POST['password'];
$address  = $_POST['address'];

$personal->unlockAccount($address, $password, function ($err, $unlocked) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
	if ($unlocked) {
        echo 'New account is unlocked!' . PHP_EOL;
	} else {
	    echo 'New account isn\'t unlocked' . PHP_EOL;
	} 
});



?>