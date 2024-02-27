<?php 
// Test page : eth 추가 위해
// eToken -> Token
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$eth = $web3->eth;

$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);

$token = strtolower($_GET['token']);

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

$accountType = $row[0]['admin_type'];
$actualLoginText = $row[0]['register_with'];	
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";	
$walletAddress = $row[0]['wallet_address'];



$return_page = 'exchange_etoken_re_test1.php?token='.$token;

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
	case 'eeth': // 추가, 210519
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


// 최소 전송금액
$getMinAmountVal = 0;
$getMinAmount = $db->where("module_name", 'min_transfer_'.$token.'_to_'.$new_token)->getOne('settings');
$getMinAmountVal = $getMinAmount['value'];


///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 

	// 사용자 잔액 확인

	// 마스터 잔액 확인
	// 마스터 unlock
	// send
	// log save

	$totalAmt = trim($_POST['amount']);

	if ( !is_numeric($totalAmt) ) { // 숫자가 아닐 경우
		$_SESSION['failure'] = !empty($langArr['input_invalid_value']) ? $langArr['input_invalid_value'] : 'Please enter a valid value.';
		header('Location:'.$return_page);
		exit();
	}
	
	// 최소 전송금액 체크
	if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $totalAmt < $getMinAmountVal) { 
		$ma_tmp = $getMinAmountVal.' '.$n_epay_name_array[$token];
		$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
		header('location: '.$return_page);
		exit();
	}			
	
	if ( $token == 'ectc' ) {
		if ( $getNewCoinBalance < $totalAmt + $getTokenFeeVal ) { // 잔액+수수료 부족
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: ' . $return_page);
			exit();
		}
	} else {
		if ( $getNewCoinBalance < $getTokenFeeVal ) { // 수수료 부족
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: ' . $return_page);
			exit();
		}
		if ( $getBalance < $totalAmt ) { // 잔액 부족
			$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
			header('Location: ' . $return_page);
			exit();
		}
	}
	
	// 마스터 잔액 확인
	
	$M_getBalance = 0;
	$M_getEthBalance = $wi_wallet_infos->wi_get_balance('2', 'eth', $adminAddress, $contractAddressArr);
	$M_getBalance = $wi_wallet_infos->wi_get_balance('2', $new_token, $adminAddress, $contractAddressArr);
	$token_amount = $_POST['real_token_amount'];
	
	//$token_amount = $totalAmt/$getExchangeRateVal; // Master가 User에게 전송해줘야 할 양
	
	if ( $M_getBalance < $token_amount ) {
		$_SESSION['failure'] = !empty($langArr['exchange_message6']) ? $langArr['exchange_message6'] : 'Insufficient Balance in Admin Account.';
		header('Location: ' . $return_page);
		exit();
	}

	// 마스터 unlock
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
	$transactionId = '';
	
	// send
	try {
		if ( $new_token == 'ctc' ) {
			$contract = new Contract($web3->provider, $testAbi);
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
		} else if ( $new_token == 'eth' ) { // 추가, 210519
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
			$otherTokenContract = new Contract($web3->provider, $tokenAbi);
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

		//db에서 포인트 차감

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
			// 수량 out
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
			
			// 수수료 out
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


			
		// 수량 in
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

		// 마스터 수수료 in
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
		$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again.";
		header('location: '.$return_page);
		exit();
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
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="text">
									</div>
									
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo strtoupper(substr($token, 1)); ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees :"; ?> <?php echo $getTokenFeeVal; ?> <?php echo $n_epay_name_array['ectc']; ?></span>
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
</script>

<?php include_once 'includes/footer.php'; ?>
