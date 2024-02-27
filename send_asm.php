<?php
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$sendApproved = $row[0]['asm_send_approved'];	
$accountType = $row[0]['admin_type'];	
///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	                            
								//print_r($_POST);
								
		
		$totalAmt = trim($_POST['amount']);
		$emailCode = trim($_POST['email_code']);
		
		
		
		
		
		 /*  if(empty($emailCode)) {
			$_SESSION['failure'] = "Please Enter Verification Code";
			header('location: send_asm.php');
			exit();
		}
		
		$sessionVerificationCode = $_SESSION['emailcode'];
		if($emailCode!=$sessionVerificationCode){
			$_SESSION['failure'] = "Please Enter Correct Verification Code";
			header('location: send_asm.php');
			exit();
		} */
		if($sendApproved=='N' && $accountType=='user'){
			$_SESSION['failure'] = "You doesn't have permission for transfer";
			header('location: send_asm.php');
			exit();
		}					
								
								
	// send transactions start

	//if($_POST['address']=='' or  ($_SESSION['eth_balance']<=0 || $_SESSION['Token_balance'] <=trim($_POST['amount']))){
		
		
		
		
		/* if($_SESSION['eth_balance'] < 0.0005){
			$_SESSION['failure'] = "Insufficient Eth fees.";
			header('location: send_token.php');
			exit();
		} */
		
		
	//}
	
	
	
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
	
	
	//echo $_SESSION['user_id'];
	//exit();
	$adminPassword =	'ajay@mailinator.comZUMBAE54R2507c16VipAjaImmuAM';
	$adminAddress =	'0x1481a5d32ea2fa570c6b0cd2b729a38b5de2d932';
	if($_SESSION['user_id']==45){
		$adminPassword =	'ajay@mailinator.comZUMBAE54R2507c16VipAjaImmuAM';
		$walletAddress = $row[0]['wallet_address'];
		
		//$password  = "E54R2507c16VipAjaImmuAM";
		//$walletAddress  = "0xf7c6ecbbbac3fe7ec61e09d53b92dda060cd90fb";
	}else{
		$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaImmuAM';
		$walletAddress = $row[0]['wallet_address'];
	}
	
	// unlock account
	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$personal = $web3->personal;
	$personal->unlockAccount($adminAddress, $adminPassword, function ($err, $unlocked) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ($unlocked) {
			//echo 'New account is unlocked!' . PHP_EOL;
		} else {
			//echo 'New account isn\'t unlocked' . PHP_EOL;
		}
	});


	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"initialSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_value","type":"uint256"}],"name":"burn","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_subtractedValue","type":"uint256"}],"name":"decreaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_addedValue","type":"uint256"}],"name":"increaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"burner","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Burn","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]';

	
	
	
	
	
	
	
	
	
	$contract = new Contract($web3->provider, $testAbi);
	$contractAddress = '0xA67d6bce80A4a98CC66ea508B1B5791919fE3176';
	$functionName = "transfer";
	$toAccount = trim($_POST['address']);
	$fromAccount = $walletAddress;
	$amountToSend = trim($_POST['amount']);
	
	
	
		
	
	$functionName = "balanceOf";
	//$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
		$coinBalance = $result['balance']->toString();
	});
	

	$EMTCBalance = $coinBalance/1000000000000000000;
	
	
	if($EMTCBalance < trim($_POST['amount'])){
			$_SESSION['failure'] = "Token balance not sufficient";
			header('location: send_asm.php');
			exit();
		}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// if admin send token than call transfer Method 
	if($_SESSION['user_id']==45){
		$amountToSend = $amountToSend*1000000000000000000;

		$amountToSend = dec2hex($amountToSend);
		$gas = '0x9088';
		$transactionId = '';
		$contract->at($contractAddress)->send('transfer', $toAccount, $amountToSend, [
				'from' => $fromAccount,
				'gas' => '0x186A0',   //100000
				'gasprice' =>'0x218711A00'    //9000000000wei // 9 gwei
				//'gas' => '0xD2F0'
			], function ($err, $result) use ($contract, $fromAccount, $toAccount,$transactionId) {
				if ($err !== null) {
					throw $err;
				}
				if ($result) {
					$msg = "Transaction has made:) id: <a href=https://etherscan.io/tx/".$result.">" . $result . "</a>";
					$_SESSION['success'] = $msg;
				}
				$transactionId = $result;
				if(!empty($transactionId))
				{
					
					$data_to_store = filter_input_array(INPUT_POST);
					$data_to_store = [];
					$data_to_store['created_at'] = date('Y-m-d H:i:s');
					$data_to_store['coin_type'] = "asm";
					$data_to_store['sender_id'] = $_SESSION['user_id'];
					$data_to_store['reciver_address'] = $_POST['address'];
					$data_to_store['amount'] = $_POST['amount'];
					$data_to_store['fee_in_eth'] =0;
					$data_to_store['fee_in_gcg'] = $_POST['amount'] * 0.05;
					$data_to_store['transactionId'] = $transactionId;
					
					//print_r($data_to_store);die;
					$db = getDbInstance();
					$last_id = $db->insert('user_transactions', $data_to_store);
					
					
				}  
				else {
					$_SESSION['failure'] = "Unable to send Token ! Try Again";
				}
			});
			
			header('location: send_asm.php');
			exit();
	}
	else {
		
		  if($totalAmt<=10) {
			$_SESSION['failure'] = "Amount Should Be Grater Than 10";
			header('location: send_token.php');
			exit();
		}
		$adminFee = 10;
		$actualAmountToSend = $amountToSend-$adminFee;
		$actualAmountToSendWithoutDecimal = $actualAmountToSend;
		$actualAmountToSend = $actualAmountToSend*1000000000000000000;
		
		$actualAmountToSend = dec2hex($actualAmountToSend);
		$gas = '0x9088';
		$transactionId = '';
		
		$senderAccount = "0x1481a5d32ea2fa570c6b0cd2b729a38b5de2d932";
		$ownerAccount = $walletAddress;
		
		// send GCG Token to destination Address
		$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $toAccount, $actualAmountToSend, [
                        'from' => $senderAccount,
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei
					], function ($err, $result) use ($contract, $ownerAccount, $toAccount, &$transactionId) {
						if ($err !== null) {
							$transactionId = '';
						}
						else {
							$transactionId = $result;
						}
					});

		if(!empty($transactionId))
		{
			$msg = "Transaction has made:) id: <a href=https://etherscan.io/tx/".$transactionId.">" . $transactionId . "</a>";
			$_SESSION['success'] = $msg;
			
			$data_to_store = filter_input_array(INPUT_POST);
			$data_to_store = [];
			$data_to_store['created_at'] = date('Y-m-d H:i:s');
			$data_to_store['sender_id'] = $_SESSION['user_id'];
			$data_to_store['coin_type'] = "asm";
			$data_to_store['reciver_address'] = $_POST['address'];
			$data_to_store['amount'] = $actualAmountToSendWithoutDecimal;
			$data_to_store['fee_in_eth'] = 0;
			$data_to_store['fee_in_gcg'] = $adminFee;
			$data_to_store['transactionId'] = $transactionId;
			
			//print_r($data_to_store);die;
			$db = getDbInstance();
			$last_id = $db->insert('user_transactions', $data_to_store);
			
			
			// send GCG Token to destination Address START
			
			$adminTransactionId = '';
			
			$adminFeeInDecimal = $adminFee*1000000000000000000;
			$adminFeeInDecimal = dec2hex($adminFeeInDecimal);
			$contract->at($contractAddress)->send('transferFrom',$ownerAccount, $senderAccount, $adminFeeInDecimal, [
							'from' => $senderAccount,
							'gas' => '0x186A0',   //100000
							'gasprice' =>'0x12A05F200'    //5000000000wei // 5 gwei
						], function ($err, $result) use ($contract, $ownerAccount,  &$adminTransactionId) {
							if ($err !== null) {
								$adminTransactionId = '';
							}
							else {
								$adminTransactionId = $result;
							}
						});
			
			if(!empty($adminTransactionId))
			{			
				$data_to_store_admin = filter_input_array(INPUT_POST);
				$data_to_store_admin = [];
				$data_to_store_admin['created_at'] = date('Y-m-d H:i:s');
				$data_to_store_admin['coin_type'] = "asm";
				$data_to_store_admin['sender_id'] = $_SESSION['user_id'];
				$data_to_store_admin['reciver_address'] = $senderAccount;
				$data_to_store_admin['amount'] = $adminFee;
				$data_to_store_admin['fee_in_eth'] = 0;
				$data_to_store_admin['fee_in_gcg'] = 0;
				$data_to_store_admin['transactionId'] = $transactionId;
				
				//print_r($data_to_store);die;
				$db = getDbInstance();
				$last_id = $db->insert('user_transactions', $data_to_store_admin); 		
			}
			// send GCG Token to destination Address END
			
			
		} 
		else {
			$_SESSION['failure'] = "Unable to send Token ! Try Again";
			
		}	
		
		

		header('location: send_asm.php');
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



$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
	
	
	//echo $_SESSION['user_id'];
	//exit();
	$adminPassword =	'ajay@mailinator.comZUMBAE54R2507c16VipAjaImmuAM';
	$adminAddress =	'0x1481a5d32ea2fa570c6b0cd2b729a38b5de2d932';
	if($_SESSION['user_id']==45){
		$adminPassword =	'ajay@mailinator.comZUMBAE54R2507c16VipAjaImmuAM';
		$walletAddress = $row[0]['wallet_address'];
		
		//$password  = "E54R2507c16VipAjaImmuAM";
		//$walletAddress  = "0xf7c6ecbbbac3fe7ec61e09d53b92dda060cd90fb";
	}else{
		$password =	$row[0]['email'].'ZUMBAE54R2507c16VipAjaImmuAM';
		$walletAddress = $row[0]['wallet_address'];
	}
	
	// unlock account
	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
	$personal = $web3->personal;
	$personal->unlockAccount($adminAddress, $adminPassword, function ($err, $unlocked) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ($unlocked) {
			//echo 'New account is unlocked!' . PHP_EOL;
		} else {
			//echo 'New account isn\'t unlocked' . PHP_EOL;
		}
	});


	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_from","type":"address"},{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"initialSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_value","type":"uint256"}],"name":"burn","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_subtractedValue","type":"uint256"}],"name":"decreaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"balance","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"owner","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"_to","type":"address"},{"name":"_value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"_spender","type":"address"},{"name":"_addedValue","type":"uint256"}],"name":"increaseApproval","outputs":[{"name":"success","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"_owner","type":"address"},{"name":"_spender","type":"address"}],"name":"allowance","outputs":[{"name":"remaining","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"previousOwner","type":"address"},{"indexed":true,"name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"burner","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Burn","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]';

	
	
	
	
	
	
	
	
	
	$contract = new Contract($web3->provider, $testAbi);
	$contractAddress = '0xA67d6bce80A4a98CC66ea508B1B5791919fE3176';

	
		
	
	$functionName = "balanceOf";
	//$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
		$coinBalance = $result['balance']->toString();
	});
	

	$EMTCBalance = $coinBalance/1000000000000000000;


//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>

<div id="page-wrapper">
	<div class="row">
	<div class="row">
<h5 style="color:green; text-align:center;">The amount of complimentary ETH upon registration has been raised from 0.0004 to 0.0007. If you're still seeing 0.0004 in your wallet, log out and log in again. If you're able to see the updated amount, then you need to repeat the process to be able to send it other wallet.</h5>
</div>
		 <div class="col-lg-12">
				<h2 class="page-header">Send ASM</h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<div class="row">
		<div class="col-lg-4 col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/logo.png" width="50%" height="100%">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($EMTCBalance,8); ?></div>
                            <div>ASM Balance</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <img src="https://www.fxmag.ru/crypto/coins/scr/1321_64.png" height="70px">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;" ><?php echo number_format($_SESSION['eth_balance'],8); ?></div>
                            <div>ETH Balance</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
	
	

       

    </div>
	
	
	
		<div class="col-sm-12 col-md-6">
		<div class="panel">
		<!-- main content -->
	       <div id="main_content" class="panel-body">
	       <!-- page heading -->
           <div class="card"> 
	
		   <div id="validate_msg" ></div>
                <div class="boxed bg--secondary boxed--lg boxed--border">
                   <form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">

                            <div class="form-group col-md-12">
                                <label>Address:</label>
                                <textarea required autocomplete="off" id="receiver_addr" name="address" class="form-control"></textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Amount:</label>
                                <input autocomplete="off" required class="validate-required form-control" id="amount" name="amount" placeholder="" type="text">
                            </div>
							<!--<div class="form-group col-md-12">
                                <label>Fees  :</label>
                                5%
                            </div>-->
                            <div class="form-group col-md-12">
                                <label>Fees Amount :</label>
                                <input class="validate-required form-control" id="fees" name="fees" value="10" readonly="" type="text">
                            </div>
                           <div class="form-group col-md-12" >
                                <label>Amount to send:</label>
                                <input class="validate-required form-control"  id="actual" name="actual_amount" readonly="" type="text">
                            </div> 
							<div class="form-group col-md-12" >
                                <label>Email Code:</label>
                                <input placeholder="Email code" type="text" autocomplete="false"  required="required" name="email_code" class="form-control input2" >
								<span class="send-button btn btn-info" id="get_code" style="padding:10px;cursor:pointer;">Get code</span>
								<div id="show_msg"></div>
                            </div>
							
                            <div class="form-group col-md-12">
                                <input name="submit" class="btn btn-danger btn-sm" value="Send Amount" type="submit">
                            </div>
							
                        </form>
                </div>
            </div>
        </div>
        <!--end of row-->
    </div> 


</div>


<script type="text/javascript">
$(document).ready(function(){
	
	$("#customer_form").submit(function(){
			var addr = $("#receiver_addr").val();
			var get = isAddress(addr);
			if(get==false){ 
				$("#validate_msg").html("<div class='alert alert-danger'>Invalid Eth Address</div>");  
				return false;
			}
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
	


    $('#amount').keyup(function () {
    if($(this).val() == '')
        {
            $("#actual").val('0.0');
        }
        else
        {
			var Fees = 10;
            var totalAmt = $(this).val();
			var actalAmt  = parseFloat(totalAmt)-parseFloat(Fees);
			var actalAmt = parseFloat(actalAmt).toFixed(8);
            $("#actual").val(actalAmt);
        }
    });
 
	$("#get_code").click(function(){
		$.ajax({
			beforeSend:function(){
				$("#show_msg").html('<img src="images/ajax-loader.gif" />');
			},
			url : 'sendmailasm.php',
			type : 'POST',
			dataType : 'json',
			success : function(resp){
				$("#show_msg").html('<div class="alert alert-success">Verification code send to your email id.</div>');
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			},
			error : function(resp){
				$("#show_msg").html('<div class="alert alert-success">Verification code send to your email id.</div>');
				setTimeout(function(){ $("#show_msg").hide(); }, 10000);
			}
		}) 
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
		} else if (/^(0x)?[0-9a-f]{40}$/.test(address) || /^(0x)?[0-9A-F]{40}$/.test(address)) {
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
</script>

<?php include_once 'includes/footer.php'; ?>