<?php 
// Test Page
// CTC -> eCTC
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();





require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;

$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	if ( !empty($result) ) {
		$gasPriceInWei = $result->toString();
	}
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);

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

if ( empty($row[0]['transfer_passwd']) ) {
	$_SESSION['failure'] = !empty($langArr['transfer_pw_message4']) ? $langArr['transfer_pw_message4'] : 'Please set payment password.';
	header('Location:profile.php');
	exit();
}

$token = 'ctc';
$send_type = 'exchange_eToken';
$return_page = 'exchange_ectc.php';
$masterAddress = $n_master_ectc_wallet_address;

// 21.03.23 : 특정 사용자, 특정 코인 사용 불가능한 정보 가져오기
if ( new_get_untransmittable($_SESSION['user_id'], $token) > 0 ) { // 1이면 전송불가
	$_SESSION['failure_error'] = !empty($langArr['error_message1']) ? $langArr['error_message1'] : 'It cannot be moved.';
	header('Location:index.php');
	exit();
}

// 잔액
$getNewBalance = 0;
$getNewCoinBalance = 0;
$getNewBalance = $wi_wallet_infos->wi_get_balance('2', 'eth', $walletAddress, $contractAddressArr);
$getNewCoinBalance = $wi_wallet_infos->wi_get_balance('2', 'ctc', $walletAddress, $contractAddressArr);

//$db = getDbInstance();

// 최소 전송금액
$getMinAmountVal = 0;
$getMinAmount = $db->where("module_name", 'min_transfer_ctc_to_ectc')->getOne('settings');
$getMinAmountVal = $getMinAmount['value'];

// 교환비율
$getExchangeRateVal = '';
$getExchangeRate = $db->where("module_name", 'exchange_ectc_per_ctc')->getOne('settings');
$getExchangeRateVal = $getExchangeRate['value'];






///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 


	$totalAmt = trim($_POST['amount']);

	// 최소전송금액 체크
	if ( !empty($getMinAmountVal) && $getMinAmountVal > 0 && $totalAmt < $getMinAmountVal) { 
		$ma_tmp = $getMinAmountVal.' '.strtoupper($token);
		$_SESSION['failure'] = !empty($langArr['send_min_amount']) ? $langArr['send_min_amount'].$ma_tmp : "The minimum limit is : ".$ma_tmp;
		header('location: '.$return_page);
		exit();
	}			

	// send transactions start

	if($_SESSION['user_id']==$n_master_id){
		$_SESSION['failure'] = !empty($langArr['exchange_message2']) ? $langArr['exchange_message2'] : 'You are not allowed to exchange.';
		header('location: '.$return_page);
		exit();
	}

	//$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');

	$password =	$row[0]['email'].$n_wallet_pass_key;
	$walletAddress = $row[0]['wallet_address'];
	
	$toAccount = $masterAddress;
	$fromAccount = $walletAddress;
	$amountToSend = trim($_POST['amount']);

	// unlock
	$personal = $web3->personal;
	try {
		$personal->unlockAccount($walletAddress, $password, function ($err, $unlocked) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 1);
			}
		});
	} catch (Exception $e) {

		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = $send_type;
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
	
	
	// 잔액 체크
	if($getNewCoinBalance < trim($_POST['amount']) ) {
		$_SESSION['failure'] = !empty($langArr['token_balance_not_sufficient']) ? $langArr['token_balance_not_sufficient'] : 'Token balance not sufficient';
		header('Location: '.$return_page);
		exit();
	}
	// ETH 잔액 체크
/*	if($getNewBalance < 0.008){
		$_SESSION['failure'] = !empty($langArr['insufficient_eth_balance']) ? $langArr['insufficient_eth_balance'] : "Insufficient Eth Balance";
		header('Location: '.$return_page);
		exit();
	}
*/
	//$amountToSend = $amountToSend*1000000000000000000;
	$amountToSend = bcmul($amountToSend,1000000000000000000); // 201112

	$amountToSend = dec2hex($amountToSend);
	$amountToSend = '0x'.$amountToSend; // Must add 0x
	$gas = '0x9088';
	$transactionId = '';

	$contract = new Contract($web3->provider, $testAbi);
	try {
		$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
			'from' => $fromAccount,
			'gas' => '0x186A0',   //100000
			'gasprice'=>$gasPriceInWei
		], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 2);
			}
			$transactionId = $result;
		});
	
	} catch (Exception $e) {
		$send_error_msg = '';
		if(stristr($e->getMessage(), 'gas required exceeds allowance') == TRUE) {
			$send_error_msg = '(gas required exceeds allowance)';
		} else if(stristr($e->getMessage(), 'insufficient funds') == TRUE) {
			$send_error_msg = '(insufficient funds)';
		}

		$data_to_sendlog = [];
		$data_to_sendlog['send_type'] = $send_type;
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

	// Add log records
	$data_to_send_logs = [];
	$data_to_send_logs['send_type'] = $send_type;
	$data_to_send_logs['coin_type'] = $token;
	$data_to_send_logs['from_id'] = $_SESSION['user_id'];
	//$data_to_send_logs['to_id'] = '';
	$data_to_send_logs['from_address'] = $fromAccount;
	$data_to_send_logs['to_address'] = $toAccount;
	$data_to_send_logs['amount'] = $_POST['amount'];
	$data_to_send_logs['fee'] = 0;
	if ( !empty($transactionId) ) {
		$data_to_send_logs['transactionId'] = $transactionId;
	}
	$data_to_send_logs['status'] = !empty($transactionId) ? 'send' : 'fail';
	$data_to_send_logs['etoken_send'] = 'P';
	$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

	//$db = getDbInstance();
	$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
	
	if(!empty($transactionId)) {
		
		$data_to_store = [];
		$data_to_store['created_at'] = date('Y-m-d H:i:s');

		$data_to_store['sender_id'] = $_SESSION['user_id'];
		$data_to_store['reciver_address'] = $toAccount;
		$data_to_store['amount'] = $_POST['amount'];
		$data_to_store['fee_in_eth'] = 0;
		$data_to_store['status'] = 'completed';
		$data_to_store['fee_in_gcg'] = 0;
		$data_to_store['transactionId'] = $transactionId;
		
		//$db = getDbInstance();
		$last_id = $db->insert('user_transactions', $data_to_store);

		$_SESSION['success'] = !empty($langArr['send_success_message1']) ? $langArr['send_success_message1'] : "Transmission was successful.";
		header('location: '.$return_page);
		exit();
		
	} else {
		$_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again.";
		header('location: '.$return_page);
		exit();
	}
	
	// send transactions end
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
	<div id="exchange_ectc" class="send_common">

		<?php include('./includes/flash_messages.php') ?>
		<div class="row">
			
			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->
				   <div id="main_content" class="panel-body">
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
								<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo strtoupper($token); ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['exchange_rate']) ? $langArr['exchange_rate'] : "Exchange Rate :"; ?> 1 <?php echo strtoupper($token); ?>  = <?php echo $getExchangeRateVal; ?> E-<?php echo strtoupper($token); ?></span>
										</label>
										<input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="number">
									</div>
									<div class="clearfix"></div>
									
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo 'E-'.strtoupper($token); ?> <?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span>
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
			var etoken_value = getAmt*<?php echo $getExchangeRateVal; ?>;
			$("#etoken_value").val(etoken_value);
        }
    });
});
</script>

<?php include_once 'includes/footer.php'; ?>
