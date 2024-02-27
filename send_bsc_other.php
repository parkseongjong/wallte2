<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
//require('includes/web3/vendor/autoload.php');
//use Web3\Web3;
//use Web3\Contract;
use wallet\common\Log as walletLog;
use wallet\common\Info as walletInfo;
use wallet\common\InfoWeb3 as walletInfoWeb3;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

$filter = walletFilter::getInstance();

//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'address' => 'string',
    'p_token' => 'string',
    'p_kind' => 'string',
    'amount' => 'string',
);

$filterData = $filter->postDataFilter($_POST,$targetPostData);
$filterDataGet = $filter->postDataFilter($_GET,['token'=>'string']);
unset($targetPostData);

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();


$log->info('token 목록 > token(other) send 조회',['target_id'=>0,'action'=>'S']);
$web3Instance = new walletInfoWeb3();
$web3outter = $web3Instance->outterInit();
$web3BscChain = $web3Instance->bscChainInnerInit();
//$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$web3 = $web3Instance->innerInit();
$eth = $web3->eth;

//$gasPriceInWei = 40000000000;
$web3outter->eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});

$gasPriceInWei = "0x".dechex($gasPriceInWei);

$userId = $_SESSION['user_id'];
$db = getDbInstance();
//휴면 계정 확인용 컬럼.
$column = array(
    'A.account_type2','A.virtual_wallet_address','A.id_auth','A.transfer_passwd',
    'B.id','B.email','B.wallet_phone_email','B.register_with','B.passwd','B.passwd_new','B.passwd_salt','B.passwd_datetime',
    'B.name','B.lname','B.user_ip','B.phone','B.gender','B.dob','B.location','B.auth_phone','B.auth_name','B.auth_gender',
    'B.auth_dob','B.auth_local_code','B.n_country','B.n_phone','B.device','B.devId','B.devId2','B.devId3'
);
//2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
//휴면 회원 쪽 조회 START
$db->where("A.id", $_SESSION['user_id']);
$db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
$row = $db->get('admin_accounts A',null,$column);
if(!$row){
    $db->where("id", $_SESSION['user_id']);
    $row = $db->get('admin_accounts');
}
$walletAddress = $row[0]['wallet_address'];
//휴면 회원 쪽 조회 END
//    $db->where("id", $_SESSION['user_id']);
//    $row = $db->get('admin_accounts');
//    $walletAddress = $row[0]['wallet_address'];


// When connecting with a domestic IP, only users who have completed authentication are allowed to send
// If you access overseas IP, you can send without authentication.
$user_id_auth = 'N';
if ( !empty($row[0]['id_auth']) && $row[0]['id_auth'] == 'Y' ) {
	$user_id_auth = 'Y';
}
$ip_kor = '';
$ip_kor = trim(new_ipinfo_ip_chk('2'));
if ($ip_kor == '') {
	$ip_kor = new_kisa_ip_chk();
}
if ($ip_kor == 'KR' && $user_id_auth != 'Y') {
	$_SESSION['failure'] = !empty($langArr['send_auth_need']) ? $langArr['send_auth_need'] : 'Can be used after authentication. Please use after verifying your identity in [My Info].';
	header('Location:profile.php');
	exit();
}

if ( empty($row[0]['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['transfer_pw_message4']) ? $langArr['transfer_pw_message4'] : 'Please set payment password.';
	header('Location:profile.php');
	exit();
}

$allowTokenArr = ['ctc7','ctctm'];
if(!isset($filterDataGet['token']) || empty($filterDataGet['token']) || !in_array($filterDataGet['token'],$allowTokenArr)){
	$_SESSION['failure'] = !empty($langArr['send_message4']) ? $langArr['send_message4'] : 'Invalid Token'; // (2020-05-22, YMJ)
	header('location: index.php');
	exit();
}
$token = strtolower($filterDataGet['token']);
$tokenArr = $contractAddressArr[$token];

// 21.03.23 : 특정 사용자, 특정 코인 사용 불가능한 정보 가져오기
if ( new_get_untransmittable($_SESSION['user_id'], $token) > 0 ) { // 1이면 전송불가
	$_SESSION['failure_error'] = !empty($langArr['error_message1']) ? $langArr['error_message1'] : 'It cannot be moved.';
	header('Location:index.php');
	exit();
}

$return_page = 'send_bsc_other.php?token='.$token;
$return_page2 = 'token.php?token='.$token;

$getNewBalance = 0;
try {
    $web3outter->eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance) {
    //$eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance) {
		if ($err !== null) {
			throw new Exception($err->getMessage(), 1);
		}
		$getNewBalance = $balance->toString();
		$getNewBalance = $getNewBalance/1000000000000000000;
	});
} catch (Exception $e) {
	new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
	header('Location: ' . $return_page2); // $return_page
	exit();
}

$getNewCoinBalance = 0 ;
$functionName = "balanceOf";
$tokenAbi = $tokenArr['abi'];
$tokenContractAddress = $tokenArr['contractAddress'];
$decimalDigit = $tokenArr['decimal'];
//$otherTokenContract = new Contract($web3outter->provider, $tokenAbi);
$otherTokenContract = $web3Instance->innerContract($web3outter->provider, $testAbi);
try {
	// $otherTokenContract->at($tokenContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalance,$decimalDigit){
	// 	if ($err !== null) {
	// 		throw new Exception($err->getMessage(), 2);
	// 	}
	// 	if ( !empty( $result ) ) { // Add (2020-05-18, YMJ)
	// 		$getNewCoinBalance = reset($result)->toString();
	// 		$getNewCoinBalance = $getNewCoinBalance/$decimalDigit;
	// 	}
	// });


    $getNewCoinBalance = $web3Instance->wi_get_bsc_balance($token,$walletAddress,$contractAddressArr);


} catch (Exception $e) {
	new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
	header('Location: ' . $return_page2); // $return_page
	exit();
}

// (2020-05-25, YMJ)
$getMinAmountVal = 0;
if ($token == 'tp3') {
	$getMinAmount = $db->where("module_name", 'min_transfer_amount_tp3')->getOne('settings');
	$getMinAmountVal = $getMinAmount['value'];	
} else if ($token == 'mc') {
	$getMinAmount = $db->where("module_name", 'min_transfer_amount_mc')->getOne('settings');
	$getMinAmountVal = $getMinAmount['value'];	
}

$getExchangeFeeSetting = $db->where("module_name", 'exchange_fee_in_eth')->getOne('settings');
$getExchangeFee = $getExchangeFeeSetting['value'];

$db = getDbInstance();
$db->where("user_id", $_SESSION['user_id']);
$addressList = $db->get('user_withdrawal_addresses');

$allowCoinCnt = 0;





$bscTokenContract = $web3Instance->innerContract($web3BscChain->provider, $tokenAbi);
$bscTokenContract->at($tokenContractAddress)->call("allowance", $walletAddress,$bsc_master_wallet,function($err, $result) use (&$allowCoinCnt){

    
    if ($err !== null) {
        $allowCoinCnt = 0;
    }
    if ( !empty( $result ) ) { 
        $allowCoinCnt = reset($result)->toString();
		$allowCoinCnt = $allowCoinCnt/(10**18);
    }
});


///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

 
if($allowCoinCnt==0){
    
   // try {
        $approveTransactionId = "";
        $password =	$row[0]['email'].$n_wallet_pass_key;
        // get private Key
        $curlLPvt = curl_init();
    
        curl_setopt_array($curlLPvt, array(
        CURLOPT_URL => 'http://195.201.168.34:3000/getpvtkey',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{"address":"'.$walletAddress.'","password":"'.$password.'"}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $responsePvt = curl_exec($curlLPvt);

        curl_close($curlLPvt);
        
        $decodeRespPvt = json_decode($responsePvt,true);

        $getUserPvtKey = $decodeRespPvt["pvtKey"];


        $curl = curl_init();
    
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://127.0.0.1:5000/api/v1/approve_token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{"sender_pvt_key":"'.$getUserPvtKey.'","to_address":"'.$bsc_master_wallet.'","token":"'.$token.'"}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $decodeResp = json_decode($response,true);
		//print_r($decodeResp); die;
        if($decodeResp['success']==true){
            $approveTransactionId = $decodeResp['data'];
        }



    // } catch (Exception $e) {
    //     $send_error_msg = '';
    //     if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
    //         $send_error_msg = '(gas required exceeds allowance)';
    //     } else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
    //         $send_error_msg = '(insufficient funds)';
    //     }

    //     $data_to_sendlog = [];
    //     $data_to_sendlog['send_type'] = 'approve';
    //     $data_to_sendlog['coin_type'] = $token;
    //     $data_to_sendlog['user_id'] = $_SESSION['user_id'];
    //     $data_to_sendlog['to_address'] = $walletAddress;
    //     $data_to_sendlog['msg_type'] = 'error'; // error, permission
    //     $data_to_sendlog['message'] = 'send'.$send_error_msg;
    //     //$db = getDbInstance();
    //     $last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

    //     new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
        
    //     if ( !empty($send_error_msg) ) {
    //         $_SESSION['failure'] = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : "The balance is insufficient.";
    //     } else {
    //         $_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
    //     }
    //     header('Location: ' . $return_page);
    //     exit();
    // }



       
		$data_to_send_logs = [];
		$data_to_send_logs['send_type'] = 'approve';
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['from_id'] = $_SESSION['user_id'];
		//$data_to_send_logs['to_id'] = '';
		$data_to_send_logs['from_address'] = $walletAddress;
		$data_to_send_logs['to_address'] = $bsc_master_wallet;
		$data_to_send_logs['amount'] = 20000000;
		$data_to_send_logs['fee'] =0;
		if ( !empty($approveTransactionId) ) {
			$data_to_send_logs['transactionId'] = $approveTransactionId;
		}
		$data_to_send_logs['status'] = !empty($approveTransactionId) ? 'send' : 'fail';
		$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

		//$db = getDbInstance();
		$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);

		if ( !empty($approveTransactionId) ) {
			$_SESSION['success'] = !empty($langArr['token_kind_errors']) ? $langArr['token_kind_errors'] : 'Tokens will approve in 2 minutes.';
		}
		else {
			$_SESSION['failure'] = !empty($langArr['token_kind_errors']) ? $langArr['token_kind_errors'] : 'Tokens approve Failed.';
		}
		header('location: '.$return_page);
        exit();
	







    
}


	// TP3가 아닌데 키오스크가 받을 경우, 201013
	//if ( $token != 'tp3' ) {
		//$db = getDbInstance();
		$db->where("wallet_address", $filterData['address']);
		$kiosk_count = $db->getValue('kiosk_config', 'count(*)');
		if ( $kiosk_count > 0 ) {
			//$_SESSION['failure'] .= !empty($langArr['send_text5']) ? $langArr['send_text5'] : 'Please use TP3 or eTP3 for store payment.';
			$_SESSION['failure'] .= !empty($langArr['send_text6']) ? $langArr['send_text6'] : 'Please use eTP3 or eMC for store payment.';
			header('location: '.$return_page);
			exit();	
		}
	//}
    //COIN IBT 계정 인 경우 불가능. 2021.12.01 By.OJT
    $db->where("wallet_address", $filterData['address'])->where('account_type2','CoinIBT');
    $coinibtInfo = $db->getValue('admin_accounts', 'count(*)');
    if ( $coinibtInfo > 0 ) {
        $_SESSION['failure'] .= !empty($langArr['commonStringDanger01']) ? $langArr['commonStringDanger01'] : 'COIN IBT 계정으로는 보낼 수 없습니다.';
        header('location: '.$return_page);
        exit();
    }

	if ( isset($filterData['p_token']) && !empty($filterData['p_token']) && isset($filterData['p_kind']) && !empty($filterData['p_kind']) && $filterData['p_token'] != $filterData['p_kind'] ) {
		$_SESSION['failure'] = !empty($langArr['token_kind_error']) ? $langArr['token_kind_error'] : 'Tokens are different.';
		header('Location:'.$return_page);
		exit();
	}
		
	// No transmission for 3 minutes after the last transmission
	// 마지막 전송 시간 구하기
	//$db = getDbInstance();
	$db->where("from_id", $_SESSION['user_id']);
	$db->where("send_type", 'send');
	$db->pageLimit = 1;
	$db->orderBy('id', 'desc');
	$row_last = $db->getOne('user_transactions_all');
	if ( !empty($row_last['id']) ) {
		$last_send_time = $row_last['created_at'];
	}
	if ( !empty($last_send_time) ) {
		$created_time = strtotime($last_send_time);
		$now_time = strtotime("Now");
		if ($now_time - $created_time < $n_send_re_time * 60) { // 3분 (180) : 마지막 전송 후 3분이 되지 않았으면 전송 불가
			$_SESSION['failure'] = !empty($langArr['send_retry_time_message1']) ? $langArr['send_retry_time_message1'] : 'You cannot retransmit for ';
			$_SESSION['failure'] .= $n_send_re_time;
			$_SESSION['failure'] .= !empty($langArr['send_retry_time_message2']) ? $langArr['send_retry_time_message2'] : '	minutes after transmission. Please try again in a few minutes.';
			header('location: '.$return_page);
			exit();	
		}
	}

	$totalAmt = trim($filterData['amount']);

	// (2020-05-25, YMJ)
	if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $totalAmt < $getMinAmountVal) { // 금액이 너무 작음
		$ma_tmp = $getMinAmountVal.' '.strtoupper($token);
		$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
		header('location: '.$return_page);
		exit();
	}			
	

	// monthly transfer limit 
	$resp = $db->rawQueryOne("select sum(amount) as currentMonthSum from user_transactions where sender_id = ? and MONTH(created_at) = MONTH(CURRENT_DATE())
	AND YEAR(created_at) = YEAR(CURRENT_DATE()) and coin_type = ?", Array($_SESSION['user_id'],$token));
	$currentMonthSum = $resp['currentMonthSum'];

	//print_r($resp); die;
	
	$db->where("token_shortcode", ($token=="ctctm" ? "ctc_tm" : $token));
	$tokenDetails = $db->getOne('tokenlist');
	$tokenMonthlyLimit = $tokenDetails['monthly_transfer_limit'];

	if($totalAmt+$currentMonthSum > $tokenMonthlyLimit){
		$_SESSION['failure'] = !empty($langArr['send_max_monhtly_amount']) ? $langArr['send_max_monhtly_amount'] : "Monthly Transfer Limit Exceed ";
		header('location: '.$return_page);
		exit();
	}

	//$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');	

	if($_SESSION['user_id']==$n_master_id){ // 45
		$password =	$n_master_wallet_pass;
		$walletAddress = $row[0]['wallet_address'];
	}else{
		$password =	$row[0]['email'].$n_wallet_pass_key;
		$walletAddress = $row[0]['wallet_address'];
	}

	
	$functionName = "transfer";
	$toAccount = trim($filterData['address']);
	$fromAccount = $walletAddress;
	$amountToSend = trim($filterData['amount']);
	
	//$amountToSend = $amountToSend*$decimalDigit; // 201112
	$amountToSend = bcmul($amountToSend,$decimalDigit);
    $amountToSendApi = $amountToSend;
	$amountToSend = dec2hex($amountToSend);
	$amountToSend = '0x'.$amountToSend; // Must add 0x
	$gas = '0x9088';
	$transactionId = '';

	$db = getDbInstance();
    $db->where("u_address", $toAccount);
    $db->where("user_id", $_SESSION['user_id']);
    $checkValidAddr = $db->get('user_withdrawal_addresses');
    
    // if(empty($checkValidAddr)){
    //     $_SESSION['failure'] = !empty($langArr['token_balance_not_sufficients']) ? $langArr['token_balance_not_sufficients'] : 'Invalid Wallet Address';
	// 	header('location: '.$return_page);
	// 	exit();
    // }

	 // (2020-05-25, YMJ)
	if($getNewCoinBalance < trim($filterData['amount'])){
		$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
		header('location: send_other.php?token='.$token);
		exit();
	}
	/*if($getNewBalance < 0.008){
		$_SESSION['failure'] = !empty($langArr['insufficient_eth_balance']) ? $langArr['insufficient_eth_balance'] : "Insufficient Eth Balance";
		header('location: send_other.php?token='.$token);
		exit();
	}*/
	
	// unlock
	$personal = $web3->personal;
	try {
		$personal->unlockAccount($walletAddress, $password, function ($err, $unlocked) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 4);
			}
		});
	
	} catch (Exception $e) {

		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = 'send';
		$data_to_sendlog['coin_type'] = $token;
		$data_to_sendlog['user_id'] = $_SESSION['user_id'];
		$data_to_sendlog['msg_type'] = 'error'; // error, permission
		$data_to_sendlog['message'] = 'unlock';
		//$db = getDbInstance();
		$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

		new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
		header('Location: ' . $return_page);
		exit();
	}


	$feeTransactionId = "";

	try {
		$feeAmountToSend = $getExchangeFee; // ETH
		$feeAmountToSend = bcmul($feeAmountToSend,1000000000000000000);  // 201112

		$feeAmountToSend = dec2hex($feeAmountToSend);
		$eth->sendTransaction([
			'from' => $fromAccount,
			'to' => $bsc_master_wallet,
			'value' => '0x'.$feeAmountToSend,
			'gasprice'=>$gasPriceInWei
		], function ($err, $result) use (&$feeTransactionId,&$return_page,&$langArr) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 4);
			}
			$feeTransactionId = $result;

		});



	} catch (Exception $e) {
		$send_error_msg = '';
		if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
			$send_error_msg = '(gas required exceeds allowance)';
		} else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
			$send_error_msg = '(insufficient funds)';
		}

		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = 'send';
		$data_to_sendlog['coin_type'] = 'eth';
		$data_to_sendlog['user_id'] = $_SESSION['user_id'];
		$data_to_sendlog['msg_type'] = 'error'; // error, permission
		$data_to_sendlog['message'] = 'send'.$send_error_msg;

		//$db = getDbInstance();
		$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

		new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', eth) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());

		if ( !empty($send_error_msg) ) {
			$_SESSION['failure'] = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : "The balance is insufficient.";
		} else {
			$_SESSION['failure'] = !empty($langArr['send_message244']) ? $langArr['send_message244'] : "Unable to send Token. Try Again44.";
		}
		header('Location: ' . $return_page);
		exit();
	} 

	if(!empty($feeTransactionId)){
		try {
		
            

            // get private Key
            $curlLPvt = curl_init();
		
            curl_setopt_array($curlLPvt, array(
            CURLOPT_URL => 'http://195.201.168.34:3000/getpvtkey',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"address":"'.$fromAccount.'","password":"'.$password.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            ));
    
            $responsePvt = curl_exec($curlLPvt);
    
            curl_close($curlLPvt);
            
            $decodeRespPvt = json_decode($responsePvt,true);

            $getUserPvtKey = $decodeRespPvt["pvtKey"];


            $curl = curl_init();
		
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://127.0.0.1:5000/api/v1/transfer_from_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"sender_pvt_key":"'.$bsc_master_pvt_key.'","from_address":"'.$fromAccount.'","to_address":"'.$toAccount.'","amount":'.$amountToSendApi.',"token":"'.$token.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            ));
    
            $response = curl_exec($curl);
    
            curl_close($curl);

            $decodeResp = json_decode($response,true);
            if($decodeResp['success']==true){
                $transactionId = $decodeResp['data'];
            }





		} catch (Exception $e) {
			$send_error_msg = '';
			if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
				$send_error_msg = '(gas required exceeds allowance)';
			} else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
				$send_error_msg = '(insufficient funds)';
			}

			$data_to_sendlog = [];
			$data_to_sendlog['send_type'] = 'send';
			$data_to_sendlog['coin_type'] = $token;
			$data_to_sendlog['user_id'] = $_SESSION['user_id'];
			$data_to_sendlog['to_address'] = $toAccount;
			$data_to_sendlog['msg_type'] = 'error'; // error, permission
			$data_to_sendlog['message'] = 'send'.$send_error_msg;
			//$db = getDbInstance();
			$last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

			new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', ' . $token . ') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			
			if ( !empty($send_error_msg) ) {
				$_SESSION['failure'] = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : "The balance is insufficient.";
			} else {
				$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
			}
			header('Location: ' . $return_page);
			exit();
		}
		

		// Add log records (2020-05-18, YMJ)
		$data_to_send_logs = [];
		$data_to_send_logs['send_type'] = 'send';
		$data_to_send_logs['coin_type'] = $token;
		$data_to_send_logs['from_id'] = $_SESSION['user_id'];
		//$data_to_send_logs['to_id'] = '';
		$data_to_send_logs['from_address'] = $fromAccount;
		$data_to_send_logs['to_address'] = $toAccount;
		$data_to_send_logs['amount'] = $filterData['amount'];
		$data_to_send_logs['fee'] =0;
		if ( !empty($transactionId) ) {
			$data_to_send_logs['transactionId'] = $transactionId;
		}
		$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
		$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

		//$db = getDbInstance();
		$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);

		if(!empty($transactionId)){
			
			$data_to_store = [];
			$data_to_store['created_at'] = date('Y-m-d H:i:s');
			$data_to_store['coin_type'] = $token;
			$data_to_store['sender_id'] = $_SESSION['user_id'];
			$data_to_store['reciver_address'] = $filterData['address'];
			$data_to_store['amount'] = $filterData['amount'];
			$data_to_store['fee_in_eth'] =0;
			$data_to_store['status'] = 'completed';
			$data_to_store['fee_in_gcg'] = 0;
			$data_to_store['transactionId'] = $transactionId;

			//$db = getDbInstance();
			$last_id = $db->insert('user_transactions', $data_to_store);
			header('location: send_result.php?type=send&txid='.$last_id_sl);
			exit();
			
		} else {
			$_SESSION['failure'] = !empty($langArr['send_message223213213']) ? $langArr['send_message2111111'] : "Unable to send Token. Try Again2."; // (2020-05-22, YMJ)
			header('location: '.$return_page);
			exit();
		}		
	
	// send transactions end	
	}				
	
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

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php';
//한달간 거래 내역 조회
if($token == 'ctctm'){
    require_once 'includes/dbconn.php';
$time = time();
$today = date("Y-m-d",strtotime("now"));
$month_day = date("Y-m-d",strtotime("+1 month", $time));

    $date = new DateTime(date("Y-m-01")); #DateTime 오브젝트변수를 생성
    $f_day= $date->format("Y-m-d") . "\n";
    $date->add(new DateInterval('P1M')); # 생성한 변수에 한달을 더함
    $date->sub(new DateInterval('P1D')); # 변수에 하루를 뺌
    $e_day = $date->format("Y-m-d") . "\n";

$d_day = $today." 00:00:00";
$dend_day = $today." 23:59:59";

//$sql = "select sum(amount) as total from user_transactions_all where coin_type = '$token' and from_id = '$userId' and DATE(created_at) BETWEEN '$d_day' AND '$dend_day'; ";
    $sql = "select sum(amount) as total from user_transactions_all where coin_type = '$token' and from_id = '$userId'";
$query = mysqli_query($conn,$sql);
$row = mysqli_fetch_row($query);
$sum = $row['total'];


//하루 최대치 4400만 넘을 경우 출금 금지
}


?>

<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>
  <script src="https://cdn.jsdelivr.net/npm/dynamsoft-javascript-barcode@7/dist/dbr.min.js" data-productKeys="t0068NQAAAGvSIp5Eop5g1BERYu7svRtf69fVAGjbYlaQllzCcaVvOiAH+CigIESSr0IL62dRFRzKVp3PJSy5JfOOrhtvx/Q="></script>
<!--<div class="loader" style="display:none;"> <img src="images/loader.gif"></div>-->
<div class="loader"  style="display:none;"  id="div-video-container" >
<div class="camera-part" >
       <!-- <video class="dbrScanner-video" width="200" height="200" playsinline="true"></video>-->
	   <video id="video1" class="dbrScanner-video" playsinline="true">
	  </video>
    </div></div>
<div id="page-wrapper">
	<div id="send_other" class="send_common">
	
		<?php include('./includes/flash_messages.php') ?>
		<div class="row">

			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->
					<!-- main content -->
					<div id="main_content" class="panel-body">
						<!-- page heading -->
						<div class="card"> 
							<ul class="index_token_block">
								<li class="token_block">
									<div class="a1">
										<div class="img2"><div><img src="images/logo2/<?php echo $token; ?>.png" alt="<?php echo $token; ?>" /></div></div>
										<span class="text"><?php echo $n_full_name_array[$token]; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewCoinBalance,$n_decimal_point_array[$token]); ?></span><span class="amount_t2"> <?php echo strtoupper($token); ?></span></span>
									</div>
								</li>
							</ul>

							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">

								<form class="form" action="<?php echo ($allowCoinCnt>0) ? 'set_transferpw_frm_send.php' : 'send_bsc_other.php' ?>?token=<?php echo $token; ?>" method="post"  id="<?php echo ($allowCoinCnt>0) ? 'customer_forms' : 'customer_forms' ?>" enctype="multipart/form-data">
									<input type="hidden" name="token" id="n_token" value="<?php echo $token; ?>" />
									<input type="hidden" name="lang" id="n_lang" value="<?php echo $_SESSION['lang']; ?>" />
									<input type="hidden" name="kind" id="kind" value="" />
									<input type="hidden" name="payment_no" id="payment_no" value="" />

									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['send_text1']) ? $langArr['send_text1'] : "Address"; ?></span>
											<a href="withdrawal_addresses_tm.php"><img src="images/icons/plus.png" id="qrimgs" alt="barcode" class="barcode_img" /></a>
											<div id="to_name">
												<img src="images/icons/send_name_chk_t.png" alt="success" />
												<span id="receiver_addr_name"></span>
											</div>
											<div id="to_message">
												<img src="images/icons/send_name_chk_f.png" alt="fail" />
												<span id="receiver_message"></span>
											</div>
										</label>
										<!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class="form-control"></textarea>-->
										<div class="barcode_img_area">
										<!--	<select style="background:#fff;" required id="receiver_addr" name="address" class="form-control">
                                                <option value="">Please Select</option>
												<?php /*if(!empty($addressList)) { foreach($addressList as $addressSingle){ */?>
												<option value='<?php /*echo $addressSingle['u_address']  */?>'><?php /*echo $addressSingle['u_address']  */?></option>
												<?php /*} } */?>
											</select>-->
											<!--<input type=text required title="<?php //echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="" placeholder="<?php //echo !empty($langArr['send_explain1']) ? $langArr['send_explain1'] : 'Please paste your wallet address or take a barcode.'; ?>"><img src="images/icons/send_barcode.png" id="qrimg" class="barcode_img" />-->
                                            <input type=text <?php echo ($allowCoinCnt>0) ? "required" : ""; ?> title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="" placeholder="<?php echo !empty($langArr['send_explain1']) ? $langArr['send_explain1'] : 'Please paste your wallet address or take a barcode.'; ?>"><img src="images/icons/send_barcode.png" id="qrimg" alt="barcode" class="barcode_img" />

										</div>

									</div>
									<div class="clearfix"></div>
									<input type="hidden" name="get_name_result" id="get_name_result" value="0" />
									<?php
										// Add (2020.05.18, YMJ)
										// get_name_result : 받는이가 회원인 경우 1, 회원이 아니면 0
									?>

									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php echo $getExchangeFee; ?> <?php echo $langArr['minimum_limit_is_eth_2']; ?></span>
										</label>
                                        <?php
                                        if($_GET['token'] == 'ctctm'){
                                        ?>
                                        <!--월별 전송량을 체크한다-->
                                        <input type="hidden" value="<?php echo $sum;?>" id="total_sum">
										<input autocomplete="off"  <?php echo ($allowCoinCnt>0) ? "required" : ""; ?> title="<?php echo $langArr['this_field_is_required']; ?>" onkeyup="test()" oninput='handleOnInput(this, 5)' id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="number" value="0">
                                            <p>One revolution feed is 1000 CTC(TM).</p>
                                            <script>
                                                function test(){
                                                    var amount = $("#amount").val();
                                                    var total_sum = $("#total_sum").val();

                                                    /*var total = $("#total_sum").val();
                                                    if(total >= 10001){
                                                        alert("이번달 전송량이 초과되었습니다.");
                                                        return;
                                                    }

                                                     /*
                                                     else if(amount > 1800){
                                                         alert("1800개 까지 전송이 가능합니다.");
                                                         $("#amount").val();
                                                         $("#amount").val(1800);
                                                         return;
                                                     }
                                                      */

                                                    if(amount >= 1001){
                                                        alert("1000개 까지 전송이 가능합니다.");
                                                        $("#amount").val(1000);
                                                        return;
                                                    }

                                                    if(total_sum >= 1000){
                                                        alert("1000개 까지 전송 가능합니다.")
                                                        return;;

                                                    }

                                                }
                                            </script>
                                        <?php }else{ ?>
                                        <input autocomplete="off"  required title="<?php echo $langArr['this_field_is_required']; ?>" oninput='handleOnInput(this, 6)' id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="number">
                                        <?php } ?>
									</div>
									<div class="clearfix"></div>

									<div id="show_msg" class="alert alert-info alert-dismissable"></div>
									<div class="clearfix"></div>
                                    <?php
                                    if($token == 'ctctm'){
                                        if($sum >= 1001){
                                    ?>
                                    
                                            <div class="col-md-12 btn_area">
                                                <input name="submit" class="btn" id="confirm_modals" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit" onclick="end_coin();" />
                                                <script>
                                                    function end_coin(){
                                                        alert("전송량이 초과하였습니다.");
                                                        return;

                                                    }
                                                </script>
                                            </div>
                                            <?php } else { ?>
                                        <div class="col-md-12 btn_area">
                                            <?php if($allowCoinCnt>0) { ?>
                                            <input name="submit" class="btn" id="confirm_modals" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit" />
                                            <?php } else { ?>
                                                <input name="submit" class="btn" id="confirm_modals" value="<?php echo !empty($langArr['approveToken']) ? $langArr['approveToken'] : "Approve Token"; ?>" type="submit" />

                                                <?php } ?> 
                                        </div>
                                        <?php } ?>
                                    <?php
                                    } else {
                                    ?>
                                    <div class="col-md-12 btn_area">
                                        <input name="submit" class="btn" id="confirm_modals" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit" />
                                    </div>
									<?php } ?>
								</form>
							</div>
						</div>
					</div>

					<div class="modal fade" id="confirm_modal_box" role="dialog">
						<div class="modal-dialog confirm_modal_box1">
							<form action="set_transferpw_frm_send.php" method="POST">
								<input type="hidden" name="token" value="<?php echo $token; ?>" />
								<input type="hidden" name="amount" id="m_amount" value="" />
								<input type="hidden" name="address" id="m_receiver_addr" value="" />
								<input type="hidden" name="kind" id="m_kind" value="" />
								<input type="hidden" name="payment_no" id="m_payment_no" value="" />

								<!-- Modal content-->
								<div class="modal-content">
									<div class="modal-body">
										<p id="confirm_message"></p>
									</div>
									<div class="modal-footer">
										<button type="submit" class="btn_left"><?php echo !empty($langArr['confirm_btn_yes']) ? $langArr['confirm_btn_yes'] : "Yes"; ?> </button>
										<button type="button" class="" id="closeModalBtn"><?php echo !empty($langArr['confirm_btn_no']) ? $langArr['confirm_btn_no'] : "No"; ?> </button>
									</div>
								</div>
							</form>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<style>
/* send : modal box (confirm) */
.confirm_modal_box1 {
	top: 150px;
}
.confirm_modal_box1 #confirm_message {
	font-size: 1.2rem;
}
.confirm_modal_box1 .modal-footer {
	background-color: #F2F2F2;
}
.confirm_modal_box1 .modal-footer button {
	font-size: 1.2rem;
}
.confirm_modal_box1 .modal-footer button:nth-child(1) {
	margin-right: 15px;
}
.send_common #show_msg {
	margin: 15px 15px 0 15px;
	display: none;
}
.confirm_modal_box1 .modal-content {
	-ms-overflow-style: none;
	scrollbar-width: none;
}
.confirm_modal_box1 .modal-content::-webkit-scrollbar {
	display: none;
}
</style>

<script type="text/javascript">

function openQRCamera(node) {
}
function check_amount(){

    var amount = $("#amount").val();
    //alert(amount);

   /* if(amount <= 9999){
        alert("10000개 이하는 전송이 불가능합니다.");
        $("#amount").val(10000);
        return;
    }*/

    handleOnInput(this, 6);
}
function handleOnInput(el, maxlength) {
    if(el.value.length > maxlength)  {
        el.value = el.value.substr(0, maxlength);
    }
}

$(document).ready(function(){
	//pa_init();

	var target_id = "#qrimg"
	//if (navigator.userAgent == "android-web-view"){
	//if (navigator.userAgent.indexOf("android-web-view2") > - 1){
	if (navigator.userAgent.indexOf("android-web-view2") > - 1 || navigator.userAgent.indexOf("android-web-view3") > - 1 ){
		$(target_id).hide();
	} else if (navigator.userAgent.indexOf("android-web-view") > - 1){
		target_id = "#qrnull";
		var element = document.getElementById('qrimg');
		var href_el = document.createElement('a');
		href_el.href = 'activity://scanner_activity';
		element.parentNode.insertBefore(href_el, element);
		href_el.appendChild(element);
	} else if (navigator.userAgent.indexOf("ios-web-view") > - 1){
        $(target_id).hide();
    }

	$(target_id).click(function(){
		$(".loader").show();
		let scanner = null;
        Dynamsoft.BarcodeScanner.createInstance({
			UIElement: document.getElementById('div-video-container'),
			onFrameRead: function(results) { console.log(results);},
            onUnduplicatedRead: function(txt, result) {  $("#receiver_addr").val(txt);  $(".loader").hide(); scanner.hide(); addr_check();}
        }).then(function(s) {
            scanner = s;
			$("#div-video-container").click(function(){
				scanner.hide();
			});
			// Use back camera in mobile. Set width and height.
			// Refer [MediaStreamConstraints](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia#Syntax).
			//scanner.setVideoSettings({ video: { width: 200, height: 220, facingMode: "environment" } });

			let runtimeSettings = scanner.getRuntimeSettings();
			// Only decode OneD and QR
			runtimeSettings.BarcodeFormatIds = Dynamsoft.EnumBarcodeFormat.OneD | Dynamsoft.EnumBarcodeFormat.QR_CODE;
			// The default setting is for an environment with accurate focus and good lighting. The settings below are for more complex environments.
			runtimeSettings.localizationModes = [2,16,4,8,0,0,0,0];
			// Only accept results' confidence over 30
			runtimeSettings.minResultConfidence = 30;
			scanner.updateRuntimeSettings(runtimeSettings);

			let scanSettings = scanner.getScanSettings();
			// The same code awlways alert? Set duplicateForgetTime longer.
			scanSettings.duplicateForgetTime = 20000;
			// Give cpu more time to relax
			scanSettings.intervalTime = 300;
			scanner.setScanSettings(scanSettings);
            scanner.show().catch(function(ex){
                console.log(ex);
				 alert(ex.message || ex);
				scanner.hide();
            });
        });
		
		//$('#qrfield').trigger('click'); 
	})

	// Add (2020-05-18, YMJ)
	// It can only be sent to members.
	$("#receiver_addr").on('propertychange change keyup paste input', function(){
		addr_check();
	});


	//$("#confirm_modal").on('click', function(){
	$("#customer_form").on('submit', function(){
		var get_name_result = $("#get_name_result").val();
		var amount = $("#amount").val();

        <?php
        if($_GET['token'] == 'CTCTM'){
        ?>
        if(amount < 0) {
            //alert("10000개 미만은 전송이 불가능합니다.");
            //$("#amount").val(10000);
            return;
        } else if(amount >= 1801){
            alert("20000개 초과 전송이 불가능합니다.");
            $("#amount").val(1800);
            return;
        }
         <?php }?>

        //CTC TM 전용

        console.log(amount);
        if(amount >= 1801){
            alert('1회 전송량을 초과하였습니다.');
            return false;
        }


		$("#show_msg").html('').hide();
		if (get_name_result == '0' || !amount) {
			return false;
		} else {
			var msg = send_before_msg_confirm();
			$("#m_amount").val($("#amount").val());
			$("#m_receiver_addr").val($("#receiver_addr").val());
			$("#m_kind").val($("#kind").val());
			$("#m_payment_no").val($("#payment_no").val());
			if ( $("#m_amount").val() == '' || $("#m_receiver_addr").val() == '' ) {
				$("#show_msg").html("<?php echo !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred.'; ?>").show();
				return false;
			} else {
				$("#confirm_message").html(msg);
				$("#confirm_modal_box").modal('show');
				return false;
			}

		}
	});
	$("#closeModalBtn").on('click', function(){
		$("#confirm_modal_box").modal('hide');
	});

});


/**
 * Checks if the given string is an address
 *
 * @method isAddress
 * @param {String} address the given HEX adress
 * @return {Boolean}
*/  
  
    var isAddress = function (address) {
		if (!/^(0x)?[0-9a-f]{40}$/i.test(address)) {
			// check if it has the basic requirements of an address
			return false;
		//} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address)) {
		} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address) || /^(0x)?[0-9a-fA-F]{40}$/.test(address)) {
			// If it's all small caps or all all caps, return true
			return true;
		} else {
			// Otherwise check each case
			return isChecksumAddress(address);
		}
};

/**
 * Checks if the given string is a checksummed address
 *
 * @method isChecksumAddress
 * @param {String} address the given HEX adress
 * @return {Boolean}
*/
var isChecksumAddress = function (address) {
	// Check each case
	address = address.replace('0x','');
	var addressHash = sha3(address.toLowerCase());
	for (var i = 0; i < 40; i++ ) {
		// the nth letter should be uppercase if the nth digit of casemap is 1
		if ((parseInt(addressHash[i], 16) > 7 && address[i].toUpperCase() !== address[i]) || (parseInt(addressHash[i], 16) <= 7 && address[i].toLowerCase() !== address[i])) {
			return false;
		}
	}
	return true;
};

function addr_check(){
	var addr = $("#receiver_addr").val();
	var addr_length = addr.length;
	
	if( addr_length < 42){
		$("#to_name").removeClass('to_name');
		$("#receiver_addr_name").html('');
		$("#to_message").addClass('to_name');
		$("#receiver_message").html("<?php echo !empty($langArr['invalid_wallet_address']) ? $langArr['invalid_wallet_address'] : 'Invalid Wallet Address'; ?>");
		$("#get_name_result").val('0');
	} else {
		var get = isAddress(addr);
		if (get == false) {
			$("#to_name").removeClass('to_name');
			$("#receiver_addr_name").html('');
			$("#to_message").addClass('to_name');
			$("#receiver_message").html("<?php echo !empty($langArr['invalid_wallet_address']) ? $langArr['invalid_wallet_address'] : 'Invalid Wallet Address'; ?>");
			$("#get_name_result").val('0');
		} else {
			$("#to_message").removeClass('to_name');
			
			$.ajax({
				url : 'send.pro.php',
				type : 'POST',
				//data : {mode: 'get_name', waddr : addr},
                data : {mode: 'wallet_check', waddr : addr},
				dataType : 'json',
				success : function(resp){
					if (resp != '') {
						/*$("#to_name").addClass('to_name');
						$("#receiver_addr_name").html(resp);
						$("#to_message").removeClass('to_name');
						$("#receiver_message").html("");
						$("#get_name_result").val('1');
						*/	
						if ( resp == 'coinibt_false' ) {
							$("#to_name").removeClass('to_name');
							$("#receiver_addr_name").html('');
							$("#to_message").addClass('to_name');
							$("#receiver_message").html("<?php echo !empty($langArr['send_member_msg2']) ? $langArr['send_member_msg2'] : 'You can only send to your own exchange address.'; ?>");
							$("#get_name_result").val('0');
						} else {
							$("#to_name").addClass('to_name');
							$("#receiver_addr_name").html(resp);
							$("#to_message").removeClass('to_name');
							$("#receiver_message").html("");
							$("#get_name_result").val('1');
						}
					} else {
						$("#to_name").removeClass('to_name');
						$("#receiver_addr_name").html('');
						$("#to_message").addClass('to_name');
						$("#receiver_message").html("<?php echo !empty($langArr['send_member_msg1']) ? $langArr['send_member_msg1'] : 'It can only be sent to members.'; ?>");
						$("#get_name_result").val('0');
					}
				},
				error : function(resp){
					$("#to_name").removeClass('to_name');
					$("#receiver_addr_name").html('');
					$("#to_message").addClass('to_name');
					$("#receiver_message").html("<?php echo !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred'; ?>");
					$("#get_name_result").val('0');
				}
			});
		}
	}
}

// Add : Check the recipient and amount before sending
function send_before_msg_confirm() {
	var to_name = $("#receiver_addr_name").html();
	var amount = $("#amount").val();
	var token = $("#n_token").val().toUpperCase();
	var lang = $("#n_lang").val();
	var msg_c1 = "<?php echo !empty($langArr['send_confirm_message1']) ? $langArr['send_confirm_message1'] : ' to '; ?>";
	var msg_c2 = "<?php echo !empty($langArr['send_confirm_message2']) ? $langArr['send_confirm_message2'] : 'Would you like to send '; ?>";
	if ( lang == 'en') {
		var msg = msg_c2 + amount + ' ' + token + msg_c1 + to_name + '?';
	} else {
		var msg = to_name + msg_c1 + amount + ' ' + token + msg_c2 + '?';
	}
	return msg;
}
</script>


<style>
    /*the container must be positioned relative:*/
    .autocomplete {
        position: relative;
        display: inline-block;
    }

    input {
        border: 1px solid transparent;
        background-color: #f1f1f1;
        padding: 10px;
        font-size: 16px;
    }

    input[type=text] {
        background-color: #f1f1f1;
        width: 100%;
    }

    input[type=submit] {
        background-color: DodgerBlue;
        color: #fff;
        cursor: pointer;
    }

    .autocomplete-items {
        position: absolute;
        border: 1px solid #d4d4d4;
        border-bottom: none;
        border-top: none;
        z-index: 99;
        /*position the autocomplete items to be the same width as the container:*/
        top: 100%;
        left: 0;
        right: 0;
    }

    .autocomplete-items div {
        padding: 10px;
        cursor: pointer;
        background-color: #fff;
        border-bottom: 1px solid #d4d4d4;
    }

    /*when hovering an item:*/
    .autocomplete-items div:hover {
        background-color: #e9e9e9;
    }

    /*when navigating through the items using the arrow keys:*/
    .autocomplete-active {
        background-color: DodgerBlue !important;
        color: #ffffff;
    }
</style>
<script>
    function autocomplete(inp, arr) {
        /*the autocomplete function takes two arguments,
        the text field element and an array of possible autocompleted values:*/
        var currentFocus;
        /*execute a function when someone writes in the text field:*/
        inp.addEventListener("input", function(e) {
            var a, b, i, val = this.value;
            /*close any already open lists of autocompleted values*/
            closeAllLists();
            //if (!val) { return false;}
            currentFocus = -1;
            /*create a DIV element that will contain the items (values):*/
            a = document.createElement("DIV");
            a.setAttribute("id", this.id + "autocomplete-list");
            a.setAttribute("class", "autocomplete-items");
            /*append the DIV element as a child of the autocomplete container:*/
            this.parentNode.appendChild(a);
            /*for each item in the array...*/
            for (i = 0; i < arr.length; i++) {
                /*check if the item starts with the same letters as the text field value:*/
                if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
                    /*create a DIV element for each matching element:*/
                    b = document.createElement("DIV");
                    /*make the matching letters bold:*/
                    b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                    b.innerHTML += arr[i].substr(val.length);
                    /*insert a input field that will hold the current array item's value:*/
                    b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                    /*execute a function when someone clicks on the item value (DIV element):*/
                    b.addEventListener("click", function(e) {
                        /*insert the value for the autocomplete text field:*/
                        inp.value = this.getElementsByTagName("input")[0].value;
                        /*close the list of autocompleted values,
                        (or any other open lists of autocompleted values:*/
                        closeAllLists();
                        addr_check();
                    });
                    a.appendChild(b);
                }
            }
        });
        /*execute a function presses a key on the keyboard:*/
        inp.addEventListener("keydown", function(e) {
            var x = document.getElementById(this.id + "autocomplete-list");
            if (x) x = x.getElementsByTagName("div");
            if (e.keyCode == 40) {
                /*If the arrow DOWN key is pressed,
                increase the currentFocus variable:*/
                currentFocus++;
                /*and and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 38) { //up
                /*If the arrow UP key is pressed,
                decrease the currentFocus variable:*/
                currentFocus--;
                /*and and make the current item more visible:*/
                addActive(x);
            } else if (e.keyCode == 13) {
                /*If the ENTER key is pressed, prevent the form from being submitted,*/
                e.preventDefault();
                if (currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x) x[currentFocus].click();
                }
            }
        });
        function addActive(x) {
            /*a function to classify an item as "active":*/
            if (!x) return false;
            /*start by removing the "active" class on all items:*/
            removeActive(x);
            if (currentFocus >= x.length) currentFocus = 0;
            if (currentFocus < 0) currentFocus = (x.length - 1);
            /*add class "autocomplete-active":*/
            x[currentFocus].classList.add("autocomplete-active");
        }
        function removeActive(x) {
            /*a function to remove the "active" class from all autocomplete items:*/
            for (var i = 0; i < x.length; i++) {
                x[i].classList.remove("autocomplete-active");
            }
        }
        function closeAllLists(elmnt) {
            /*close all autocomplete lists in the document,
            except the one passed as an argument:*/
            var x = document.getElementsByClassName("autocomplete-items");
            for (var i = 0; i < x.length; i++) {
                if (elmnt != x[i] && elmnt != inp) {
                    x[i].parentNode.removeChild(x[i]);
                }
            }
        }
        /*execute a function when someone clicks in the document:*/
        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });
    }

    /*An array containing all the country names in the world:*/
    var countries = [];
    <?php if(!empty($addressList)) { foreach($addressList as $addressSingle){ ?>
    countries.push('<?php echo $addressSingle['u_address']  ?>');
    <?php } } ?>

    /*initiate the autocomplete function on the "myInput" element, and pass along the countries array as possible autocomplete values:*/
    autocomplete(document.getElementById("receiver_addr"), countries);
</script>
<?php include_once 'includes/footer.php'; ?>
