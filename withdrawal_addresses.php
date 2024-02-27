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
unset($targetPostData);
//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();


//0x3719ef7904be4114327ce04e77e0d551eedbd63f

$log->info('token 목록 > token send 조회',['target_id'=>0,'action'=>'S']);
$web3Instance = new walletInfoWeb3();
//$web3outter = $web3Instance->outterInit();
//$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$web3 = $web3Instance->innerInit();
$eth = $web3->eth;

//$gasPriceInWei = 40000000000;
//$web3outter->eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
	$gasPriceInWei = $result->toString();
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);
$userId = $_SESSION['user_id'];
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$walletAddress = $row[0]['wallet_address'];


$db = getDbInstance();
$db->where("user_id", $_SESSION['user_id']);
$db->where("status", 'enable');
$addressList = $db->get('user_withdrawal_addresses');

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



///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
	$postData = filter_input_array(INPUT_POST);

	if($postData['form_type']=="delete_wallet_addr"){
		
		$db = getDbInstance();
		$db->where('id', $postData['wallet_id']);
		$db->where('user_id', $_SESSION['user_id']);

        $stat = $db->delete('user_withdrawal_addresses');

		//$stat = $db->update('user_withdrawal_addresses', ['status'=>'disable']);
		
		$_SESSION['failure'] = !empty($langArr['walletDeletedSuccesfully']) ? $langArr['walletDeletedSuccesfully'] : "Wallet Deleted Successfully";
		header('Location: withdrawal_addresses.php');
		exit();
	}
	else {

		$db = getDbInstance();
		$db->where("user_id", $_SESSION['user_id']);
		$db->where("status", 'enable');
		$addressData = $db->get('user_withdrawal_addresses');
		if(count($addressData)==3){
			$_SESSION['failure'] = !empty($langArr['alreadyAddedWithdrawalAddresses']) ? $langArr['alreadyAddedWithdrawalAddresses'] : "Already Added 3 Wallet Addresses";
			header('Location: withdrawal_addresses.php');
			exit();
		}

		$db = getDbInstance();
		$db->where("u_address", $postData['address']);
		$db->where("status", 'enable');
		$checkAlready = $db->get('user_withdrawal_addresses');
		
		if(!empty($checkAlready)){
			$_SESSION['failure'] = !empty($langArr['alreadyExistWithdrawalAddresses']) ? $langArr['alreadyExistWithdrawalAddresses'] : "Wallet Addresses Already Exist";
			header('Location: withdrawal_addresses.php');
			exit();
		}

		// TP3가 아닌데 키오스크가 받을 경우, 201013
	
		// print_r($postData); die;
		$dataArr = [];
		$dataArr['u_address'] = $postData['address'];
		$dataArr['u_name'] = $postData['wallet_name'];
		
		$dataArr['status'] = 'enable';
		$dataArr['user_id'] = $_SESSION['user_id'];

		//$db = getDbInstance();
		$insertIdWallet = $db->insert('user_withdrawal_addresses', $dataArr);
		

		if (!empty($insertIdWallet) ) {
			$_SESSION['success'] = !empty($langArr['withdrawalWalletAdded']) ? $langArr['withdrawalWalletAdded'] : "Wallet Address Added Successfully.";
		} else {
			$_SESSION['failure'] = !empty($langArr['withdrawalWalletAddedFailed']) ? $langArr['withdrawalWalletAddedFailed'] : "Unable to add wallet Address.";
		}
		header('Location: withdrawal_addresses.php');
		exit();


		
			
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
	<div id="token" class="send_common">
				
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
										<div class="img2"><div></div></div>
										<span class="text"><?php echo !empty($langArr['whitelist_wallet_address']) ? $langArr['whitelist_wallet_address'] : "Whitelist Wallet Addresses"; ?></span>
										<span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewCoinBalance,$n_decimal_point_array['ctc']); ?></span><span class="amount_t2"> CTC</span></span>
									</div>
								</li>
							</ul>
							
							<div id="validate_msg" ></div>
							<div class="boxed bg--secondary boxed--lg boxed--border">
								
								<form class="form" method="post"  id="customer_form" enctype="multipart/form-data">
								<input type="hidden" name="form_type" value="add_wallet_addr" />
									<input type="hidden" name="lang" id="n_lang" value="<?php echo $_SESSION['lang']; ?>" />
									
									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['wallet_name']) ? $langArr['wallet_name'] : "Wallet name"; ?></span>
										</label>
										<!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class=""></textarea>-->
										<div class="barcode_img_area">
											<input type=text" required title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" maxlength="10" id="wallet_name" name="wallet_name" class="" placeholder="<?php echo !empty($langArr['wallet_name']) ? $langArr['wallet_name'] : 'Please enter your wallet name'; ?>">
										</div>
									</div>

									<div class="form-group col-md-12">
										<label class="address_area">
											<span class="label_subject"><?php echo !empty($langArr['send_text1']) ? $langArr['send_text1'] : "Address"; ?></span>
											<div id="to_name">
												<img src="images/icons/send_name_chk_t.png" alt="success" />
												<span id="receiver_addr_name"></span>
											</div>
											<div id="to_message">
												<img src="images/icons/send_name_chk_f.png" alt="fail" />
												<span id="receiver_message"></span>
											</div>
										</label>
										<!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class=""></textarea>-->
										<div class="barcode_img_area">
                                            <!-- CTCTM 부분 수정 -->
											<input type=text required title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="" placeholder="<?php echo !empty($langArr['send_explain1']) ? $langArr['send_explain1'] : 'Please paste your wallet address or take a barcode.'; ?>"><img src="images/icons/send_barcode.png" id="qrimg" alt="barcode" class="barcode_img" />
										</div>
									</div>


									<div class="clearfix"></div>
									<input type="hidden" name="get_name_result" id="get_name_result" value="0" />
								
																	
									<div id="show_msg" class="alert alert-info alert-dismissable"></div>
									<div class="clearfix"></div>

									<div class="col-md-12 btn_area">
										<input name="submit" class="btn" id="confirm_modals" value="<?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>" type="submit" />
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


<hr>

		<div class="row send-rr">
								<div id="history_new">			
									<div class="history">
									<div class="subject">
										<span><?php echo !empty($langArr['withdrawalAddresses']) ? $langArr['withdrawalAddresses'] : "Withdrawal Addresses" ?></span>
										<span> <?php echo !empty($langArr['list']) ? $langArr['list'] : "List" ?></span>
										
									</div>
								
									<?php if(!empty($addressList)) {
									$i=1;
									foreach($addressList as $addressSingle){
									?>
									<ul class="contents">
										<li>
											<span class="icon">
												<img class="main" src="images/icons/history_circle-.png" alt="circle"><br>
												<img class="big" src="images/icons/history_circles.png" alt="circle">
											</span>
											<span class="amount"><?php echo $addressSingle['u_address']; ?></span>
											<p style="color:#000;" class="date"><?php echo $addressSingle['u_name']; ?></p>
											<p class="date"><?php echo date("d M,Y h:i A",strtotime($addressSingle['created_at'])); ?></p>
											<form class="form" method="post"  id="customer_forms">
												<input type="hidden" name="wallet_id" value="<?php echo $addressSingle['id']; ?>" />
												<input type="hidden" name="form_type" value="delete_wallet_addr" />
												<div class="col-md-4 btn_area">
													<input type="submit" name="Submit" value="Delete" onclick="return confirm('출금주소를 삭제하시겠습니까?');" />
												</div>
											</form>
											
										</li>
									</ul>
									<br/>
									<br/>
									<?php $i++; } } ?>
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
	//$("#receiver_addr").on('propertychange change keyup paste input', function(){
    $("#receiver_addr").on('propertychange change keyup paste input', function(){
		addr_check();
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
	
	
		var get = isAddress(addr);
        
		if (get == false) {
			$("#to_name").removeClass('to_name');
			$("#receiver_addr_name").html('');
			$("#to_message").addClass('to_name');
			$("#receiver_message").html("<?php echo !empty($langArr['invalid_wallet_addresss']) ? $langArr['invalid_wallet_addresss'] : 'Invalid Wallet Address2'; ?>");
			$("#get_name_result").val('0');
		}
        else {
            $("#to_message").removeClass('to_name');
			$("#receiver_message").html("");
			
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

<?php include_once 'includes/footer.php'; ?>
