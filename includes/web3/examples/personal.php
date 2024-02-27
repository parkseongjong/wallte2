<?php


require('../vendor/autoload.php');

use Web3\Web3;

//$web3 = new Web3('http://192.168.99.100:8545/');
$web3 = new Web3('http://127.0.0.1:8545/');
//$web3 = new Web3('http://localhost:8545/');

$personal = $web3->personal;
$newAccount = '';

echo 'Personal Create Account and Unlock Account' . PHP_EOL;

// create account
$personal->newAccount('mighty_admin@gmail.com', function ($err, $account) use (&$newAccount) {
	if ($err !== null) {
	    echo 'Error: ' . $err->getMessage();
		return;
	}
	$newAccount = $account;
	echo 'New account: ' . $account . PHP_EOL;
});
/* 
$personal->unlockAccount($newAccount, '123456', function ($err, $unlocked) {
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


// get balance
$web3->eth->getBalance($newAccount, function ($err, $balance) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
	echo 'Balance: ' . $balance->toString() . PHP_EOL;
});
 */