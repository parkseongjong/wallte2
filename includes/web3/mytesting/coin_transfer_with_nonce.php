<?php 
require('./config.php');
use Web3\Web3;
use Web3\Contract;

$personal = $web3->personal;



$fromAccount  = $_POST['fromWalletAddress'];
$toAccount  = $_POST['toWalletAddress'];
$nonce  = $_POST['nonce'];
$amount  = $_POST['amount'];
$token_type  = $_POST['token_type'];



$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";  

$personal->unlockAccount($adminAccountWalletAddress, $adminAccountWalletPassword, function ($err, $unlocked) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
/* 	if ($unlocked) {
        echo 'New account is unlocked!' . PHP_EOL;
	} else {
	    echo 'New account isn\'t unlocked' . PHP_EOL;
	} */
});



$testAbi = $contractAddressArr[$token_type]['abi'];




$contract = new Contract($web3->provider, $testAbi);
$coinAmount  =$amount;
$contractAddress = $contractAddressArr[$token_type]['contractAddress']; 
$functionName = "transferFrom";

$fromAccount = $adminAccountWalletAddress;
$coinAmount = $coinAmount*$contractAddressArr[$token_type]['decimal'];

$coinAmount = '0x'.dechex($coinAmount);


$transactionId = '';








 $contract->at($contractAddress)->send($functionName, $fromAccount, $toAccount, $coinAmount, [
                        'from' => $fromAccount,
						'gas' => '0x'.dechex(50000),
						//'gasprice' =>'0x9502F9000',
						'nonce' => '0x'.dechex($nonce),
						//'gas' => '0xD2F0'
					], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId){
						if ($err !== null) {
							print_r($err);  die;
						}
						 
						print_r($result); die;  

					});
/* $ownerAccount = '0x4207a1d716809b6efc9520af280f542309067619';	
$toAccount = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount, $coinAmount, [
                        'from' => $adminAccountWalletAddress,
						//'gas' => '0x186A0',   //100000
						//'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei 
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x6FC23AC00' ,   //30000000000 // 9 gwei
						'nonce' => '0x54D'
					], function ($err, $result) use ($contract, $ownerAccount, $toAccount, &$transactionId) {
						if ($err !== null) {
							print_r($err);
							$transactionId = '';
						}
						else {
							$transactionId = $result;
						}
					});		 */			

echo $transactionId;
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
?>