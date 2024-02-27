<?php
$connect = mysqli_connect("localhost","web3_hedgevip","web3_hedgevip@453ds#24$","wallet");

require('../includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://127.0.0.1:8545/');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if($_POST['secret']!='fcc7105c52fd3fce442d5e51176fb956b33e5f7dfcc7105c5fd3fce442d5e51176f')
	{
		$returnArr = [];
		$returnArr['success'] = false;
		$returnArr['message'] = "Secret key not matched";
		$returnArr['data'] = '';
		echo json_encode($returnArr); 
		$selectQry = "INSERT into apirequests SET apiname = 'get_balance', returndata = '".$jsonData."'";
		$runQry = mysqli_query($connect,$selectQry);	
		die;
	}




	$eth = $web3->eth;
	$walletAddress = $_POST['address'];
	$etherTokenBalance = '';
	/**
	 * getBalance
	 * get Ether Balance
	 *
	 * @var string
	 */
	 $eth->getBalance($walletAddress, function ($err, $balance) use(&$etherTokenBalance) {
				/* if ($err !== null) {
					echo 'Error: ' . $err->getMessage();
					return;
				} */
			  $etherTokenBalance = $balance->toString();
			});


	/**
	 * testAbi
	 * GameToken abi from https://github.com/sc0Vu/GameToken
	 *
	 * @var string
	 */
	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"initialSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_value","type":"uint256"}],"name":"burn","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_subtractedValue","type":"uint256"}],"name":"decreaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_addedValue","type":"uint256"}],"name":"increaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"burner","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Burn","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]';


	$contractAddress = '0x823DCD364a0B3F2519751214ca1BcAA093826b90'; 
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$hedTokenBalance = '';

	$functionData = $contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result)use(&$hedTokenBalance){
		$hedTokenBalance = $result['balance']->toString();
	});
	if($hedTokenBalance!='' && $hedTokenBalance!=0) {
		$hedTokenBalance = $hedTokenBalance/1000000000000000000;
	}
	if($etherTokenBalance!='' && $etherTokenBalance!=0) {
		$etherTokenBalance = $etherTokenBalance/1000000000000000000;
	}
	
	
	//$balanceArr = ['eth'=>$etherTokenBalance,'intaro'=>$hedTokenBalance];
	$balanceArr = ['intaro'=>$hedTokenBalance];
	$returnArr = [];
	$returnArr['success'] = true;
	$returnArr['message'] = "get balance";
	$returnArr['data'] = $balanceArr;


}
else {
	$returnArr = [];
	$returnArr['success'] = false;
	$returnArr['message'] = "Invalid Request";
	$returnArr['data'] = [];
	
}




$jsonData = json_encode($returnArr);

$selectQry = "INSERT into apirequests SET apiname = 'get_balance', returndata = '".$jsonData."'";

$runQry = mysqli_query($connect,$selectQry);										  

echo $jsonData; die;