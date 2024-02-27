<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

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

$db->where('email', 'ajay@mailinator.com', '!=');
//$db->where('admin_type', 'admin');
$db->orderBy('id', 'DESC');
$result = $db->get('admin_accounts'); 

$file = fopen('php://output', 'w');

$headers = array('#','Lname','Name','Email','Wallet Address','CTC Balance','Phone','Date','PanNo','AccountNo','IfscCode','BankName');
$walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 목록 > 엑셀 파일 다운로드',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'DOWNLOAD']);
fputcsv($file,$headers);
$k=1;
foreach ($result as $row) {
	$userGcgAmt = getMyCTCbalance($row['wallet_address'],$testAbi,$contractAddress, $n_connect_ip, $n_connect_port);
	$userGcgAmt = new_number_format($userGcgAmt, $n_decimal_point_array['ctc']);
	$arr = [];
	$arr['#'] = $k;
	$arr['Lname'] = mb_convert_encoding( htmlspecialchars($row['lname']), "EUC-KR", "UTF-8" );
	$arr['Name'] = mb_convert_encoding( htmlspecialchars($row['name']), "EUC-KR", "UTF-8" );
	$arr['Email'] = ($row['register_with']=='email') ? htmlspecialchars($row['email']) : "" ;
	//$arr['Password'] = htmlspecialchars($row['passwd_b']);
	$arr['Wallet Address'] = htmlspecialchars($row['wallet_address']);
	$arr['CTC Balance'] = $userGcgAmt;
	//$arr['Phone'] = htmlspecialchars($row['phone']);
	$arr['Phone'] = $row['phone'] != '' ? '="'.htmlspecialchars($row['phone']).'"' : '';
	$arr['Date'] = htmlspecialchars($row['created_at']);
	$arr['PanNo'] = htmlspecialchars($row['pan_no']);
	$arr['AccountNo'] = "'0".$row['bank_ac_no']."'";
	$arr['IfscCode'] = htmlspecialchars($row['ifsc_code']);
	$arr['BankName'] = htmlspecialchars($row['bank_name']);
	
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

?>	
