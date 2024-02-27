<?php

session_start();
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
$eth = $web3->eth;
$personal = $web3->personal;

	
require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use \Messente\Omnichannel\Api\OmnimessageApi;
use \Messente\Omnichannel\Configuration;
use \Messente\Omnichannel\Model\Omnimessage;
use \Messente\Omnichannel\Model\SMS;


return;
exit;


	
for($x=0;$x<50;$x++)
{


	$newAccount = '';
//	$personal->newAccount('onefamilyhappynewyear2020ONEFAMILYHAPPYNEWYEAR2020', function ($err, $account) use (&$newAccount) {
	$personal->newAccount('market2020MARKET@)@)barrybarriesBARRYBARRIES@)@)MARKET2020market', function ($err, $account) use (&$newAccount) {

		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			
		}
		else {
			$newAccount = $account;
		}
	});
	
	
	echo $newAccount;
	echo "<br>";
	

}


$web3 = NULL;
unset($web3);




function validate_mobile($mobile)
{
    return preg_match('/^[0-9]{10}+$/', $mobile);
}

function getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

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