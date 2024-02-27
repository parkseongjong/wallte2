<?php
// Page in use
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

use Pachico\Magoo\Magoo as walletMasking;
use wallet\common\Log as walletLog;

require __DIR__ .'/vendor/autoload.php';

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('계좌 이체 조회',['target_id'=>0,'action'=>'S']);

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
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
$accountType = $row[0]['admin_type'];	
$userEmail = $row[0]['email'];	
$actualLoginText = $row[0]['register_with'];
$codeSendTo = ($row[0]['register_with']=='email') ? "Email Id" : "Phone";
$walletAddress = $row[0]['wallet_address'];




$getNewBalance = 0 ;
$eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance) {
		
		if ($err !== null) {
			$_SESSION['failure'] = "Unable to Get User Eth Balance.";
			header('location: exchange.php');
			exit();
		}
		$getNewBalance = $balance->toString();
		$getNewBalance = $getNewBalance/1000000000000000000;
	});

$getNewCoinBalance = 0 ;
$functionName = "balanceOf";
$contract = new Contract($web3->provider, $testAbi);
$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalance){
	$getNewCoinBalance = reset($result)->toString();
	$getNewCoinBalance = $getNewCoinBalance/1000000000000000000;
});



$getNewCoinBalanceTokenPay = 0 ;
$functionName = "balanceOf";
$contract = new Contract($web3->provider, $testAbi);
$contract->at($tokenPayContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getNewCoinBalanceTokenPay){
	$getNewCoinBalanceTokenPay = reset($result)->toString();
	$getNewCoinBalanceTokenPay = $getNewCoinBalanceTokenPay/1000000000000000000;
});

///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
	//$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";                           
								//print_r($_POST);
								
		
	$totalAmt = trim($_POST['ethamount']);
	/* $emailCode = trim($_POST['email_code']);
	
	if(empty($emailCode)) {
		$_SESSION['failure'] = "Please Enter Verification Code";
		header('location: exchange.php');
		exit();
	}
	
	$sessionVerificationCode = $_SESSION['emailcode'];
	if($emailCode!=$sessionVerificationCode){
		$_SESSION['failure'] = "Please Enter Correct Verification Code";
		header('location: exchange.php');
		exit();
	} */

	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
	
	

	$adminPassword =	$n_master_wallet_pass;
	$adminAddress =	$n_master_wallet_address;
	if($_SESSION['user_id']==$n_master_id){
		$adminPassword =	$n_master_wallet_pass;
		$walletAddress = $row[0]['wallet_address'];
		

	}else{
		$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaImmuAM';
		$walletAddress = $row[0]['wallet_address'];
	}
	
	// get user eth balance
	$getEthBalance = 0 ; 
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getEthBalance) {
		
		if ($err !== null) {
			$_SESSION['failure'] = "Unable to Get User Eth Balance.";
			header('location: exchange.php');
			exit();
		}
		$getEthBalance = $balance->toString();
		$getEthBalance = $getEthBalance/1000000000000000000;
	});

	$amountToSend = trim($_POST['ethamount']);
	if(empty($amountToSend) || !(is_numeric($amountToSend))){
		$_SESSION['failure'] = $langArr['enter_valid_eth_amount'];
		header('location: exchange.php');
		exit();
	}
	
	if($amountToSend<0.028){
		$_SESSION['failure'] = $langArr['minimum_limit_is_eth'];
		header('location: exchange.php');
		exit();
	}
	
	
	$amountToSendWithTransferFee = $amountToSend+$transferFee;
	$ctcAmountToSend = $amountToSend*$getExchangePrice;
	
	// check user eth balance

	if($getEthBalance<$amountToSendWithTransferFee){
		$_SESSION['failure'] = $langArr['insufficient_eth_balance'];
		header('location: exchange.php');
		exit();
	}
	
	// check Admin token balance
	$contract = new Contract($web3->provider, $testAbi);
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$coinBalance = 0;
	$contract->at($contractAddress)->call($functionName, $n_master_wallet_address,function($err, $result) use (&$coinBalance){
		$coinBalance = reset($result)->toString();
		$coinBalance = $coinBalance/1000000000000000000;
	});

	if($coinBalance<$ctcAmountToSend){
		$_SESSION['failure'] = "Insufficient CTC Balance in Admin Account";
		header('location: exchange.php');
		exit();
	}
	
	
	
	
	
	
	
	
	$functionName = "transfer";
	
	$fromAccount = $walletAddress;
	
	

	
	// if admin send token than call transfer Method 
	if($_SESSION['user_id']==$n_master_id){

			$_SESSION['failure'] = "You are not allowed to exchange";
			header('location: exchange.php');
			exit();
	}
	else {
		

		$feePercent = 3.5;
		$adminFee = ($amountToSend*$feePercent)/100;
		$adminFee = number_format((float)$adminFee,2);
		//$actualAmountToSend = $amountToSend-$adminFee;
		$actualAmountToSend = $amountToSend;
		$actualAmountToSendWithoutDecimal = $actualAmountToSend;
		$actualAmountToSend = $actualAmountToSend*1000000000000000000;
		$actualAmountToSend = dec2hex($actualAmountToSend);
		$toAccount=$n_master_wallet_address;
		$fromAccountPassword = $userEmail.$n_wallet_pass_key;
		// unlock user account
		$personal = $web3->personal;
		$personal->unlockAccount($fromAccount, $fromAccountPassword, function ($err, $unlocked) {
			
		});
		
		$ethTransactionId = '';
		// send eth from user to admin account
		
		$eth->sendTransaction([
			'from' => $fromAccount,
			'to' => $toAccount,
			'value' => "0x".$actualAmountToSend,
			'gas' => '0x186A0',   //100000
			'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
		], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$actualAmountToSendWithoutDecimal,&$ethTransactionId) {
			if ($err !== null) {
				$_SESSION['failure'] = "Unable to Transfer Eth to Admin Account.";
				header('location: exchange.php');
				exit();
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
		$msg = "Transaction has made:) id: <a href=https://etherscan.io/tx/".$ethTransactionId.">" . $ethTransactionId . "</a>. You will get CTC when transaction will complete";
		$_SESSION['success'] = $msg;
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
			$personal->unlockAccount($fromAdminAccount, $n_master_wallet_pass, function ($err, $unlocked) {
				
			});
			
			$contract->at($contractAddress)->send('transfer', $toUserAccount, $ctcAmountToSend, [
					'from' => $fromAdminAccount,
					'gas' => '0x186A0',   //100000
					'gasprice' =>'0x218711A00'    //9000000000wei // 9 gwei
				], function ($err, $result) use ($contract, $fromAccount, $toAccount, &$transactionId) {
						if ($err !== null) {
							$_SESSION['failure'] = "Unable to Transfer CTC to User Account.";
							header('location: exchange.php');
							exit();
						}
						
						$transactionId =$result; 
				});
				
			if(!empty($transactionId)){
				//$data_to_store = filter_input_array(INPUT_POST);
				$data_to_store = [];
				$data_to_store['created_at'] = date('Y-m-d H:i:s');
				$data_to_store['sender_id'] = $n_master_id;
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
						
	header('location: exchange.php');
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
<style>
.form-control {
	flex-basis: 90%;
	color: #000;
	padding: 12px 12px;
	border: none;
	box-sizing: border-box;
	outline: none;
	letter-spacing: 1px;
	font-size: 17px;
	border-left: 1px solid #fff;
	border-bottom: 1px solid #fff;
	background: #eee;
	height:auto;
}

.panel-body {
	padding: 0 15px;
	background: none;
}

#receiver_addr {
	height: 89px;
}

.submit-button {
	width: 50%;
	background: rgb(51, 232, 255);
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 14px;
	font-weight: 400;
	border: 1px solid #33e8ff;
	padding: 11px 11px;
	letter-spacing: 1px;
	text-transform: uppercase;
	border-radius: 20px;
	cursor: pointer;
	transition: 0.5s all;
	-webkit-transition: 0.5s all;
	-o-transition: 0.5s all;
	-moz-transition: 0.5s all;
	-ms-transition: 0.5s all;
	margin-top: 24px;
}

.submin-bttn-part {
	text-align:center;	
}

.panel {
	background: none;
}

.huge {
	font-size: 29px;
}
.coin_box{background:#ffd602!important;    height: 140px;}

@media only screen and (max-width: 600px) {
.coin_box{ height: auto;}

}
</style>

<div id="page-wrapper">
	<div class="row">
	
<h5 style="color:#000; text-align:center;"><?php //echo !empty($langArr['exchange_heading']) ? $langArr['exchange_heading'] : "The amount of complimentary ETH upon registration has been raised from 0.0004 to 0.0007. If you're still seeing 0.0004 in your wallet, log out and log in again. If you're able to see the updated amount, then you need to repeat the process to be able to send it other wallet."; ?></h5>

		 <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['coin_bank']) ? $langArr['coin_bank'] : "Coin Bank"; ?></h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<div class="row">
    <div class="col-lg-2 col-md-2"></div>
		<div class="col-lg-4 col-md-4">
            <div class="panel panel-primary" style="border-color:#ffd602;">
                <div class="panel-heading coin_box">
                    <div class="row" style="color:#333;">
                        <div class="col-xs-3">
                           <img src="images/gcg2.png" width="100%" height="100%">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?php echo number_format($getNewCoinBalance,8); ?></div>
                            <div><?php echo !empty($langArr['ctc_balance']) ? $langArr['ctc_balance'] : "CTC Balance"; ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="panel panel-primary" style="border-color:#ffd602;">
                <div class="panel-heading coin_box">
                    <div class="row" style="color:#333;">
                        <div class="col-xs-3">
                            <img src="images/exchange.png" height="70px">
                        </div>
                        <div class="col-xs-9 text-rights ">
							<div class="form-group">
								<label><?php echo !empty($langArr['bank_name']) ? $langArr['bank_name'] : "Bank Name"; ?></label>
								<input type="text" style="width:100%" name="bank_name" />
							</div>
							<div class="form-group">
								<label><?php echo !empty($langArr['bank_number']) ? $langArr['bank_number'] : "Bank Number"; ?></label>
								<input type="text" style="width:100%" name="bank_number" />
							</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
		</div>
	
	<div class="row">
	 <div class="col-lg-2 col-md-2"></div>
		 <div class="col-lg-4 col-md-4">
            <div class="panel panel-primary" style="border-color:#ffd602;">
                <div class="panel-heading coin_box" >
                    <div class="row" style="color:#333;">
                        <div class="col-xs-3">
                            <img src="images/tp3_logo.png" height="70px">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" ><?php echo number_format($getNewCoinBalanceTokenPay,8); ?></div>
                            <div><?php echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "TP3 Balance"; ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
		
		 <div class="col-lg-4 col-md-4">
            <div class="panel panel-primary" style="border-color:#ffd602;">
                <div class="panel-heading coin_box" >
                    <div class="row" style="color:#333;">
                        <div class="col-xs-3">
                            <img src="images/exchange.png" height="70px">
                        </div>
                        <div class="col-xs-9 text-rights ">
							<div class="form-group">
								<label><?php echo !empty($langArr['bank_name']) ? $langArr['bank_name'] : "Bank Name"; ?></label>
								<input type="text" style="width:100%" name="bank_name" />
							</div>
							<div class="form-group">
								<label><?php echo !empty($langArr['bank_number']) ? $langArr['bank_number'] : "Bank Number"; ?></label>
								<input type="text" style="width:100%" name="bank_number" />
							</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
	</div>
	

       
		<!--	<div class="col-sm-12 col-md-12 form-part-token">
		<div class="panel">
		
	       <div id="main_content" class="panel-body">
	     
           <div class="card"> 
	
				<div id="validate_msg" ></div><div class="col-md-3"></div>
                <div class="boxed bg--secondary boxed--lg boxed--border col-md-6">
                   <form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
                            <div class="form-group col-md-12">
                                <label><?php //echo !empty($langArr['eth_amount']) ? $langArr['eth_amount'] : "Eth Amount :"; ?></label>
                                <input autocomplete="off" required  title="<?php //echo $langArr['this_field_is_required']; ?>" class="validate-required form-control" id="ethamount" name="ethamount" placeholder="" type="text">
                            </div>
                            
							<div class="form-group col-md-12">
                                <label><?php //echo !empty($langArr['exchange_rate']) ? $langArr['exchange_rate'] : "Exchange Rate :"; ?></label>
                               1 ETH  = <?php //echo $getExchangePrice; ?> CTC
							   
                            </div>
							<div class="form-group col-md-12">
                                <label><?php //echo !empty($langArr['ctc_amount']) ? $langArr['ctc_amount'] : "CTC Amount :"; ?></label>
                                <input autocomplete="off" disabled required class="validate-required form-control" id="ctcamount" name="ctcamount" placeholder="" type="text">
                            </div>
                             
						<div class="form-group col-md-12" >
                                <label><?php //echo ucfirst($actualLoginText); ?> Code:</label>
								<div>
                                <input placeholder="Verification code" type="text" autocomplete="false"  required="required" name="email_code" class="form-control input2" >
								<span class="send-button btn btn-info" id="get_code" style="padding: 13px 14px 13px 14px;cursor:pointer;margin-left:4px;">Get code</span>
								</div>
								<div id="show_msg"></div>
								
                            </div> 
                            <div class="form-group col-md-12 submin-bttn-part">
                                <input name="submit" class="submit-button btn btn-danger btn-sm" value="<?php //echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>" type="submit">
                            </div> 
                        </form>
                </div>
            </div>
        </div>
       
    </div> 


</div>-->

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
            f_name: {
                required: true,
                minlength: 3
            },
            l_name: {
                required: true,
                minlength: 3
            },   
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