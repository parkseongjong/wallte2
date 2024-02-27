<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$eth = $web3->eth;

$gasPriceInWei = 40000000000;
$eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
    $gasPriceInWei = $result->toString();
});
$gasPriceInWei = "0x".dechex($gasPriceInWei);

$userId = $_SESSION['user_id'];

//휴면 계정 확인용 컬럼.
$column = array(
    'A.account_type2','A.virtual_wallet_address','A.id_auth','A.transfer_passwd',
    'B.id','B.email','B.wallet_phone_email','B.register_with','B.passwd','B.passwd_new','B.passwd_salt','B.passwd_datetime',
    'B.name','B.lname','B.user_ip','B.phone','B.gender','B.dob','B.location','B.auth_phone','B.auth_name','B.auth_gender',
    'B.auth_dob','B.auth_local_code','B.n_country','B.n_phone','B.device','B.devId','B.devId2','B.devId3'
);

$db = getDbInstance();
//2021.06.16 by.OJT 휴면 회원은 조회 되어야 함.
//휴면 회원 쪽 조회 START
$db->where("A.id", $_SESSION['user_id']);
$db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
$row = $db->get('admin_accounts A',null,$column);
if(!$row){
    $db->where("id", $_SESSION['user_id']);
    $row = $db->get('admin_accounts');
}
$walletAddress = $row[0]['wallet_address'];
//    $db->where("id", $_SESSION['user_id']);
//    $row = $db->get('admin_accounts');
//    $walletAddress = $row[0]['wallet_address'];
//휴면 회원 쪽 조회 END

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







//$token = 'ctc';
//$tokenArr = $contractAddressArr[$token];

// 21.03.23 : 특정 사용자, 특정 코인 사용 불가능한 정보 가져오기
if ( new_get_untransmittable($_SESSION['user_id'], 'eth') > 0 ) { // 1이면 전송불가
    $_SESSION['failure_error'] = !empty($langArr['error_message1']) ? $langArr['error_message1'] : 'It cannot be moved.';
    header('Location:index.php');
    exit();
}

$return_page = 'send_eth.php';
$return_page2 = 'token.php?token=eth';

$getNewBalance = 0;
try {
    $eth->getBalance($walletAddress, function ($err, $balance) use (&$getNewBalance) {
        if ($err !== null) {
            throw new Exception($err->getMessage(), 1);
        }
        $getNewBalance = $balance->toString();
        $getNewBalance = $getNewBalance/1000000000000000000;
    });
} catch (Exception $e) {
    new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', eth) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
    $_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
    header('Location: ' . $return_page2);
    exit();
}


//$functionName = "balanceOf";
//$tokenAbi = $tokenArr['abi'];
//$tokenContractAddress = $contractAddress;
$decimalDigit = 1000000000000000000;




























///serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {














    if ( isset($_POST['p_token']) && !empty($_POST['p_token']) && isset($_POST['p_kind']) && !empty($_POST['p_kind']) && $_POST['p_token'] != $_POST['p_kind'] ) {
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















    if($_SESSION['user_id']==$n_master_id){ // 45
        $password =	$n_master_wallet_pass;
        $walletAddress = $row[0]['wallet_address'];
    }else{
        $password =	$row[0]['email'].$n_wallet_pass_key; // Changed it to set it at once on that page : config/new_config.php
        $walletAddress = $row[0]['wallet_address'];
    }


    //$functionName = "transfer";
    $toAccount = trim($_POST['address']);
    $fromAccount = $walletAddress;
    $amountToSend = trim($_POST['amount']);

    //$amountToSend = $amountToSend*$decimalDigit;
    $amountToSend = bcmul($amountToSend,$decimalDigit);  // 201112

    $amountToSend = dec2hex($amountToSend);

    $gas = '0x9088';
    $transactionId = '';

    // 20.08.04
    if ( $getNewBalance <= $_POST['amount'] ) {
        $_SESSION['failure'] = !empty($langArr['insufficient_eth_balance']) ? $langArr['insufficient_eth_balance'] : "Insufficient Eth Balance";
        header('location: '.$return_page);
        exit();
    }







    $personal = $web3->personal;
    try {
        $personal->unlockAccount($walletAddress, $password, function ($err, $unlocked) {
            if ($err !== null) {
                throw new Exception($err->getMessage(), 3);
            }
        });

    } catch (Exception $e) {

        $data_to_sendlog = [];
        $data_to_sendlog['send_type'] = 'send';
        $data_to_sendlog['coin_type'] = 'eth';
        $data_to_sendlog['user_id'] = $_SESSION['user_id'];
        $data_to_sendlog['msg_type'] = 'error'; // error, permission
        $data_to_sendlog['message'] = 'unlock';
        //$db = getDbInstance();
        $last_id_dts = $db->insert('send_error_logs', $data_to_sendlog);

        new_fn_logSave( 'Message : (' . $_SESSION['user_id'] . ', eth) ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
        $_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
        header('Location: ' . $return_page);
        exit();
    }

    try {
        $eth->sendTransaction([
            'from' => $fromAccount,
            'to' => $toAccount,
            'value' => '0x'.$amountToSend,
            'gasprice'=>$gasPriceInWei
        ], function ($err, $result) use (&$transactionId,&$return_page,&$langArr) {
            if ($err !== null) {
                throw new Exception($err->getMessage(), 4);
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
            $_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again.";
        }
        header('Location: ' . $return_page);
        exit();
    }

    // Add log records (2020-05-27, YMJ)
    $data_to_send_logs = [];
    $data_to_send_logs['send_type'] = 'send';
    $data_to_send_logs['coin_type'] = 'eth';
    $data_to_send_logs['from_id'] = $_SESSION['user_id'];
    //$data_to_send_logs['to_id'] = '';
    $data_to_send_logs['from_address'] = $fromAccount;
    $data_to_send_logs['to_address'] = $toAccount;
    $data_to_send_logs['amount'] = $_POST['amount'];
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
        $data_to_store['coin_type'] = 'eth';
        $data_to_store['sender_id'] = $_SESSION['user_id'];
        $data_to_store['reciver_address'] =$toAccount;
        $data_to_store['amount'] = $_POST['amount'];
        $data_to_store['fee_in_eth'] =0;
        $data_to_store['status'] = 'completed';
        $data_to_store['fee_in_gcg'] = 0;
        $data_to_store['transactionId'] = $transactionId;

        //$db = getDbInstance();
        $last_id = $db->insert('user_transactions', $data_to_store);
        header('location: send_result.php?type=send&txid='.$last_id_sl);
        exit();

    } else {
        $_SESSION['failure'] = !empty($langArr['send_message2']) ? $langArr['send_message2'] : "Unable to send Token. Try Again."; // (2020-05-22, YMJ)
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
                                        <div class="img2"><div><img src="images/logo2/eth.png" alt="eth" /></div></div>
                                        <span class="text"><?php echo $n_full_name_array['eth']; ?></span>
                                        <span class="amount"><span class="amount_t1"><?php echo new_number_format($getNewBalance,$n_decimal_point_array['eth']); ?></span><span class="amount_t2"> ETH</span></span>
                                    </div>
                                </li>
                            </ul>

                            <div id="validate_msg" ></div>
                            <div class="boxed bg--secondary boxed--lg boxed--border">

                                <form class="form" action="set_transferpw_frm_send.php" method="post"  id="customer_form" enctype="multipart/form-data">
                                    <input type="hidden" name="token" id="n_token" value="eth" />
                                    <input type="hidden" name="lang" id="n_lang" value="<?php echo $_SESSION['lang']; ?>" />
                                    <input type="hidden" name="kind" id="kind" value="" />
                                    <input type="hidden" name="payment_no" id="payment_no" value="" />

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
                                        <!-- <textarea required autocomplete="off" name="address" id="receiver_addr" class="form-control"></textarea>-->
                                        <div class="barcode_img_area">
                                            <input type=text required title="<?php echo $langArr['this_field_is_required']; ?>" autocomplete="off" id="receiver_addr" name="address" class="" placeholder="<?php echo !empty($langArr['send_explain1']) ? $langArr['send_explain1'] : 'Please paste your wallet address or take a barcode.'; ?>"><img src="images/icons/send_barcode.png" id="qrimg" class="barcode_img" />
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
                                        </label>
                                        <input autocomplete="off" required title="<?php echo $langArr['this_field_is_required']; ?>" id="amount" name="amount" placeholder="<?php echo !empty($langArr['send_explain2']) ? $langArr['send_explain2'] : 'Please enter the quantity to send.'; ?>" type="text">
                                    </div>
                                    <div class="clearfix"></div>

                                    <div id="show_msg" class="alert alert-info alert-dismissable"></div>
                                    <div class="clearfix"></div>

                                    <div class="col-md-12 btn_area">
                                        <input name="submit" class="btn" id="confirm_modal" value="<?php echo !empty($langArr['send_amount']) ? $langArr['send_amount'] : "Send Amount"; ?>" type="submit" />
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="confirm_modal_box" role="dialog">
                        <div class="modal-dialog confirm_modal_box1">
                            <form action="set_transferpw_frm_send.php" method="POST">
                                <input type="hidden" name="token" value="eth" />
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
                    data : {mode: 'get_name', waddr : addr},
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

<?php include_once 'includes/footer.php'; ?>
