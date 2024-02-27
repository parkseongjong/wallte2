<?php
exit();
require_once './config/config.php';
require_once './includes/web3/vendor/autoload.php';         // Web3 용
require_once './test7_common.php';

//require_once '/var/www/html/wallet2/vendor/autoload.php';
//require_once(__DIR__ . '/messente_api/vendor/autoload.php');

use Web3\Web3;
use Web3\Contract;


$walletAddressArr = array('0xcea66e2f92e8511765bc1e2a247c352a7c84e895','0x83e3efc3f0301235981f2049a0579089b79bd495');


$timeout = 30; // set this time accordingly by default it is 1 sec

// $web3 = new Web3('https://mainnet.infura.io/v3/e9bbe05f9dc949838c685503e32c4334');
$web3 = new Web3('http://127.0.0.1:8545/', $timeout);


$eth = $web3->eth;
$contract = new Contract($web3->provider, $testAbi);

$mobile = checkMobile();

if ($mobile) {
    echo "<div style='width:100%; min-height:300px; background:#ddd'>";
} else {
    echo "<div style='width:480px; min-height:300px; background:#ddd; margin:0 auto'>";
}

foreach($walletAddressArr as $walletAddress) {

	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		if ($err !== null) {
			echo 'Error: eth ' . $err->getMessage(); return;
		}
		$getBalance = $balance->toString();
	});

	$contract->at($contractAddress)->call("balanceOf", $walletAddress,function($err, $result) use (&$coinBalance){
		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage(); return;
		}
		if ( !empty( $result ) ) {
			$coinBalance = reset($result)->toString();
		} else {
            echo 'reset error';
		}
	});

	$contract->at($tokenPayContractAddress)->call("balanceOf", $walletAddress,function($err, $result) use (&$coinBalance1){
		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage(); return;
		}
		if ( !empty( $result ) ) {
			$coinBalance1 = reset($result)->toString();
		} else {
            echo 'reset error';
		}
	});

	$contract->at($usdtContractAddress)->call("balanceOf", $walletAddress,function($err, $result) use (&$coinBalance2){
		if ($err !== null) {
			echo 'Error: contract ' . $err->getMessage(); return;
		}
		if ( !empty( $result ) ) {
			$coinBalance2 = reset($result)->toString();
		} else {
            echo 'reset error';
		}
	});

    /*
    $tokenPay = $erc20->token($tokenPayContractAddress);
    $usdtObj = $erc20->token($usdtContractAddress);
    */
	

    $logs = array();

    $logs['WalletAddress'] = '';// . $walletAddress;

    $logs['Balance (eth)'] = $getBalance;
    $logs['Balance (com)'] = $getBalance/1000000000000000000;

    $logs['Coin Balance (ctc)'] = $coinBalance;
    $logs['Coin Balance (com)'] = $coinBalance/1000000000000000000;

    $logs['Coin Balance1 (tp)'] = $coinBalance1;
    $logs['Coin Balance1 (com)'] = $coinBalance1/1000000000000000000;

    $logs['Coin Balance2 (usdt)'] = $coinBalance2;
    $logs['Coin Balance2 (com)'] = $coinBalance2/1000000;

    //echo '<ul><li>' . implode("</li><li>", $logs) . '</li></ul>';

    echo '<ul>';
    foreach ($logs as $k => $v) {
        echo "<li>{$k} : {$v}</li>";
    }
    echo '</ul>';

/*
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
*/
}

    $logs = array();
    $logs[] = "현재 날짜 : ". date("Y-m-d");
    $logs[] = "현재 시간 : ". date("H:i:s");
    $logs[] = "현재 일시 : ". date("Y-m-d H:i:s");

    echo '<div>' . implode("</div><div>", $logs) . '</div>';
?>





<?php
define('DEFAULT_SCALE', 8);
define('MONEY_SCALE', 6);

require_once '/var/www/html/wallet2/vendor/autoload.php';    // EthereumRPC, ERC20 용

use EthereumRPC\EthereumRPC;
use ERC20\ERC20;

$geth = new EthereumRPC('127.0.0.1', 8545);
$erc20 = new \ERC20\ERC20($geth);

$walletAddressArr = array(
    '0xcea66e2f92e8511765bc1e2a247c352a7c84e895',
    '0x83e3efc3f0301235981f2049a0579089b79bd495'
);
$walletAddress = $walletAddressArr[1];

$logs = array(
    'ethBalance' => 0,
    'ctcBalance' => 0,
    'tpBalance'  => 0,
    'usdBalance' => 0,
    'mcBalance'  => 0,
    'krwBalance' => 0,
);

try {
    $logs['ethBalance'] = $geth->eth()->getBalance($walletAddress);

    $ethObj = $erc20->token($contractAddress);
    $balance = $ethObj->balanceOf($walletAddress,false);
    $logs['ctcBalance'] = bcdiv($balance, bcpow("10", "18", 0), DEFAULT_SCALE);

	// tp3 balance
    $tokenPay = $erc20->token($tokenPayContractAddress);
    $balance = $tokenPay->balanceOf($walletAddress,false);
    $logs['tpBalance'] = bcdiv($balance, bcpow("10", "18", 0), DEFAULT_SCALE);

	// usdt balance
	$usdtObj = $erc20->token($usdtContractAddress);
	$balance = $usdtObj->balanceOf($walletAddress,false);
	$logs['usdBalance'] = bcdiv($balance, bcpow("10", "6", 0), MONEY_SCALE);

	// mc balance
	$mcObj = $erc20->token($marketCoinContractAddress);
	$balance = $mcObj->balanceOf($walletAddress,false);
	$logs['mcBalance'] = bcdiv($balance, bcpow("10", "6", 0), MONEY_SCALE);

	// krw balance
	$krwObj = $erc20->token($koreanWonContractAddress);
	$balance = $krwObj->balanceOf($walletAddress,false);
	$logs['krwBalance'] = bcdiv($balance, bcpow("10", "6", 0), MONEY_SCALE);

} catch(Exception $e) {
    echo "error" . error_reporting(0); return;
}



?>
<style type="text/css">
#container {}
.section { padding: 16px 24px; min-height: 80px; margin: 16px; border-radius: 8px; background: #ffcc66; }

.title { font-size: 14px; }
.balance { margin: 10px 0 20px; font-size: 30px; text-align: right; }
.unit { color: #9a9a9a; }
.command { overflow:hidden; list-style-type:none; }
.command li { float:left; width:49%; }
</style>

<div id="container">

    <div class="section">
        <div class="title">Ethereum</div>
        <div class="balance">
            <?=$logs['ethBalance'] ?>
            <span class="unit">ETH</span>
        </div>
    </div>

    <div class="section">
        <div class="title">CyberTronChain</div>
        <div class="balance">
            <?=floatval($logs['ctcBalance']) ?>
            <span class="unit">CTC</span>
        </div>
    </div>

    <div class="section">
        <div class="title">Token Play</div>
        <div class="balance">
            <?=floatval($logs['tpBalance']) ?>
            <span class="unit">TP3</span>
        </div>
    </div>

    <div class="section">
        <div class="title">Tether USD</div>
        <div class="balance">
            <?=floatval($logs['usdBalance']) ?>
            <span class="unit">USDT</span>
        </div>
    </div>

    <div class="section">
        <div class="title">MC Token</div>
        <div class="balance">
            <?=$logs['mcBalance'] ?>
            <span class="unit">MC</span>
        </div>
    </div>

    <div class="section">
        <div class="title">KRW Token</div>
        <div class="balance">
            <?=$logs['krwBalance'] ?>
            <span class="unit">KRW</span>
        </div>
    </div>

    <div class="section">
        <div class="title">매장포인트</div>
        <div class="balance">
            <?=number_format(0,2) ?>
            <span class="unit">P</span>
        </div>
    </div>

</div>
