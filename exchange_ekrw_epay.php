<?php 
// Test Page (사용안할듯 : 2021-05-12)
// E-KRW -> E-CTC, E-TP3, ...
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

require_once './config/config_exchange.php';


if(!isset($_GET['token']) || empty($_GET['token'])){
	header("Location:index.php");
	exit();
}

$token = strtolower($_GET['token']); // tp3, mc, krw
if ( $token == 'ekrw' ) {
	header('Location:index.php');
	exit();
}
if ( !in_array($token, $new_walletapp_epay_list) ) {
	header('Location:index.php');
	exit();
}

$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$walletAddress = $row[0]['wallet_address'];

// When connecting with a domestic IP, only users who have completed authentication are allowed to send
// If you access overseas IP, you can send without authentication.
$user_id_auth = 'N';
if ( !empty($row[0]['id_auth']) && $row[0]['id_auth'] == 'Y' ) {
	$user_id_auth = 'Y';
}
$ip_kor = '';
$ip_kor = trim(new_ipinfo_ip_chk('2'));

if ($ip_kor == 'KR' && $user_id_auth != 'Y') {
	$_SESSION['failure'] = !empty($langArr['send_auth_need']) ? $langArr['send_auth_need'] : 'Can be used after authentication. Please use after verifying your identity in [My Info].';
	header('Location:profile.php');
	exit();
}

$send_type = 'ekrw_to_etoken';
$return_page = 'exchange_ekrw_epay.php?token='.$token;
//$masterAddress = $n_master_etoken_receive_address[$token];

// 특정 사용자, 특정 코인 사용 불가능한 정보 가져오기
if ( new_get_untransmittable($_SESSION['user_id'], $token) > 0 ) { // 1이면 전송불가
	$_SESSION['failure_error'] = !empty($langArr['error_message1']) ? $langArr['error_message1'] : 'It cannot be moved.';
	header('Location:index.php');
	exit();
}

// 잔액

$getBalance_KRW = 0;
$getBalance = 0;
$getBalance_KRW = $row[0]['etoken_ekrw'];
$getNewBalance = $row[0]['etoken_'.$token];

$coin = substr($token, 1); // ctc, tp3, ...


// 최소 전송금액
$getMinAmountVal = 0;
$getMinAmount = $db->where("module_name", 'min_transfer2_ekrw_to_'.$token)->getOne('settings');
$getMinAmountVal = $getMinAmount['value'];
// 최소전송금액을 KRW에 맞춰야 하나 아니면 E-Pay에 맞춰야 하나 => E-Pay에 맞출것

// 교환비율
$getExchangeRateVal = '';
$getExchangeRateVal2 = '';
$getExchangeRateVal2 = ex_get_coin_price_one('KRW', strtoupper($coin)); // 1 coin = ? KRW / 거래소의 실시간 가격
$getExchangeRateVal = bcdiv(1, $getExchangeRateVal2, 18); // 1 KRW = ? coin

// 수수료
$getFeeVal = '';
$getFee = $db->where("module_name", "exchange_ekrw_fee")->getOne('settings');
$getFeeVal = $getFee['value'];
$feeAmount = 0;

///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
	
	$amount_KRW = trim($_POST['amount']);
	//$amount_Coin = trim($_POST['etoken_value']);

	$amount_Coin = $amount_KRW* $getExchangeRateVal;
	$amount_Coin = round($amount_Coin, 4);
	$amount_Coin = sprintf('%0.4f', $amount_Coin);

	$feeAmount = $amount_KRW*$getFeeVal;
	$feeAmount = round($feeAmount, 4);
	$feeAmount = sprintf('%0.2f', $feeAmount);
	
	// E-KRW 받고, E-Pay 보낼 관리자
	$adminId = $n_master_etoken_id;
	$adminAddress = $n_master_etoken_wallet_address;
	
	// 수수료 받는 관리자
	$receive_fee_id = $n_master_etoken_ctc_fee_id;
	$receiver_fee_address = $n_master_etoken_ctc_fee_wallet_address;

	// 최소전송금액 체크
	if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $amount_Coin < $getMinAmountVal) {
		$ma_tmp = $getMinAmountVal.' '.strtoupper($token);
		$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
		header('location: '.$return_page);
		exit();
	}

	// 잔액 체크
	if ( $feeAmount + $amount_KRW > $getBalance_KRW ) {
		$_SESSION['failure'] = !empty($langArr['insufficient_balance']) ? $langArr['insufficient_balance'] : 'The balance is insufficient.';
		header('location: '.$return_page);
		exit();
	}
	
	
	
	// 본인 E-KRW 차감

	$coin_type = 'ekrw';

	
	$db->where("id", $_SESSION['user_id']);
	$updateArr = [];
	$updateArr['etoken_'.$coin_type] = $db->dec($amount_KRW);
	$last_id = $db->update('admin_accounts', $updateArr);

	//if ( $last_id) {
	$data_to_log = [];
	$data_to_log['user_id'] = $_SESSION['user_id'];
	$data_to_log['wallet_address'] = $walletAddress;
	$data_to_log['coin_type'] = $coin_type;
	$data_to_log['points'] = '-'.$amount_KRW;
	$data_to_log['in_out'] = 'out';
	if ( !empty($send_type) ) {
		$data_to_log['send_type'] = $send_type;
	}
	$data_to_log['send_user_id'] = $adminId;
	$data_to_log['send_wallet_address'] = $adminAddress;
	$data_to_log['send_fee'] = $feeAmount;
	$data_to_log['created_at'] = date("Y-m-d H:i:s");
	
	$last_id_sl = $db->insert('etoken_logs', $data_to_log);
	//}


	// 관리자 E-KRW 증가

	
	$db->where("id", $adminId);
	$updateArr = [];
	$updateArr['etoken_'.$coin_type] = $db->inc($amount_KRW);
	$last_id2 = $db->update('admin_accounts', $updateArr);

	//if ( $last_id2 ) {
	$data_to_log = [];
	$data_to_log['user_id'] = $adminId;
	$data_to_log['wallet_address'] = $adminAddress;
	$data_to_log['coin_type'] = $coin_type;
	$data_to_log['points'] = '+'.$amount_KRW;
	$data_to_log['in_out'] = 'in';
	if ( !empty($send_type) ) {
		$data_to_log['send_type'] = $send_type;
	}
	$data_to_log['send_user_id'] = $_SESSION['user_id'];
	$data_to_log['send_wallet_address'] = $walletAddress;
	$data_to_log['send_fee'] = '0';
	$data_to_log['created_at'] = date("Y-m-d H:i:s");
	
	$last_id_sl2 = $db->insert('etoken_logs', $data_to_log);
	//}


	// 본인 E-PAY 추가
	$db->where("id", $_SESSION['user_id']);
	$updateArr = [];
	$updateArr['etoken_'.$token] = $db->inc($amount_Coin);
	$last_id3 = $db->update('admin_accounts', $updateArr);

	//if ( $last_id3 ) {
	$data_to_log = [];
	$data_to_log['user_id'] = $_SESSION['user_id'];
	$data_to_log['wallet_address'] = $walletAddress;
	$data_to_log['coin_type'] = $token;
	$data_to_log['points'] = '+'.$amount_Coin;
	$data_to_log['in_out'] = 'in';
	if ( !empty($send_type) ) {
		$data_to_log['send_type'] = $send_type;
	}
	$data_to_log['send_user_id'] = $adminId;
	$data_to_log['send_wallet_address'] = $adminAddress;
	$data_to_log['send_fee'] = '0';
	$data_to_log['created_at'] = date("Y-m-d H:i:s");
	
	$last_id_sl3 = $db->insert('etoken_logs', $data_to_log);
	//}



	// 관리자 E-PAY 차감
	$db->where("id", $adminId);
	$updateArr = [];
	$updateArr['etoken_'.$token] = $db->dec($amount_Coin);
	$last_id4 = $db->update('admin_accounts', $updateArr);

	//if ( $last_id4) {
	$data_to_log = [];
	$data_to_log['user_id'] = $adminId;
	$data_to_log['wallet_address'] = $adminAddress;
	$data_to_log['coin_type'] = $token;
	$data_to_log['points'] = '-'.$amount_Coin;
	$data_to_log['in_out'] = 'out';
	if ( !empty($send_type) ) {
		$data_to_log['send_type'] = $send_type;
	}
	$data_to_log['send_user_id'] = $_SESSION['user_id'];
	$data_to_log['send_wallet_address'] = $walletAddress;
	$data_to_log['send_fee'] = 0;
	$data_to_log['created_at'] = date("Y-m-d H:i:s");
	
	$last_id_sl4 = $db->insert('etoken_logs', $data_to_log);
	//}

	
	// 수수료 차감
	if ( $feeAmount > 0 ) {
		$db->where("id", $_SESSION['user_id']);
		$updateArr = [];
		$updateArr['etoken_'.$coin_type] = $db->dec($feeAmount);
		$last_id5 = $db->update('admin_accounts', $updateArr);

		//if ( $last_id5) {
		$data_to_log = [];
		$data_to_log['user_id'] = $_SESSION['user_id'];
		$data_to_log['wallet_address'] = $walletAddress;
		$data_to_log['coin_type'] = $coin_type;
		$data_to_log['points'] = '-'.$feeAmount;
		$data_to_log['in_out'] = 'out';
		if ( !empty($send_type) ) {
			$data_to_log['send_type'] = $send_type;
		}
		$data_to_log['send_user_id'] = $receive_fee_id;
		$data_to_log['send_wallet_address'] = $receiver_fee_address;
		$data_to_log['send_fee'] = 0;
		$data_to_log['created_at'] = date("Y-m-d H:i:s");
		
		$last_id_sl5 = $db->insert('etoken_logs', $data_to_log);
		//}

		// 수수료 관리자 증가

		$db->where("id", $receive_fee_id);
		$updateArr = [];
		$updateArr['etoken_'.$coin_type] = $db->inc($feeAmount);
		$last_id6 = $db->update('admin_accounts', $updateArr);

		//if ( $last_id6 ) {
		$data_to_log = [];
		$data_to_log['user_id'] = $receive_fee_id;
		$data_to_log['wallet_address'] = $receiver_fee_address;
		$data_to_log['coin_type'] = $coin_type;
		$data_to_log['points'] = '+'.$feeAmount;
		$data_to_log['in_out'] = 'in';
		if ( !empty($send_type) ) {
			$data_to_log['send_type'] = $send_type;
		}
		$data_to_log['send_user_id'] = $_SESSION['user_id'];
		$data_to_log['send_wallet_address'] = $walletAddress;
		$data_to_log['send_fee'] = '0';
		$data_to_log['created_at'] = date("Y-m-d H:i:s");
		
		$last_id_sl6 = $db->insert('etoken_logs', $data_to_log);
		//}
	}
	
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>

<div id="page-wrapper">
	<div id="exchange_etoken" class="send_common">

		<?php include('./includes/flash_messages.php') ?>
		<div class="row">

			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->
				   <div id="main_content" class="panel-body">
						<div class="card">
							
							<ul class="index_token_block">
								<li class="token_block">
									<div class="a1">
										<div class="img2"><div><img src="images/logo2/ekrw.png" alt="ekrw" /></div></div>
										<span class="text"><?php echo $n_full_name_array2['ekrw']; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getBalance_KRW,$n_decimal_point_array2['ekrw']); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array['ekrw']; ?></span></span>
									</div>
								</li>
								<li class="token_block">
									<div class="a1">
										<div class="img2"><div><img src="images/logo2/<?php echo $token; ?>.png" alt="<?php echo $token; ?>" /></div></div>
										<span class="text"><?php echo $n_full_name_array2[$token]; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewBalance,$n_decimal_point_array2[$token]); ?></span><span class="amount_t2"> <?php echo $n_epay_name_array[$token]; ?></span></span>
									</div>
								</li>
							</ul>
							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">
								<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject">E-KRW <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['exchange_rate']) ? $langArr['exchange_rate'] : "Exchange Rate :"; ?> 1 <?php echo $n_epay_name_array[$token]; ?> = <?php echo $getExchangeRateVal2; ?> E-KRW</span>
										</label>
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="text">
									</div>
									<div class="clearfix"></div>
														
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo $n_epay_name_array[$token]; ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1">, <?php echo !empty($langArr['minimum_limit']) ? $langArr['minimum_limit'] : "Minimum limit"; ?> : <?php echo $getMinAmountVal; ?> <?php echo $n_epay_name_array[$token]; ?></span>
											<span class="fee1"><?php echo !empty($langArr['fees']) ? $langArr['fees'] : "Fees"; ?> <span id="fee1"></span> <?php echo $n_epay_name_array['ekrw']; ?></span>
										</label>
										<input autocomplete="off" disabled required id="etoken_value" name="etoken_value" placeholder="" type="text">
									</div>
									<div class="clearfix"></div>
									
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
    if($(this).val() == '') {
            $("#etoken_value").val(0);
        } else {
			var getAmt = $('#amount').val();
			var etoken_value = (getAmt*<?php echo $getExchangeRateVal; ?>).toFixed(4);
			$("#etoken_value").val(etoken_value);

			var fee1 = (getAmt*<?php echo $getFeeVal; ?>).toFixed(2);
			$("#fee1").html(fee1);
        }
    });	
});


</script>

<?php include_once 'includes/footer.php'; ?>
