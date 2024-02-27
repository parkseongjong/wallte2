<?php 
// Page in use
// eToken -> Token
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

use wallet\common\Log as walletLog;
use wallet\common\Info as walletInfo;
use wallet\common\InfoWeb3 as walletInfoWeb3;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

$filter = walletFilter::getInstance();

//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'amount' => 'string',
    'real_token_amount' => 'string',
);

$filterData = $filter->postDataFilter($_POST,$targetPostData);
$filterDataGet = $filter->postDataFilter($_GET,['token'=>'string']);
unset($targetPostData);

//use Web3\Web3;
//use Web3\Contract;

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('e-pay -> token 변환 조회',['target_id'=>0,'action'=>'S']);

//require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new walletInfo();

$web3Instance = new walletInfoWeb3();
//$web3outter = $web3Instance->outterInit();
//$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
    //$web3 = $web3Instance->innerTempInit();
$web3 = $web3Instance->innerInit();
$eth = $web3->eth;

//$gasPriceInWei = 40000000000;
//$gasPriceInWei = 4000000000000;
//$web3outter->eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);

$token = strtolower($filterDataGet['token']);

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];



$return_page = 'exchange_etoken_re.php?token='.$token;

$getBalance = 0;
$getNewCoinBalance = 0;

if ( $token != 'ectc' ) {
	$getBalance = $row[0]['etoken_'.$token];
}
$getNewCoinBalance = $row[0]['etoken_ectc'];

switch($token) {
	case 'ectc':
		$adminAddress = $n_master_ectc_re_wallet_address;
		$adminPass = $n_master_ectc_re_pass;
		$adminId = $n_master_ectc_re_id;
		$new_token = 'ctc';
		$module_name = 'exchange_ectc_per_ctc';
		break;
	case 'etp3':
		$adminAddress = $n_master_etp3_re_wallet_address;
		$adminPass = $n_master_etp3_re_pass;
		$adminId = $n_master_etp3_re_id;
		$new_token = 'tp3';
		$module_name = 'exchange_etp3_per_tp3';
		break;
	case 'emc':
		$adminAddress = $n_master_emc_re_wallet_address;
		$adminPass = $n_master_emc_re_pass;
		$adminId = $n_master_emc_re_id;
		$new_token = 'mc';
		$module_name = 'exchange_emc_per_mc';
		break;
	case 'ekrw':
		$_SESSION['failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.';
		header('location: index.php');
		exit();
		break;
	case 'eusdt':
		$adminAddress = $n_master_eusdt_re_wallet_address;
		$adminPass = $n_master_eusdt_re_pass;
		$adminId = $n_master_eusdt_re_id;
		$new_token = 'usdt';
		$module_name = 'exchange_eusdt_per_usdt';
		break;
	case 'eeth': // �߰�, 210519
		$adminAddress = $n_master_eeth_re_wallet_address;
		$adminPass = $n_master_eeth_re_pass;
		$adminId = $n_master_eeth_re_id;
		$new_token = 'eth';
		$module_name = 'exchange_eeth_per_eth';
		break;
}

// 201126
$module_name_fee = 'send_etoken_fee2';
if ( $row[0]['transfer_fee_type'] == 'H' ) {
	$module_name_fee = 'send_etoken_fee2_h';
}
$getTokenFee = $db->where("module_name", $module_name_fee)->getOne('settings');
$getTokenFeeVal = $getTokenFee['value'];


$getExchangeRate = $db->where("module_name", $module_name)->getOne('settings');
$getExchangeRateVal = $getExchangeRate['value'];


// �ּ� ���۱ݾ�
$getMinAmountVal = 0;
$getMinAmount = $db->where("module_name", 'min_transfer_'.$token.'_to_'.$new_token)->getOne('settings');
$getMinAmountVal = $getMinAmount['value'];

$getExchangeFeeSetting = $db->where("module_name", 'exchange_fee_in_eth')->getOne('settings');
$getExchangeFee = $getExchangeFeeSetting['value'];


///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

	// ����� �ܾ� Ȯ��

	// ������ �ܾ� Ȯ��
	// ������ unlock
	// send
	// log save
    $log->info('epay -> token 변환 처리('.$new_token.')',['target_id'=>0,'action'=>'E']);
	$totalAmt = trim($filterData['amount']);

	if ( !is_numeric($totalAmt) ) { // ���ڰ� �ƴ� ���
		$_SESSION['failure'] = !empty($langArr['input_invalid_value']) ? $langArr['input_invalid_value'] : 'Please enter a valid value.';
		header('Location:'.$return_page);
		exit();
	}

	// �ּ� ���۱ݾ� üũ
	if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $totalAmt < $getMinAmountVal) { 
		$ma_tmp = $getMinAmountVal.' '.$n_epay_name_array[$token];
		$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
		header('location: '.$return_page);
		exit();
	}
	
	if ( $token == 'ectc' ) {
		if ( $getNewCoinBalance < $totalAmt + $getTokenFeeVal ) { // �ܾ�+������ ����
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: ' . $return_page);
			exit();
		}
	} else {
		if ( $getNewCoinBalance < $getTokenFeeVal ) { // ������ ����
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: ' . $return_page);
			exit();
		}
		if ( $getBalance < $totalAmt ) { // �ܾ� ����
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: ' . $return_page);
			exit();
		}
	}
	
	// ������ �ܾ� Ȯ��
	
	$M_getBalance = 0;
	$M_getEthBalance = $wi_wallet_infos->wi_get_balance('2', 'eth', $adminAddress, $contractAddressArr);
	$M_getBalance = $wi_wallet_infos->wi_get_balance('2', $new_token, $adminAddress, $contractAddressArr);
	$token_amount = $filterData['real_token_amount'];
	
	//$token_amount = $totalAmt/$getExchangeRateVal; // Master�� User���� ��������� �� ��
	
	if ( $M_getBalance < $token_amount ) {
		$_SESSION['failure'] = !empty($langArr['exchange_message6']) ? $langArr['exchange_message6'] : 'Insufficient Balance in Admin Account.';
		header('Location: ' . $return_page);
		exit();
	}

	


	



	$tokenArr = $contractAddressArr[$new_token];
	$tokenAbi = $tokenArr['abi'];
	$tokenContractAddress = $tokenArr['contractAddress'];
	$decimalDigit = $tokenArr['decimal'];

	//$amountToSend = $token_amount*$decimalDigit;
	$amountToSend = bcmul($token_amount, $decimalDigit); // 201112

	$amountToSend = dec2hex($amountToSend);
	$amountToSend = '0x'.$amountToSend; // Must add 0x

	$fromAccount = $adminAddress;
	$toAccount = $walletAddress;


	$feeTransactionId = "";
	// ������ unlock
	try {
		$passwordUser =	$row[0]['email'].$n_wallet_pass_key;
		$personal = $web3->personal;
		$personal->unlockAccount($walletAddress, $passwordUser, function ($err, $unlocked) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 3);
			}
		});

	} catch (Exception $e) {

		$last_id_dts = new_set_send_err_log ('send', $new_token, $_SESSION['user_id'], '', 'error', 'admin_unlock');

		new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', '.$new_token.') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
		//return;
		header('Location: ' . $return_page);
		exit();
	}

	try {
		

		$feeAmountToSend = $getExchangeFee; // ETH
		$feeAmountToSend = bcmul($feeAmountToSend,1000000000000000000);  // 201112

		$feeAmountToSend = dec2hex($feeAmountToSend);
		$eth->sendTransaction([
			'from' => $walletAddress,
			'to' => $adminAddress,
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
			$_SESSION['failure'] = !empty($langArr['send_message22122']) ? $langArr['send_message22313'] : "Unable to send Token. Try Again1.";
		}
		header('Location: ' . $return_page);
		exit();
	} 










	if(!empty($feeTransactionId)){

		// ������ unlock
		try {
			$personal = $web3->personal;
			$personal->unlockAccount($adminAddress, $adminPass, function ($err, $unlocked) {
				if ($err !== null) {
					throw new Exception($err->getMessage(), 3);
				}
			});

		} catch (Exception $e) {

			$last_id_dts = new_set_send_err_log ('send', $new_token, $_SESSION['user_id'], '', 'error', 'admin_unlock');

			new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', '.$new_token.') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
			//return;
			header('Location: ' . $return_page);
			exit();
		}

		$transactionId = '';
		
		// send
		try {
			if ( $new_token == 'ctc' ) {
				//$contract = new Contract($web3->provider, $testAbi);
				$contract = $web3Instance->innerContract($web3->provider, $testAbi);
				$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
					'from' => $fromAccount,
					'gas' => '0x186A0',   //100000
					'gasprice'=>$gasPriceInWei
				], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 4);
					}
					$transactionId = $result;
				});
			} else if ( $new_token == 'eth' ) { // �߰�, 210519
				$eth->sendTransaction([
					'from' => $fromAccount,
					'to' => $toAccount,
					'value' => $amountToSend,
					'gasprice'=>$gasPriceInWei
				], function ($err, $result) use (&$transactionId) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 4);
					}
					$transactionId = $result;
				});
			} else {
				//$otherTokenContract = new Contract($web3->provider, $tokenAbi);
				$otherTokenContract = $web3Instance->innerContract($web3->provider, $tokenAbi);
				$otherTokenContract->at($tokenContractAddress)->send('transfer', $toAccount, $amountToSend, [
					'from' => $fromAccount,
					'gas' => '0x186A0',   //100000
					'gasprice'=>$gasPriceInWei
				], function ($err, $result) use ( $fromAccount, $toAccount,&$transactionId) {
					if ($err !== null) {
						throw new Exception($err->getMessage(), 4);
					}
					$transactionId = $result;
				});
			}
		
		} catch (Exception $e) {
			$send_error_msg = '';
			if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
				$send_error_msg = '(gas required exceeds allowance)';
			} else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
				$send_error_msg = '(insufficient funds)';
			}

			$last_id_dts = new_set_send_err_log ('send', $new_token, $_SESSION['user_id'], '', 'error', 'send'.$send_error_msg);

			new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', '.$new_token.') ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());

			if ( !empty($send_error_msg) ) {
				$_SESSION['failure'] = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : "The balance is insufficient.";
			} else {
				$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
			}
			header('Location: ' . $return_page);
			exit();
		}
		
		// Add log records
		$status = !empty($transactionId) ? 'send' : 'fail';
		$last_id_sl = new_set_user_transactions_all ('exchange_eToken', $new_token, $adminId, $_SESSION['user_id'], $fromAccount, $toAccount, $token_amount, 0, $transactionId, $status, '', '', '', '');
		
		if(!empty($transactionId)) {
			
			$last_id = new_set_user_transactions ($new_token, $adminId, $toAccount, $token_amount, 0, 0, 'completed', $transactionId);
			
			//db���� ����Ʈ ����

			$db = getDbInstance();
			$db->where("id", $_SESSION['user_id']);
			$updateArr = [];
			if ( $token == 'ectc' ) {
				$tmp = $totalAmt + $getTokenFeeVal;
				$updateArr['etoken_'.$token] = $db->dec($tmp);
			} else{
				$updateArr['etoken_ectc'] = $db->dec($getTokenFeeVal);
				$updateArr['etoken_'.$token] = $db->dec($totalAmt);
			}
			$last_id1 = $db->update('admin_accounts', $updateArr);
			
			if ( $last_id1 ) {
				// ���� out
				$data_to_log = [];
				$data_to_log['user_id'] = $_SESSION['user_id'];
				$data_to_log['wallet_address'] = $walletAddress;
				$data_to_log['coin_type'] = $token;
				$data_to_log['points'] = '-'.$totalAmt;
				$data_to_log['in_out'] = 'out';
				$data_to_log['send_type'] = 'to_token';
				$data_to_log['send_user_id'] = $adminId;
				$data_to_log['send_wallet_address'] = $adminAddress;
				$data_to_log['send_fee'] = $getTokenFeeVal;
				$data_to_log['user_transactions_all_id'] = $last_id_sl;
				$data_to_log['created_at'] = date("Y-m-d H:i:s");
				$db = getDbInstance();
				$last_id_sl1 = $db->insert('etoken_logs', $data_to_log);
				
				// ������ out
				$data_to_log = [];
				$data_to_log['user_id'] = $_SESSION['user_id'];
				$data_to_log['wallet_address'] = $walletAddress;
				$data_to_log['coin_type'] = 'ectc';
				$data_to_log['points'] = '-'.$getTokenFeeVal;
				$data_to_log['in_out'] = 'out';
				$data_to_log['send_type'] = 'to_token';
				$data_to_log['send_user_id'] = $n_master_etoken_ctc_fee_id;
				$data_to_log['send_wallet_address'] = $n_master_etoken_ctc_fee_wallet_address;
				$data_to_log['send_fee'] = '0';
				$data_to_log['user_transactions_all_id'] = $last_id_sl;
				$data_to_log['created_at'] = date("Y-m-d H:i:s");
				$db = getDbInstance();
				$last_id_sl2 = $db->insert('etoken_logs', $data_to_log);
			}


				
			// ���� in
			$db = getDbInstance();
			$db->where("id", $adminId);
			$updateArr = [];
			$updateArr['etoken_'.$token] = $db->inc($totalAmt);
			$last_id3 = $db->update('admin_accounts', $updateArr);

			if ( $last_id3 ) {
				$data_to_log = [];
				$data_to_log['user_id'] = $adminId;
				$data_to_log['wallet_address'] = $adminAddress;
				$data_to_log['coin_type'] = $token;
				$data_to_log['points'] = $totalAmt;
				$data_to_log['in_out'] = 'in';
				$data_to_log['send_type'] = 'to_token';
				$data_to_log['send_user_id'] = $_SESSION['user_id'];
				$data_to_log['send_wallet_address'] = $walletAddress;
				$data_to_log['send_fee'] = '0';
				$data_to_log['user_transactions_all_id'] = $last_id_sl;
				$data_to_log['created_at'] = date("Y-m-d H:i:s");
				$db = getDbInstance();
				$last_id_sl3 = $db->insert('etoken_logs', $data_to_log);
			}

			// ������ ������ in
			$db = getDbInstance();
			$db->where("id", $n_master_etoken_ctc_fee_id);
			$updateArr = [];
			$updateArr['etoken_ectc'] = $db->inc($getTokenFeeVal);
			$last_id4 = $db->update('admin_accounts', $updateArr);

			if ( $last_id4 ) {
				$data_to_log = [];
				$data_to_log['user_id'] = $n_master_etoken_ctc_fee_id;
				$data_to_log['wallet_address'] = $n_master_etoken_ctc_fee_wallet_address;
				$data_to_log['coin_type'] = 'ectc';
				$data_to_log['points'] = $getTokenFeeVal;
				$data_to_log['in_out'] = 'in';
				$data_to_log['send_type'] = 'to_token';
				$data_to_log['send_user_id'] = $_SESSION['user_id'];
				$data_to_log['send_wallet_address'] = $walletAddress;
				$data_to_log['send_fee'] = '0';
				$data_to_log['user_transactions_all_id'] = $last_id_sl;
				$data_to_log['created_at'] = date("Y-m-d H:i:s");
				$db = getDbInstance();
				$last_id_sl4 = $db->insert('etoken_logs', $data_to_log);
			}
			
			$_SESSION['success'] = !empty($langArr['send_success_message1']) ? $langArr['send_success_message1'] : "Transmission was successful.";
			header('location: '.$return_page);
			exit();

		} else {
			$_SESSION['failure'] = !empty($langArr['send_message211']) ? $langArr['send_message211'] : "Unable to send Token. Try Again2.";
			header('location: '.$return_page);
			exit();
		}

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
?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>

<div id="page-wrapper">
	<div id="send_token" class="send_common">
		
		<?php include('./includes/flash_messages.php') ?>
		<div class="row">
			
			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->
				   <div id="main_content" class="panel-body">
					   <div class="card">  
							
							<ul class="index_token_block">
								<li class="token_block">
									<div class="a1">
										<div class="img2"><div><img src="images/logo2/ectc.png" alt="ectc" /></div></div>
										<span class="text"><?php echo $n_full_name_array2['ectc']; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewCoinBalance, $n_decimal_point_array2[$token]); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array['ectc']; ?></span></span>
									</div>
								</li>
								<?php
								if ( $token != 'ectc' ) {
									?>
										<li class="token_block">
											<div class="a1">
												<div class="img2"><div><img src="images/logo2/<?php echo $token; ?>.png" alt="<?php echo $token; ?>" /></div></div>
												<span class="text"><?php echo $n_full_name_array2[$token]; ?></span>
												<span class="amount"><span class="amount_t1"><?php echo new_number_format($getBalance, $n_decimal_point_array2[$token]); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array[$token]; ?></span></span>
											</div>
										</li>
									<?php
								} ?>
							</ul>
				
							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">
								
								<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo $n_epay_name_array[$token]; ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['exchange_rate']) ? $langArr['exchange_rate'] : "Exchange Rate :"; ?>  1 <?php echo strtoupper(substr($token, 1)); ?> = <?php echo $getExchangeRateVal; ?> <?php echo $n_epay_name_array[$token]; ?></span>
										</label>
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" oninput='handleOnInput(this, 6)' id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="text">
									</div>
									
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo strtoupper(substr($token, 1)); ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<!--<span class="fee1"><?php //echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php //echo $getTokenFeeVal; ?> <?php //echo $n_epay_name_array['ectc']; ?></span>-->
											<span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php echo $getExchangeFee; ?> <?php echo $langArr['minimum_limit_is_eth_2']; ?></span>
										</label>
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="real_token_amount" name="real_token_amount" type="text" readonly />
									</div>

									<div class="col-md-12 btn_area">
										<input name="submit" class="btn" value="<?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>" type="submit">
									</div>
								</form>
							</div>

						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $('#amount').keyup(function () {
		var tmp = '<?php echo $getExchangeRateVal; ?>';
		var tmp2 = $("#amount").val() / tmp;
		$("#real_token_amount").val(tmp2);
    });
});

$("#customer_form").on('submit', function(){
    var get_name_result = $("#get_name_result").val();
    var amount = $("#amount").val();

    console.log(amount);
    if(amount >= 100001){
        alert('1회 충전량을 초과하였습니다.');
        return false;
    }

});

function handleOnInput(el, maxlength) {
    if(el.value.length > maxlength)  {
        el.value = el.value.substr(0, maxlength);
    }
}

</script>

<?php include_once 'includes/footer.php'; ?>
