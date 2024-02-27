<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

// 21.01.25 : 거래소 오픈했기 때문에 서비스 종료함
$_SESSION['failure'] = !empty($langArr['service_end_message_exchange']) ? $langArr['service_end_message_exchange'] : 'This charging service has been terminated.';
header('Location: index.php');
exit();

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$eth = $web3->eth;


$db = getDbInstance();
$db->where("module_name", 'exchange_rate');
$getSetting = $db->get('settings');

$getExchangePrice = $getSetting[0]['value'];

$transferFee = 0.0009;
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$sendApproved = $row[0]['sendapproved'];
$checkApproved = $row[0]['usdt_approved'];	
$accountType = $row[0]['admin_type'];	
$userEmail = $row[0]['email'];	
$actualLoginText = $row[0]['register_with'];
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";
$walletAddress = $row[0]['wallet_address'];


$return_page = 'exchange.php';

$getNewBalance = 0;
try {
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance) {
		if ($err !== null) {
			throw new Exception($err->getMessage(), 1);
		}
		$getNewBalance = $balance->toString();
		$getNewBalance = $getNewBalance/1000000000000000000;
	});
} catch (Throwable $e) {
	new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
	header('Location: index.php');
	exit();
} catch (Exception $e) {
	new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
	header('Location: index.php');
	exit();
}

$getNewBalance = ($getNewBalance>0.0045 && $checkApproved=='N' && $row[0]['transfer_approved'] == 'C') ? $getNewBalance-0.0045 : $getNewBalance; // Fee choice : 20.08.04
$getNewCoinBalance = 0;
$functionName = "balanceOf";
try {
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalance){
		if ($err !== null) {
			throw new Exception($err->getMessage(), 2);
		}
		if ( !empty( $result ) ) { // Add (2020-05-18, YMJ)
			$getNewCoinBalance = reset($result)->toString();
			$getNewCoinBalance = $getNewCoinBalance/1000000000000000000;
		}
	});
} catch (Throwable $e) {
	new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
	header('Location: index.php');
	exit();
} catch (Exception $e) {
	new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
	$_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
	header('Location: index.php');
	exit();
}

///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// 20.08.12 12:58
	/*
	$db = getDbInstance();
	$db->where("module_name", 'lock_sending');
	$getlockSending = $db->getOne('settings');
	$getlockSendingVal = '';
	if ( isset($getlockSending) && !empty($getlockSending['value']) ) {
		$getlockSendingVal = $getlockSending['value'];
	}
	if ( $accountType!='admin' && $row[0]['transfer_approved'] == 'C' && $getlockSendingVal == 'C' ) {
		$_SESSION['failure'] = !empty($langArr['waiting_message']) ? $langArr['waiting_message'] : 'Please try again later.';
		header('Location: ' . $return_page);
		exit();
	}*/

	 // Changed it to set it at once on that page : config/new_config.php
	//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";                           
									
		
	$totalAmt = trim($_POST['ethamount']);
	/* $emailCode = trim($_POST['email_code']);
	
	if(empty($emailCode)) {
		$_SESSION['failure'] = "Please Enter Verification Code";
		header('location: '.$return_page);
		exit();
	}
	
	$sessionVerificationCode = $_SESSION['emailcode'];
	if($emailCode!=$sessionVerificationCode){
		$_SESSION['failure'] = "Please Enter Correct Verification Code";
		header('location: '.$return_page);
		exit();
	} */

	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
	
	

	if($_SESSION['user_id']==$n_master_id_exc){ // 45, Changed it to set it at once on that page : config/new_config.php
		$walletAddress = $row[0]['wallet_address'];
	}else{
		//$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaImmuAM';
		$walletAddress = $row[0]['wallet_address'];
	}
	
	// get user eth balance
	$getEthBalance = 0; 
	try {
		$eth->getBalance($walletAddress, function ($err, $balance) use (&$getEthBalance) {
			if ($err !== null) {
				throw new Exception($err->getMessage(), 3);
			}
			$getEthBalance = $balance->toString();
			$getEthBalance = $getEthBalance/1000000000000000000;
		});
	} catch (Throwable $e) {
		new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
		header('Location: '.$return_page);
		exit();
	} catch (Exception $e) {
		new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
		header('Location: '.$return_page);
		exit();
	}


	$amountToSend = trim($_POST['ethamount']);
	if(empty($amountToSend) || !(is_numeric($amountToSend))){
		$_SESSION['failure'] = $langArr['enter_valid_eth_amount'];
		header('location: '.$return_page);
		exit();
	}
	
	$db = getDbInstance();
	$db->where("module_name", 'min_exchange_amount_ctc');
	$getMinAmount = $db->getOne('settings');
	$getMinAmountVal = !empty($getMinAmount['value']) ? $getMinAmount['value'] : '0.028';

	if($amountToSend<$getMinAmountVal){
		$minimum_limit_is_eth_1 = !empty($langArr['minimum_limit_is_eth_1']) ? $langArr['minimum_limit_is_eth_1'] : 'Minimum limit is ';
		$minimum_limit_is_eth_2 = !empty($langArr['minimum_limit_is_eth_2']) ? $langArr['minimum_limit_is_eth_2'] : ' ETH';
		$_SESSION['failure'] = $minimum_limit_is_eth_1.$getMinAmountVal.$minimum_limit_is_eth_2;
		header('location: '.$return_page);
		exit();
	}

	$amountToSendWithTransferFee = $amountToSend+$transferFee;
	$ctcAmountToSend = $amountToSend*$getExchangePrice;
	
	// check user eth balance

	if($getEthBalance<$amountToSendWithTransferFee){
		$_SESSION['failure'] = $langArr['insufficient_eth_balance'];
		header('location: '.$return_page);
		exit();
	}
	
	// check Admin token balance
	$contract = new Contract($web3->provider, $testAbi);
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$coinBalance = 0;
	try {
		$contract->at($contractAddress)->call($functionName, $n_master_wallet_address_exc_out,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				throw new Exception($err->getMessage(), 4);
			}
			if ( !empty( $result ) ) { // Add (2020-05-18, YMJ)
				$coinBalance = reset($result)->toString();
				$coinBalance = $coinBalance/1000000000000000000;
			}
		});
	} catch (Throwable $e) {
		new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['send_message7']) ? $langArr['send_message7'] : 'Unable to Get Balance.';
		header('Location: '.$return_page);
		exit();
	} catch (Exception $e) {
		new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		$_SESSION['failure'] = !empty($langArr['send_message7']) ? $langArr['send_message7'] : 'Unable to Get Balance.';
		header('Location: '.$return_page);
		exit();
	}

	if($coinBalance<$ctcAmountToSend){
		//$_SESSION['failure'] = "Insufficient CTC Balance in Admin Account";
		$_SESSION['failure'] = !empty($langArr['exchange_message1']) ? $langArr['exchange_message1'] : 'Insufficient CTC Balance in Admin Account.'; // (2020-05-22, YMJ)
		header('location: '.$return_page);
		exit();
	}
	
	
	
	
	
	
	
	
	$functionName = "transfer";
	
	$fromAccount = $walletAddress;
	
	

	
	// if admin send token than call transfer Method 
	if($_SESSION['user_id']==$n_master_id_exc){ // 45, Changed it to set it at once on that page : config/new_config.php

			//$_SESSION['failure'] = "You are not allowed to exchange";
			$_SESSION['failure'] = !empty($langArr['exchange_message2']) ? $langArr['exchange_message2'] : 'You are not allowed to exchange.'; // (2020-05-22, YMJ)
			header('location: '.$return_page);
			exit();
	}
	else {
		

		$feePercent = 3.5;
		$adminFee = ($amountToSend*$feePercent)/100;
		$adminFee = number_format((float)$adminFee,2);
		//$actualAmountToSend = $amountToSend-$adminFee;
		$actualAmountToSend = $amountToSend;
		$actualAmountToSendWithoutDecimal = $actualAmountToSend;

		//$actualAmountToSend = $actualAmountToSend*1000000000000000000;
		$actualAmountToSend = bcmul($actualAmountToSend,1000000000000000000); // 201112

		$actualAmountToSend = dec2hex($actualAmountToSend);
		$toAccount=$n_master_wallet_address_exc;
		$fromAccountPassword = $userEmail.$n_wallet_pass_key;
		// unlock user account
		$personal = $web3->personal;
		$personal->unlockAccount($fromAccount, $fromAccountPassword, function ($err, $unlocked) {
			
		});
		
		$ethTransactionId = '';
		// send eth from user to admin account
		
		try {
			$eth->sendTransaction([
				'from' => $fromAccount,
				'to' => $toAccount,
				'value' => "0x".$actualAmountToSend,
				//'gas' => '0x186A0',   //100000
				//'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
			], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$actualAmountToSendWithoutDecimal,&$ethTransactionId) {
				if ($err !== null) {
					throw new Exception($err->getMessage(), 5);
				}
				$data_to_store = filter_input_array(INPUT_POST);
				$data_to_store = [];
				$data_to_store['created_at'] = date('Y-m-d H:i:s');
				$data_to_store['sender_id'] = $_SESSION['user_id'];
				$data_to_store['reciver_address'] =$toAccount;
				$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
				$data_to_store['status'] = 'pending';
				$data_to_store['fee_in_eth'] = 0;
				$data_to_store['fee_in_gcg'] = 0;
				$data_to_store['transactionId'] = $transaction;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store);
				$ethTransactionId = $transaction;
			});
		} catch (Throwable $e) {
			new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			$_SESSION['failure'] = !empty($langArr['exchange_message3']) ? $langArr['exchange_message3'] : 'Unable to Transfer Eth to Admin Account.';
			header('location: '.$return_page);
			exit();
		} catch (Exception $e) {
			new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			$_SESSION['failure'] = !empty($langArr['exchange_message3']) ? $langArr['exchange_message3'] : 'Unable to Transfer Eth to Admin Account.';
			header('location: '.$return_page);
			exit();
		}



		$msg = "Transaction has made:) id: <a href=https://etherscan.io/tx/".$ethTransactionId.">" . $ethTransactionId . "</a>. ".$langArr['exchange_message4']; // (2020-05-22, YMJ)
		$_SESSION['success'] = $msg;

		
		// Add log records (2020-05-18, YMJ)
		$data_to_send_logs = [];
		$data_to_send_logs['send_type'] = 'exchange';
		$data_to_send_logs['coin_type'] = 'eth';
		$data_to_send_logs['from_id'] = $_SESSION['user_id'];
		$data_to_send_logs['to_id'] = $n_master_id_exc;
		$data_to_send_logs['from_address'] = $fromAccount;
		$data_to_send_logs['to_address'] = $toAccount;
		$data_to_send_logs['amount'] = $actualAmountToSendWithoutDecimal;
		$data_to_send_logs['fee'] =0;
		if ( !empty($ethTransactionId) ) {
			$data_to_send_logs['transactionId'] = $ethTransactionId;
		}
		$data_to_send_logs['status'] = !empty($ethTransactionId) ? 'send' : 'fail';
		$data_to_send_logs['created_at'] = date('Y-m-d H:i:s');

		$db = getDbInstance();
		$last_id_sl = $db->insert('user_transactions_all', $data_to_send_logs);
		

		/* if(!empty($ethTransactionId)) { 
		$transactionId = '';
		// send CTC Token To User Account
			$fromAdminAccount = $toAccount;
			$toUserAccount = $fromAccount;
			$ctcAmountToSend = $amountToSend*2500;
			$actualAmountToSendWithoutDecimal = $ctcAmountToSend;
			$ctcAmountToSend = $ctcAmountToSend*1000000000000000000;
			$ctcAmountToSend = dec2hex($ctcAmountToSend);
			
			// unlock admin account
			$personal = $web3->personal;
			$personal->unlockAccount($fromAdminAccount, $n_master_wallet_pass_exc, function ($err, $unlocked) {
				
			});
			
			$contract->at($contractAddress)->send('transfer', $toUserAccount, $ctcAmountToSend, [
					'from' => $fromAdminAccount,
					'gas' => '0x186A0',   //100000
					'gasprice' =>'0x218711A00'    //9000000000wei // 9 gwei
				], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId,&$return_page) {
						if ($err !== null) {
							$_SESSION['failure'] = "Unable to Transfer CTC to User Account.";
							header('location: '.$return_page);
							exit();
						}
						
						$transactionId =$result; 
				});
				
			if(!empty($transactionId)){
				//$data_to_store = filter_input_array(INPUT_POST);
				$data_to_store = [];
				$data_to_store['created_at'] = date('Y-m-d H:i:s');
				$data_to_store['sender_id'] = $n_master_id_exc;
				$data_to_store['reciver_address'] = $toUserAccount;
				$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
				$data_to_store['fee_in_eth'] = 0;
				$data_to_store['fee_in_gcg'] = 0;
				$data_to_store['transactionId'] = $transactionId;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store);
			}	
			$msg = "Transaction has made:) id: <a href=https://etherscan.io/tx/".$transactionId.">" . $transactionId . "</a>";
			$_SESSION['success'] = $msg;
			
		} */
	}
						
	header('location: '.$return_page);
	exit();		

   
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
	<div id="exchange" class="send_common">
		<!--<div class="row">
			<h5 style="color:#000; text-align:center;"><?php //echo !empty($langArr['exchange_heading']) ? $langArr['exchange_heading'] : "The amount of complimentary ETH upon registration has been raised from 0.0004 to 0.0007. If you're still seeing 0.0004 in your wallet, log out and log in again. If you're able to see the updated amount, then you need to repeat the process to be able to send it other wallet."; ?></h5>
			<div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?></h2>
			</div>
		</div>-->
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
									<a href="token.php?token=ctc" title="CTC">
										<div class="img2"><div><img src="images/logo2/ctc.png" alt="ctc" /></div></div>
										<span class="text"><?php echo $n_full_name_array['ctc']; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewCoinBalance,$n_decimal_point_array['ctc']); ?></span><span class="amount_t2"> CTC</span></span>
									</a>
								</li>
								<li class="token_block">
									<a href="token.php?token=eth" title="ETH">
										<div class="img2"><div><img src="images/logo2/eth.png" alt="eth" /></div></div>
										<span class="text"><?php echo $n_full_name_array['eth']; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewBalance, $n_decimal_point_array['eth']); ?></span><span class="amount_t2"> ETH</span></span>
									</a>
								</li>
							</ul>

							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">
								<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['eth_amount']) ? $langArr['eth_amount'] : "Eth Amount :"; ?></span>
											<span class="fee1"><?php echo !empty($langArr['exchange_rate']) ? $langArr['exchange_rate'] : "Exchange Rate :"; ?>  1 ETH  = <?php echo $getExchangePrice; ?> CTC</span>
										</label>
										<input autocomplete="off" required  title="<?php echo $langArr['this_field_is_required']; ?>" id="ethamount" name="ethamount" placeholder="" type="text">
									</div>
									
									<div class="form-group col-md-12">
										<label><span class="label_subject"><?php echo !empty($langArr['ctc_amount']) ? $langArr['ctc_amount'] : "CTC Amount :"; ?></span></label>
										<input autocomplete="off" disabled required id="ctcamount" name="ctcamount" placeholder="" type="text">
									</div>
									 
									<!--<div class="form-group col-md-12" >
										<label><?php //echo ucfirst($actualLoginText); ?> Code:</label>
										<div>
										<input placeholder="Verification code" type="text" autocomplete="false"  required="required" name="email_code" class="form-control input2" >
										<span class="send-button btn btn-info" id="get_code" style="padding: 13px 14px 13px 14px;cursor:pointer;margin-left:4px;">Get code</span>
										</div>
										<div id="show_msg"></div>
										
									</div> -->
									<div class="col-md-6 btn_area">
										<input name="submit" class="btn" value="<?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>" type="submit">
									</div> 
								</form>
							</div>

						</div><!-- card -->
					</div><!-- main_content -->
				</div>
			</div><!-- col-sm-12 col-md-12 form-part-token -->
		</div><!-- row -->
	</div>
</div>
	
	
	


<script type="text/javascript">
$(document).ready(function(){
	
	$("#customer_form").submit(function(){
			$(".submin-bttn-part").hide();
			/* var addr = $("#receiver_addr").val();
			var get = isAddress(addr);
			if(get==false){ 
				$("#validate_msg").html("<div class='alert alert-danger'>Invalid Eth Address</div>");  
				return false;
			} */
		});
	
   $("#customer_form").validate({
       rules: {
            ethamount: {
                required: true,
                minlength: 1
            }
        }
    });
	


    $('#ethamount').keyup(function () {
    if($(this).val() == '')
        {
            $("#ctcamount").val(0);
        }
        else
        {
			var getAmt = $('#ethamount').val();
			var ctcamt = getAmt*<?php echo $getExchangePrice; ?>;
			$("#ctcamount").val(ctcamt);
            
        }
    });

    $('#ctcamount').keyup(function () {
    if($(this).val() == '')
        {
            $("#ethamount").val(0);
        }
        else
        {
			var getAmt = $('#ctcamount').val();
			var ethamt = getAmt/<?php echo $getExchangePrice; ?>;
			ethamt = parseFloat(ethamt);
			$("#ethamount").val(ethamt);
            
        }
    });	
 
	/* $("#get_code").click(function(){
		$.ajax({
			beforeSend:function(){
				$("#show_msg").html('<img src="images/ajax-loader.gif" />');
			},
			url : 'sendemailcode.php',
			type : 'POST',
			dataType : 'json',
			success : function(resp){
				$("#show_msg").html('<div class="alert alert-success">Verification code send to your <?php //echo $codeSendTo; ?>.</div>');
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			},
			error : function(resp){
				$("#show_msg").html('<div class="alert alert-success">Verification code send to your <?php //echo $codeSendTo; ?>.</div>');
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			}
		}) 
	 }); */
	
});



</script>

<?php include_once 'includes/footer.php'; ?>