<?php 
exit();
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
 
require_once './config/config.php';

$walletAddress = '0xcea66e2f92e8511765bc1e2a247c352a7c84e895';



//	$web3 = new Web3('http://127.0.0.1:8545/');

//	$web3 = new Web3('https://mainnet.infura.io/v3/e9bbe05f9dc949838c685503e32c4334');


$timeout = 30; // set this time accordingly by default it is 1 sec
$web3 = new Web3('http://127.0.0.1:8545/', $timeout);


	$eth = $web3->eth;

	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		
		if ($err !== null) {
			echo 'Error: eth ' . $err->getMessage();
//			fn_logSave( 'Error: eth ' . $err->getMessage() );
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});

	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){

		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage();
//			fn_logSave( 'Error: contract ' . $err->getMessage() );
			return;
		}
		if ( !empty( $result ) ) {
			$coinBalance = reset($result)->toString();
		}
		else {
//			fn_logSave('reset error');
		}
	});

    $functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($tokenPayContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance1){

		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage();
//			fn_logSave( 'Error: contract ' . $err->getMessage() );
			return;
		}
		if ( !empty( $result ) ) {
			$coinBalance1 = reset($result)->toString();
		}
		else {
//			fn_logSave('reset error');
		}
	});

	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($usdtContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance2){

		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage();
//			fn_logSave( 'Error: contract ' . $err->getMessage() );
			return;
		}
		if ( !empty( $result ) ) {
			$coinBalance2 = reset($result)->toString();
		}
		else {
//			fn_logSave('reset error');
		}
	});
/*
$tokenPay = $erc20->token($tokenPayContractAddress);
$usdtObj = $erc20->token($usdtContractAddress);
*/
	


$getVal = $getBalance/1000000000000000000;
$coinBalance = $coinBalance/1000000000000000000;
$coinBalance1 = $coinBalance1/1000000000000000000;
$coinBalance2 = $coinBalance2/1000000;



echo number_format($getVal,8);;
echo "<br>";

echo $coinBalance;
echo "<br>";

echo $coinBalance1;
echo "<br>";

echo $coinBalance2;


echo "<br>";

echo "현재 날짜 : ". date("Y-m-d")."<br/>";
echo "현재 시간 : ". date("H:i:s")."<br/>";
echo "현재 일시 : ". date("Y-m-d H:i:s")."<br/>";

?>