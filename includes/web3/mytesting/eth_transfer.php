<?php 
require('./config.php');
use Web3\Web3;
/* 
$password  = $_POST['password'];
$fromAccount  = $_POST['fromWalletAddress'];
$toAccount  = $_POST['toWalletAddress']; */

$password  = "sdfdJdfOegbboixe";
$fromAccount  = "0x94386aef5129978b9429062adc978e9b6f6292f7";
$toAccount  = "0x8f75E4E6110F5112E37B260B3644473cc2085d71";

//$amount  = $_POST['amount'];
$amount = 5;

$personal = $web3->personal;
$personal->unlockAccount($fromAccount, $password, function ($err, $unlocked) {
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


$eth = $web3->eth;
// get balance
$eth->getBalance($fromAccount, function ($err, $balance) use($fromAccount) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
	echo $fromAccount . ' Balance: ' . $balance . PHP_EOL;
});


// send transaction
$eth->sendTransaction([
	'from' => $fromAccount,
	'to' => $toAccount,
	'value' => '0x9184e72a'
], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, $amount) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
	echo 'Tx hash: ' . $transaction . PHP_EOL;

});



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