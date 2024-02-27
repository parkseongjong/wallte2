<?php
/*
$returnArr = [];
$returnArr['success'] = false;
$returnArr['message'] = "transactions has failed";
$returnArr['data'] = ['transaction_id'=>"testing"];

echo $jsonData = json_encode($returnArr); die;
*/

$connect = mysqli_connect("localhost","web3_hedgevip","web3_hedgevip@453ds#24$","wallet");
require('../includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://127.0.0.1:8545/');
// from 0x2d5e51176fb956b33e5f7dfcc7105c52fd3fce44

if($_POST['secret']!='fcc7105c52fd3fce442d5e51176fb956b33e5f7dfcc7105c5fd3fce442d5e51176f')
{
	$returnArr = [];
	$returnArr['success'] = false;
	$returnArr['message'] = "Secret key not matched";
	$returnArr['data'] = '';
	echo json_encode($returnArr); 
	die;
}

$personal = $web3->personal;
$password  = $_POST['email'].'ZUMBAE54R2507c16VipAjaImmuAM';
$fromWalletAddress  = "0xf7c6ecbbbac3fe7ec61e09d53b92dda060cd90fb";
$toWalletAddress  = $_POST['toWalletAddress'];
$coinAmount  = $_POST['coinAmount'];

if(empty($toWalletAddress) || empty($coinAmount)){
	$returnArr = [];
	$returnArr['success'] = false;
	$returnArr['message'] = "Coin amount and address are required";
	$returnArr['data'] = '';
	echo json_encode($returnArr); die;
}

$personal->unlockAccount($fromWalletAddress, $password, function ($err, $unlocked) {
	if ($err !== null) {
		$returnArr = [];
		$returnArr['success'] = false;
		$returnArr['message'] = "Account is locked";
		$returnArr['data'] = '';
		echo json_encode($returnArr); die;
		/* echo 'Error: ' . $err->getMessage();
		return; */
	}
/* 	if ($unlocked) {
        echo 'New account is unlocked!' . PHP_EOL;
	} else {
	    echo 'New account isn\'t unlocked' . PHP_EOL;
	} */
});



$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"initialSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_value","type":"uint256"}],"name":"burn","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_subtractedValue","type":"uint256"}],"name":"decreaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_addedValue","type":"uint256"}],"name":"increaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"burner","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Burn","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]';



$contract = new Contract($web3->provider, $testAbi);

$contractAddress = '0x823DCD364a0B3F2519751214ca1BcAA093826b90'; 
$functionName = "transfer";
$toAccount = $toWalletAddress;
$fromAccount = $fromWalletAddress;
$coinAmount = $coinAmount*1000000000000000000;

$coinAmount = dec2hex($coinAmount);


$transactionId = '';

 $contract->at($contractAddress)->send($functionName, $toAccount, $coinAmount, [
                        'from' => $fromAccount,
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x1A13B8600'    //7000000000wei // 7 gwei
						//'gas' => '0xD2F0'
					], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId){
						if ($err !== null) {
							//print_r($err); 
							$returnArr = [];
							$returnArr['success'] = false;
							$returnArr['message'] = "transactions successful";
							$returnArr['data'] = '';
							echo json_encode($returnArr); die;
						}
						 
						$transactionId = $result; 

					});


//return $newAccount;





function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}


$returnArr = [];
$returnArr['success'] = true;
$returnArr['message'] = "transactions successful";
$returnArr['data'] = ['transaction_id'=>$transactionId];

$jsonData = json_encode($returnArr);

$selectQry = "INSERT into apirequests SET apiname = 'coin_transfer',
										  returndata = '".$jsonData."'";

$runQry = mysqli_query($connect,$selectQry);										  

echo $jsonData; die;
?>