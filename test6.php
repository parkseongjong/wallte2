<?php 
exit();
require_once './config/config.php';
require_once '/var/www/html/wallet2/vendor/autoload.php';

use EthereumRPC\EthereumRPC;
use ERC20\ERC20;


//$geth = new EthereumRPC('ropsten-rpc.linkpool.io', 8545);
//$geth = new EthereumRPC('192.168.79.1', 8545);

$geth = new EthereumRPC('195.201.168.34', 8545);


$erc20 = new \ERC20\ERC20($geth);



/*
//local
//$contract = "0xcf453db5d3b0b3cbdf45e2475077bae9c1843f13"; // ERC20 contract address

//ropsten
$contract = "0x1c81f542521a5dc5910591b8b9ac95f33bd303a6"; // ERC20 contract address

$token = $erc20->token($contract);


var_dump($token->symbol());



var_dump($token->name());
var_dump($token->decimals());

var_dump($token->balanceOf("0x6e56b9cec441c0c2f6d10b114b60bfcf3a364457")); // string(26) "1216709.519608225542850811"

echo $geth->eth()->getBalance("0x6e56B9ceC441C0C2F6D10b114b60bFcF3a364457");
exit;

*/

$getVal = '';
$coinBalance = '';

$walletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';

	try {
		$getBalance= $geth->eth()->getBalance($walletAddress);
		$getVal = $getBalance;


		$ethObj = $erc20->token($contractAddress);
		$coinBalance = $ethObj->balanceOf($walletAddress,false);

		$scale = 18;
		$coinBalance = bcdiv($coinBalance, bcpow("10", strval($scale), 0), $scale);
		


	}
	catch(Exception $e) {
		
//		fn_logSave( 'Error: ' . $e->getMessage() );

echo "error";
		error_reporting(0);
		return;
		exit;

		//echo 'Message: ' .$e->getMessage();

	}




$tokenPay = $erc20->token($tokenPayContractAddress);
$tokenPayBalance = $tokenPay->balanceOf($walletAddress,false);
$scale = 18;
$tokenPayBalance = bcdiv($tokenPayBalance, bcpow("10", strval($scale), 0), $scale);



$usdtObj = $erc20->token($usdtContractAddress);
$usdtBalance = $usdtObj->balanceOf($walletAddress,false);

$scale = 6;
$usdtBalance = bcdiv($usdtBalance, bcpow("10", strval($scale), 0), $scale);


// mc balance
$mcObj = $erc20->token($marketCoinContractAddress);
$mcBalance = $mcObj->balanceOf($walletAddress,false);

$scale = 6;
$mcBalance = bcdiv($mcBalance, bcpow("10", strval($scale), 0), $scale);

// krw balance
$krwObj = $erc20->token($koreanWonContractAddress);
$krwBalance = $krwObj->balanceOf($walletAddress,false);

$scale = 6;
$krwBalance = bcdiv($krwBalance, bcpow("10", strval($scale), 0), $scale);





echo $getVal;
echo "<br>";

echo $coinBalance;
echo "<br>";


echo $tokenPayBalance;
echo "<br>";


echo $usdtBalance;
echo "<br>";

echo $mcBalance;
echo "<br>";

echo $krwBalance;
echo "<br>";


?>