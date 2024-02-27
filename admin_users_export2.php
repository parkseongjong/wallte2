<?php
// Page in use
session_start();

ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);

require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;
use Pachico\Magoo\Magoo as walletMasking;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
// https://cybertronchain.com/wallet2/admin_users_export2.php
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

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$filter = walletFilter::getInstance();
$walletMasking = new walletMasking();
$walletLogger = new walletLogger();
$walletLogger = $walletLogger->init();

//2021-08-25 검색된 데이터만 노출 by.ojt
$targetPostData = array(
	'search_string' => 'string',
	'order_by' => 'string',
	'admin_type' => 'string',
	'date1' => 'string',
	'date2' => 'string'
);

$filterData = $filter->postDataFilter($_GET,$targetPostData);

if ( isset($filterData['date1']) && !empty($filterData['date1']) ) {
	$db->where('created_at', $filterData['date1'].' 00:00:00', '>=');
}
if ( isset($filterData['date2']) && !empty($filterData['date2']) ) {
	$db->where('created_at', $filterData['date2'].' 23:59:59', '<=');
}
if ( isset($filterData['admin_type']) && !empty($filterData['admin_type']) ) {
	$db->where('admin_type', $filterData['admin_type']);
}

if(isset($filterData['search_string']) && empty(!$filterData['search_string'])) {
	$column = array(
		'email',
		'phone',
		'wallet_address',
		'virtual_wallet_address',
		'wallet_address_change',
		'name',
		'auth_name',
		'auth_phone',
		'external_phone',
		'n_phone',
	);
	foreach ($column as $key => $value){
		if($key == 0){
			$db->where('convert('.$value.' using utf8)',$filterData['search_string']);
		}
		$db->orWhere('convert('.$value.' using utf8)',$filterData['search_string']);
	}
}

if(isset($filterData['order_by'])){
	if($filterData['order_by'] == 'ASC'){
		$db->orderBy('id', $filterData['order_by']);
	}
	else{
		$db->orderBy('id', 'DESC');
	}
}

$result = $db->get('admin_accounts');

if($filterData['admin_type'] == 'user'){
	$walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 목록 > 엑셀 파일 NEW 다운로드',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'DOWNLOAD']);
}
else{
	$walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 관리자 목록 > 엑셀 파일 NEW 다운로드',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'DOWNLOAD']);
}

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

$headers = array('#','Register with','Lname','Name','Email','Admin type('.$s1.')','Email_verify('.$s2.')','Wallet Address','PVT Key','CTC2 Balance','TP3 Balance','USDT Balance','MC Balance','KRW Balance','ETH Balance','Phone','Date','gender('.$s5.')','dob('.$s6.')','location('.$s7.')',$s8,$s9,$s10,$s11,$s12,$s13,$s14, 'Last Login Date', 'login_or_not', 'id', 'phone1', 'name1', 'eCTC', 'eTP3', 'eMC', 'eKRW', 'transfer approved', 'transfer_fee_type', 'wallet_address_change', 'wallet_change_apply', 'sendTransaction', 'sendTransaction-TF', 'approve-ctc', 'approve-tp3', 'approve-mc', 'approve-ctc-TF', 'approve-tp3-TF', 'approve-mc-TF', 'Now_Wallet_Address', 'Old_Wallet_Address', 'sendapproved', 'tp_approved', 'mc_approved');
fputcsv($file,$headers);
$k=1;
foreach ($result as $row) {

	$wallet_address = $row['wallet_address'];
	$wallet_address_old = '';


	if ( $row['wallet_change_apply'] == 'Y' ||  $row['id'] >= 10900 ) {
		$walletAddress = $row['wallet_address'];
		$wallet_address_old = !empty($row['wallet_address_change']) ? $row['wallet_address_change'] : '';
	} else {
		$walletAddress = $row['wallet_address_change']; //--------- (New Wallet Address)
		$wallet_address_old = $row['wallet_address'];
	}



	$userGcgAmt = 0;
	$userTokenPayAmt = 0;
	$userUsdtAmt = 0;
	$userMcAmt = 0;
	$userKrwAmt = 0;
	$userEthAmt = 0;
	if ($wallet_address != '' && strlen($wallet_address) > 10) {
		$userGcgAmt = getMyCTCbalance($wallet_address,$testAbi,$contractAddress, $n_connect_ip, $n_connect_port);
		$userTokenPayAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$tokenPayContractAddress,1000000000000000000, $n_connect_ip, $n_connect_port);
		$userUsdtAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$usdtContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userMcAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$marketCoinContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userKrwAmt = getMyTokenBalance($wallet_address,$tokenPayAbi,$koreanWonContractAddress,1000000, $n_connect_ip, $n_connect_port);
		$userEthAmt = getMyETHBalance($wallet_address, $n_connect_ip, $n_connect_port);
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
	$db = getDbInstance();
	$db->where('user_id', $row['id']);
	$db->where('del', 'use');
	$ethsend = $db->get('ethsend');
	if ( !empty($ethsend) ) {
		foreach($ethsend as $row2) {

			if ( $row2['ethmethod'] == 'sendTransaction' ) {
				$sendTransaction_id = $row2['tx_id'];
				if ( $row2['status'] == 'Completed' || $row2['status'] == 'Failed' ) {
					$sendTransaction_result = $row2['status'];
				} else {
					$status = $wi_wallet_infos->wi_get_status($row2['tx_id']);
					if ( $status == 'Completed' || $status == 'Failed' ) {
						$sendTransaction_result = $status;
						
						$updateArr = [];
						$updateArr['status'] = $status;
						$db = getDbInstance();
						$db->where("id", $row2['id']);
						$last_id = $db->update('ethsend', $updateArr);

					}
				}
			} // sendTransaction 종료

			if ( $row2['ethmethod'] == 'approve' && $row2['coin_type'] == 'ctc') {
				$approve_ctc_id = $row2['tx_id'];
				if ( $row2['status'] == 'Completed' || $row2['status'] == 'Failed' ) {
					$approve_ctc_result = $row2['status'];
				} else {
					$status = $wi_wallet_infos->wi_get_status($row2['tx_id']);
					if ( $status == 'Completed' || $status == 'Failed' ) {
						$approve_ctc_result = $status;
						
						$updateArr = [];
						$updateArr['status'] = $status;
						$db = getDbInstance();
						$db->where("id", $row2['id']);
						$last_id = $db->update('ethsend', $updateArr);

					}
				}
			} // approve-ctc 종료

			if ( $row2['ethmethod'] == 'approve' && $row2['coin_type'] == 'tp') {
				$approve_tp3_id = $row2['tx_id'];
				if ( $row2['status'] == 'Completed' || $row2['status'] == 'Failed' ) {
					$approve_tp3_result = $row2['status'];
				} else {
					$status = $wi_wallet_infos->wi_get_status($row2['tx_id']);
					if ( $status == 'Completed' || $status == 'Failed' ) {
						$approve_tp3_result = $status;
						
						$updateArr = [];
						$updateArr['status'] = $status;
						$db = getDbInstance();
						$db->where("id", $row2['id']);
						$last_id = $db->update('ethsend', $updateArr);

					}
				}
			} // approve-tp3 종료

			if ( $row2['ethmethod'] == 'approve' && $row2['coin_type'] == 'mc') {
				$approve_mc_id = $row2['tx_id'];
				if ( $row2['status'] == 'Completed' || $row2['status'] == 'Failed' ) {
					$approve_mc_result = $row2['status'];
				} else {
					$status = $wi_wallet_infos->wi_get_status($row2['tx_id']);
					if ( $status == 'Completed' || $status == 'Failed' ) {
						$approve_mc_result = $status;
						
						$updateArr = [];
						$updateArr['status'] = $status;
						$db = getDbInstance();
						$db->where("id", $row2['id']);
						$last_id = $db->update('ethsend', $updateArr);

					}
				}
			} // approve-mc 종료

		} // foreach
	} // if

	if ( !empty($row['id_auth']) && $row['id_auth'] == 'Y' ) {
		$phone1 = empty($row['auth_phone'])?false:$walletMasking->reset()->pushPhoneMask('other')->getMasked($row['auth_phone']);
	} else {
		$phone1 = empty($row['email'])?false:$walletMasking->reset()->pushUniversalIdMask()->getMasked($row['email']);
	}

	$name1 = get_user_real_name($row['auth_name'], $row['name'], $row['lname']);
	$name1 = empty($name1)?false:$walletMasking->reset()->pushNameMask()->getMasked($name1);
	$name1 = mb_convert_encoding( $name1, "EUC-KR", "UTF-8" );

	$arr = [];
	$arr['#'] = $row['id'];
	$arr['Register with'] = $row['register_with'];
	$arr['Lname'] = mb_convert_encoding( empty($row['lname'])?false:$walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['lname'])), "EUC-KR", "UTF-8" );
	$arr['Name'] = mb_convert_encoding(empty($row['name'])?false:$walletMasking->reset()->pushNameMask()->getMasked(htmlspecialchars($row['name'])), "EUC-KR", "UTF-8" );
	//$arr['Email'] = ($row['register_with']=='email') ? htmlspecialchars($row['email']) : "" ;
	$arr['Email'] = '="'.empty($row['email'])?false:$walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['email'])).'"';
	$arr['Admin type('.$s1.')'] = $row['admin_type'];
	$arr['Email_verify('.$s2.')'] = $row['email_verify'];
	$arr['Wallet Address'] = htmlspecialchars($row['wallet_address']);
	$arr['PVT Key'] = htmlspecialchars($row['pvt_key']);
	$arr['CTC2 Balance'] = new_number_format($userGcgAmt, $n_decimal_point_array['ctc']);
	$arr['TP3 Balance'] = new_number_format($userTokenPayAmt, $n_decimal_point_array['tp3']);
	$arr['USDT Balance'] = new_number_format($userUsdtAmt, $n_decimal_point_array['usdt']);
	$arr['MC Balance'] = new_number_format($userMcAmt, $n_decimal_point_array['mc']);
	$arr['KRW Balance'] = new_number_format($userKrwAmt, $n_decimal_point_array['krw']);
	$arr['ETH Balance'] =  new_number_format($userEthAmt, $n_decimal_point_array['eth']);
	$arr['Phone'] = $row['phone'] != '' ? '="'.empty($row['phone'])?false:$walletMasking->reset()->pushUniversalIdMask()->getMasked(htmlspecialchars($row['phone'])).'"' : '';
	$arr['Date'] = htmlspecialchars($row['created_at']);
	$arr['gender('.$s5.')'] = $row['gender'];
	$arr['dob('.$s6.')'] = $row['dob'];
	$arr['location('.$s7.')'] = mb_convert_encoding( htmlspecialchars($row['location']), "EUC-KR", "UTF-8" );
	$arr[$s8] = $row['id_auth'];
	$arr[$s9] = $row['id_auth_at'];
	$arr[$s10] = '="'.empty($row['auth_phone'])?false:$walletMasking->reset()->pushPhoneMask('other')->getMasked($row['auth_phone']).'"';
	$arr[$s11] = mb_convert_encoding( empty($row['auth_name'])?false:$walletMasking->reset()->pushNameMask('other')->getMasked($row['auth_name']), "EUC-KR", "UTF-8" );
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
	$arr['transfer approved'] = $row['transfer_approved'];
	$arr['transfer_fee_type'] = $row['transfer_fee_type'];
	$arr['wallet_address_change'] = $row['wallet_address_change'];
	$arr['wallet_change_apply'] = $row['wallet_change_apply'];
	
	$arr['sendTransaction'] = $sendTransaction_id;;
	$arr['sendTransaction-TF'] = $sendTransaction_result;
	$arr['approve-ctc'] = $approve_ctc_id;
	$arr['approve-tp3'] = $approve_tp3_id;
	$arr['approve-mc'] = $approve_mc_id;
	$arr['approve-ctc-TF'] = $approve_ctc_result;
	$arr['approve-tp3-TF'] = $approve_tp3_result;
	$arr['approve-mc-TF'] = $approve_mc_result;
	
	$arr['Now_Wallet_Address'] = $wallet_address;
	$arr['Old_Wallet_Address'] = $wallet_address_old;
	
	$arr['sendapproved'] = $row['sendapproved'];
	$arr['tp_approved'] = $row['tp_approved'];
	$arr['mc_approved'] = $row['mc_approved'];

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



?>	
