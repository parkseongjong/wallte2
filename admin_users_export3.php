<?php
// Test Page - 엑셀출력용
if($_SERVER['REMOTE_ADDR'] != '112.171.120.140'){
	exit();
}
session_start();
//exit();
ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);  

require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
use \Web3\Providers\HttpProvider;
use \Web3\RequestManagers\HttpRequestManager;
// https://cybertronchain.com/wallet2/admin_users_export3.php
require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}
$filename = time().'export.csv';

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');

$db = getDbInstance();

if ( isset($_GET['date1']) && !empty($_GET['date1']) ) {
	$db->where('created_at', $_GET['date1'].' 00:00:00', '>=');
}
if ( isset($_GET['date2']) && !empty($_GET['date2']) ) {
	$db->where('created_at', $_GET['date2'].' 23:59:59', '<=');
}
if ( isset($_GET['admin_type']) && !empty($_GET['admin_type']) ) {
	$db->where('admin_type', $_GET['admin_type']);
}

/*
 * sleep user inner.
 *
 */
//$db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
//$db->where('A.account_type2', 'wallet');
//$db->orderBy('B.id', 'DESC');
//$result = $db->get('admin_accounts A', null,'*');

/*
 * normal user
 *
 */
$db->where('account_type2', 'wallet');
$db->orderBy('id', 'DESC');
$result = $db->get('admin_accounts');

//ojt admin id : 11863
//20210825 12:14 13320

$file = fopen('php://output', 'w');

$s1 = mb_convert_encoding( '유형', "EUC-KR", "UTF-8" );
$s2 = mb_convert_encoding( '로그인허용여부', "EUC-KR", "UTF-8" );
$s5 = mb_convert_encoding( '성별', "EUC-KR", "UTF-8" );
$s6 = mb_convert_encoding( '생년월일', "EUC-KR", "UTF-8" );
$s7 = mb_convert_encoding( '지역', "EUC-KR", "UTF-8" );
$s8 = mb_convert_encoding( '본인인증여부', "EUC-KR", "UTF-8" );
$s9 = mb_convert_encoding( '본인인증일시', "EUC-KR", "UTF-8" );
$s10 = mb_convert_encoding( '본인인증 휴대폰번호', "EUC-KR", "UTF-8" );
$s11 = mb_convert_encoding( '본인인증이름', "EUC-KR", "UTF-8" );
$s12 = mb_convert_encoding( '본인인증성별', "EUC-KR", "UTF-8" );
$s13 = mb_convert_encoding( '본인인증생년월일', "EUC-KR", "UTF-8" );
$s14 = mb_convert_encoding( '본인인증 내(Kor)/외국인(For)', "EUC-KR", "UTF-8" );

$headers = array('#','Register with','Lname','Name','Email','Admin type('.$s1.')','Email_verify('.$s2.')','Wallet Address','PVT Key','CTC','CTC(Old CTC)', 'TP3','USDT','MC','KRW','ETH', 'wallet_address_change','CTC(OldAddress)', 'CTC(Old CTC, OldAddress)', 'TP3(OldAddress)', 'USDT(OldAddress)', 'MC(OldAddress)', 'KRW(OldAddress)', 'ETH(OldAddress)', 'Phone','Date','gender('.$s5.')','dob('.$s6.')','location('.$s7.')',$s8,$s9,$s10,$s11,$s12,$s13,$s14, 'Last Login Date', 'login_or_not', 'id', 'phone1', 'name1', 'eCTC', 'eTP3', 'eMC', 'eKRW', 'eETH', 'eUSDT', 'transfer approved', 'transfer_fee_type', 'wallet_change_apply');
fputcsv($file,$headers);
$k=1;
foreach ($result as $row) {

	$wallet_address = '';
	$wallet_address_old = '';
	
	/*
	if ( $row['id'] >= 10900 ) {
		$walletAddress = $row['wallet_address'];
	} else {
		if ( $row['wallet_change_apply'] == 'Y' ) {
			$walletAddress = $row['wallet_address'];
			if ( !empty($row['wallet_address_change']) ) {
				$wallet_address_old = $row['wallet_address_change'];
			}
		} else {
			$wallet_address_old = $row['wallet_address'];
			if ( !empty($row['wallet_address_change']) ) {
				$walletAddress = $row['wallet_address_change'];
			}
		}
	}
	*/

	
	$wallet_address = $row['wallet_address'];
	$wallet_address_old = $row['wallet_address_change'];
	
	$userGcgAmt = 0;
	$userTokenPayAmt = 0;
	$userUsdtAmt = 0;
	$userMcAmt = 0;
	$userKrwAmt = 0;
	$userEthAmt = 0;
	$userGcgAmt_OldCTC = 0;
	if ($wallet_address != '' && strlen($wallet_address) > 10) {
		$userGcgAmt = getMyCTCbalance($wallet_address,$testAbi,$contractAddress, $n_connect_ip, $n_connect_port);
		$userGcgAmt_OldCTC = getMyCTCbalanceOld($wallet_address,$n_connect_ip, $n_connect_port);
		$userTokenPayAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$tokenPayContractAddress,1000000000000000000, $n_connect_ip, $n_connect_port);
		$userUsdtAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$usdtContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userMcAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$marketCoinContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userKrwAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$koreanWonContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userEthAmt = getMyETHBalance($wallet_address, $n_connect_ip, $n_connect_port);
	}
	
	$userGcgAmt_old = 0;
	$userTokenPayAmt_old = 0;
	$userUsdtAmt_old = 0;
	$userMcAmt_old = 0;
	$userKrwAmt_old = 0;
	$userEthAmt_old = 0;
	$userGcgAmt_OldCTC2 = 0;
	if ($wallet_address_old != '' && strlen($wallet_address_old) > 10) {
		$userGcgAmt_old = getMyCTCbalance($wallet_address_old,$testAbi,$contractAddress, $n_connect_ip, $n_connect_port);
		$userGcgAmt_OldCTC2 = getMyCTCbalanceOld($wallet_address_old,$n_connect_ip, $n_connect_port);
		$userTokenPayAmt_old = getMyTokenBalance($wallet_address_old,$tokenPayAbi,$tokenPayContractAddress,1000000000000000000, $n_connect_ip, $n_connect_port);
		$userUsdtAmt_old = getMyTokenBalance($wallet_address_old,$tokenPayAbi,$usdtContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userMcAmt_old = getMyTokenBalance($wallet_address_old,$tokenPayAbi,$marketCoinContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userKrwAmt_old = getMyTokenBalance($wallet_address_old,$tokenPayAbi,$koreanWonContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userEthAmt_old = getMyETHBalance($wallet_address_old, $n_connect_ip, $n_connect_port);
	}


	$sendTransaction_id = '';
	$sendTransaction_result = '';
	$approve_ctc_id = '';
	$approve_ctc_result = '';
	$approve_tp3_id = '';
	$approve_tp3_result = '';
	$approve_mc_id = '';
	$approve_mc_result = '';
	$status = '';
	
	if ( !empty($row['id_auth']) && $row['id_auth'] == 'Y' ) {
		$phone1 = $row['auth_phone'];
	} else {
		$phone1 = $row['email'];
	}
	$name1 = get_user_real_name($row['auth_name'], $row['name'], $row['lname']);
	$name1 = mb_convert_encoding( $name1, "EUC-KR", "UTF-8" );


	$arr = [];
	$arr['#'] = $row['id'];
	$arr['Register with'] = $row['register_with'];
	$arr['Lname'] = mb_convert_encoding( htmlspecialchars($row['lname']), "EUC-KR", "UTF-8" );
	$arr['Name'] = mb_convert_encoding( htmlspecialchars($row['name']), "EUC-KR", "UTF-8" );
	//$arr['Email'] = ($row['register_with']=='email') ? htmlspecialchars($row['email']) : "" ;
	$arr['Email'] = '="'.htmlspecialchars($row['email']).'"';
	$arr['Admin type('.$s1.')'] = $row['admin_type'];
	$arr['Email_verify('.$s2.')'] = $row['email_verify'];
	$arr['Wallet Address'] = htmlspecialchars($row['wallet_address']);
	$arr['PVT Key'] = htmlspecialchars($row['pvt_key']);
	$arr['CTC'] = new_number_format($userGcgAmt, $n_decimal_point_array['ctc']);
	$arr['CTC(Old CTC)'] = new_number_format($userGcgAmt_OldCTC, $n_decimal_point_array['ctc']);
	$arr['TP3'] = new_number_format($userTokenPayAmt, $n_decimal_point_array['tp3']);
	$arr['USDT'] = new_number_format($userUsdtAmt, $n_decimal_point_array['usdt']);
	$arr['MC'] = new_number_format($userMcAmt, $n_decimal_point_array['mc']);
	$arr['KRW'] = new_number_format($userKrwAmt, $n_decimal_point_array['krw']);
	$arr['ETH'] =  new_number_format($userEthAmt, $n_decimal_point_array['eth']);
	$arr['wallet_address_change'] = $row['wallet_address_change'];
	$arr['CTC(OldAddress)'] = new_number_format($userGcgAmt_old, $n_decimal_point_array['ctc']);
	$arr['CTC(Old CTC, OldAddress)'] = new_number_format($userGcgAmt_OldCTC2, $n_decimal_point_array['ctc']);
	$arr['TP3(OldAddress)'] = new_number_format($userTokenPayAmt_old, $n_decimal_point_array['tp3']);
	$arr['USDT(OldAddress)'] = new_number_format($userUsdtAmt_old, $n_decimal_point_array['usdt']);
	$arr['MC(OldAddress)'] = new_number_format($userMcAmt_old, $n_decimal_point_array['mc']);
	$arr['KRW(OldAddress)'] = new_number_format($userKrwAmt_old, $n_decimal_point_array['krw']);
	$arr['ETH(OldAddress)'] =  new_number_format($userEthAmt_old, $n_decimal_point_array['eth']);
	$arr['Phone'] = $row['phone'] != '' ? '="'.htmlspecialchars($row['phone']).'"' : '';
	$arr['Date'] = htmlspecialchars($row['created_at']);
	$arr['gender('.$s5.')'] = $row['gender'];
	$arr['dob('.$s6.')'] = $row['dob'];
	$arr['location('.$s7.')'] = mb_convert_encoding( htmlspecialchars($row['location']), "EUC-KR", "UTF-8" );
	$arr[$s8] = $row['id_auth'];
	$arr[$s9] = $row['id_auth_at'];
	$arr[$s10] = '="'.$row['auth_phone'].'"';
	$arr[$s11] = mb_convert_encoding( htmlspecialchars($row['auth_name']), "EUC-KR", "UTF-8" );
	$arr[$s12] = $row['auth_gender'];
	$arr[$s13] = $row['auth_dob'];
	$arr[$s14] = $row['auth_local_code'];
	$arr['Last Login Date'] = $row['last_login_at'];
	$arr['login_or_not'] = $row['login_or_not'];
	$arr['id'] = $row['id'];
	$arr['phone1'] = '="'.$phone1.'"';
	$arr['name1'] = $name1;
	$arr['eCTC'] = $row['etoken_ectc'];
	$arr['eTP3'] = $row['etoken_etp3'];
	$arr['eMC'] = $row['etoken_emc'];
	$arr['eKRW'] = $row['etoken_ekrw'];
	$arr['eETH'] = $row['etoken_eeth'];
	$arr['eUSDT'] = $row['etoken_eusdt'];
	$arr['transfer approved'] = $row['transfer_approved'];
	$arr['transfer_fee_type'] = $row['transfer_fee_type'];
	$arr['wallet_change_apply'] = $row['wallet_change_apply'];
		
	//$arr['Now_Wallet_Address'] = $wallet_address;
	//$arr['Old_Wallet_Address'] = $wallet_address_old;
	

    fputcsv($file,$arr);
	$k++;
}
fclose($file);
die;



function getMyCTCbalance($address,$testAbi,$contractAddress, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	//$getBalance 	= 0;
	$coinBalance 	= 0;

	$walletAddress = $address;

	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // 127.0.0.1
	//$web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e')));
	/*
	$eth = $web3->eth;

	$sd= $eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});
	*/
	//-- Contranct GCG 
		
	
	
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
        if ( !empty( $result ) ) {
    		$coinBalance = reset($result)->toString();
		} else {
            $coinBalance = -1;
            //echo 'reset error';
        }
	});
	$coinBalance1 = $coinBalance/1000000000000000000;
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	



function getMyETHBalance($walletAddress, $n_connect_ip, $n_connect_port) {
	
	$getBalance = 0;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // 127.0.0.1
	//$web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e')));
	$eth = $web3->eth;
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ( !empty( $balance ) ) {
			$getBalance = $balance->toString();
		} else {
			$getBalance = -1;
		}
		//echo 'Balance: ' . $balance . PHP_EOL;
	});
	return $getBalance/1000000000000000000;
}

function getMyTokenBalance($address,$testAbi,$contractAddress,$setDecimal, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$coinBalance 	= 0;
	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // 127.0.0.1
	//$web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e')));
	$functionName = "balanceOf";

	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
        if ( !empty( $result ) ) {
    		$coinBalance = reset($result)->toString();
		} else {
            $coinBalance = -1;
            //echo 'reset error';
        }
	});
	$coinBalance1 = $coinBalance/$setDecimal;
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	


function getMyCTCbalanceOld($address, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$getBalance 	= 0;
	$coinBalance 	= 0;
	$EthCoinBalance	= 0;

	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	//$web3 = new Web3(new HttpProvider(new HttpRequestManager('https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e')));

	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
	$contractAddress = 'address';
	
	
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		
		$coinBalance1 = $coinBalance/1000000000000000000;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	
 


?>	
