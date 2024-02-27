<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

use wallet\common\Log as walletLog;
//use wallet\common\Info as walletInfo;
use wallet\common\InfoWeb3 as walletInfoWeb3;

require __DIR__ .'/vendor/autoload.php';

if(!isset($_GET['token']) || empty($_GET['token'])){
    header("Location:index.php");
}
if(empty( $_SESSION['user_id'] )) {
    return;
    exit;
}

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('token 목록 조회',['target_id'=>0,'action'=>'S']);

//require('includes/web3/vendor/autoload.php');
//use Web3\Web3;
//use Web3\Contract;


$web3Instance = new walletInfoWeb3();
//$web3outter = $web3Instance->outterInit();
//$web3Inner = $web3Instance->innerInit();
$web3Inner = $web3Instance->innerTempInit();


//$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
//$web3 = $web3Instance->innerInit();
$web3 = $web3Instance->innerTempInit();
$eth = $web3Inner->eth;


$walletAddress = '';

$tokenName = $_GET['token'];
// for check walletAddress is empty or not start
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$userEmail = $row[0]['email'];
if ($db->count > 0) {
    $walletAddress = $row[0]['wallet_address'];
    $user_name = '';
    $user_name = get_user_real_name($row[0]['auth_name'], $row[0]['name'], $row[0]['lname']);
}
else
{
    return;
    exit;
}


// �ܾ���ȸ �κ�

$functionName = "balanceOf";
// error_reporting(0);
switch ($tokenName) {
    case 'eth':
        $getBalance = 0 ;
        try {
            $eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
                if ($err !== null) {
                    throw new Exception($err->getMessage(), 1);
                }
                $getBalance = $balance->toString();
                $getBalance = $getBalance/1000000000000000000;
            });
            $getBalance = number_format($getBalance, $n_decimal_point_array[$tokenName]);
            $getBalance = rtrim($getBalance, 0);
            $getBalance = rtrim($getBalance, '.');
        } catch (Throwable $e) {
            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            $_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
            header('location: index.php');
            exit();
        } catch (Exception $e) {
            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            $_SESSION['failure'] = !empty($langArr['send_message1']) ? $langArr['send_message1'] : 'Unable to Get User Eth Balance.';
            header('location: index.php');
            exit();
        }


        break;
    case 'ctc':
        $getBalance = 0 ;
        try {
            //$contract = new Contract($web3->provider, $testAbi);
            $contract = $web3Instance->innerContract($web3Inner->provider, $testAbi);
            $contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getBalance){
                if ($err !== null) {
                    throw new Exception($err->getMessage(), 2);
                }
                if ( !empty( $result ) ) {
                    $getBalance = reset($result)->toString();
                    $getBalance = $getBalance/1000000000000000000;
                }
            });
            $getBalance = number_format($getBalance, $n_decimal_point_array[$tokenName]);
            $getBalance = rtrim($getBalance, 0);
            $getBalance = rtrim($getBalance, '.');
        } catch (Throwable $e) {
            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            $_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
            header('location: index.php');
            exit();
        } catch (Exception $e) {
            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            $_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
            header('location: index.php');
            exit();
        }

        break;
    default:

        $getBalance = 0 ;
        $tokenArr = $contractAddressArr[$tokenName];
        $tokenAbi = $tokenArr['abi'];
        $tokenContractAddress = $tokenArr['contractAddress'];
        $decimalDigit = $tokenArr['decimal'];
        try {
            //$otherTokenContract = new Contract($web3->provider, $tokenAbi);
            $otherTokenContract = $web3Instance->innerContract($web3Inner->provider, $testAbi);
            $otherTokenContract->at($tokenContractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$getBalance,$decimalDigit){
                if ($err !== null) {
                    throw new Exception($err->getMessage(), 3);
                }
                if ( !empty( $result ) ) {
                    $getBalance = reset($result)->toString();
                    $getBalance = $getBalance/$decimalDigit;
                }
            });
            $getBalance = number_format($getBalance, $n_decimal_point_array[$tokenName]);
            $getBalance = rtrim($getBalance, 0);
            $getBalance = rtrim($getBalance, '.');
        } catch (Throwable $e) {
            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            $_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
            header('location: index.php');
            exit();
        } catch (Exception $e) {
            new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            $_SESSION['failure'] = !empty($langArr['send_message6']) ? $langArr['send_message6'] : 'Unable to Get User Balance.';
            header('location: index.php');
            exit();
        }

        break;
} // switch


// for check walletAddress is empty or not end


//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");

include_once('includes/header.php');


if(empty($walletAddress)){
    //$web3 = new Web3('http://139.162.29.60:8545/');
    //$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
    $personal = $web3->personal;
    $newAccount = '';
    // create account
    $personal->newAccount($userEmail.$n_wallet_pass_key, function ($err, $account) use (&$newAccount) { //$personal->newAccount($userEmail, function ($err, $account) use (&$newAccount) {
        /* if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        } */
        $newAccount = $account;
        //echo 'New account: ' . $account . PHP_EOL;
    });

    $personal->unlockAccount($newAccount, $userEmail.$n_wallet_pass_key, function ($err, $unlocked) { //$personal->unlockAccount($newAccount, $userEmail, function ($err, $unlocked) {
        /* if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        if ($unlocked) {
            echo 'New account is unlocked!' . PHP_EOL;
        } else {
            echo 'New account isn\'t unlocked' . PHP_EOL;
        } */
    });
    $walletAddress = $newAccount;
    // update walletAddress into database
    $db = getDbInstance();
    $db->where("id", $_SESSION['user_id']);
    $row = $db->update('admin_accounts',['wallet_address'=>$walletAddress]);
}

//$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=ethereum:".$walletAddress;
$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$walletAddress;

if ($tokenName == 'ctc') {
    $sendPageUrl = "send_token_test1.php";
} else if ($tokenName == 'eth') {
    $sendPageUrl = "send_eth.php";
} else {
    $sendPageUrl = "send_other.php?token=".strtolower($tokenName);
}

?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>
<div id="page-wrapper">
    <div id="token">
        <!--<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><?php echo strtoupper($tokenName)." Token" //echo !empty($langArr['receive_token']) ? $langArr['receive_token'] : "Receive Token"; ?></h1>
			</div>
		</div>-->
        <!-- /.row -->

        <!--
						<ul class="send-part">

							<li onclick="myFunction()" style="cursor:pointer;"><img  src="images/4.png" width="50px">
							  <p><?php echo !empty($langArr['copy']) ? $langArr['copy'] : "Copy"; ?></p>
							</li>
							</ul>-->

        <div class="alert alert-info alert-dismissable none" id="e_info_msg"><a href="#" class="close" data-dismiss="alert" aria-label="close">x</a>
            <?php echo !empty($langArr['plz_use_e_coin_message1']) ? $langArr['plz_use_e_coin_message1'] : "Can't be transferred. Please use e-Pay."; ?>
            <?php //echo !empty($langArr['commonServiceNotice']) ? $langArr['commonServiceNotice'] : "Checking. We will complete the inspection as soon as possible."; ?>
        </div>

        <div class="history_top">
            <div class="top">
                <p><?php echo $user_name; ?><?php echo !empty($langArr['token_history_text6']) ? $langArr['token_history_text6'] : "'s Balance"; ?></p>
                <div class="top_amount">
                    <!--<div class="img"><div><img src="images/logo/<?php echo $tokenName; ?>.png" alt="<?php echo $tokenName; ?>" /></div></div>-->
                    <div class="img2"><div><img src="images/logo2/<?php echo $tokenName; ?>.png" alt="<?php echo $tokenName; ?>" /></div></div>
                    <span><?php echo $getBalance.' '.strtoupper($tokenName); ?></span>
                </div>
                <ul>
                    <?php if ( $_SESSION['user_id'] != '5137' && ($tokenName == 'tp3' || $tokenName == 'mc' ) ) { ?>
                        <li class="send_btn"><a href="javascript:;" onclick="view_message();" title="send"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></a></li>
                    <?php } else { ?>
                        <li class="send_btn"><a href="<?php echo $sendPageUrl; ?>" title="send"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></a></li>
                    <?php } ?>
                    <li onClick="showReceive();" class="receive_btn"><?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></li>
                </ul>
            </div>
        </div>

        <hr>

        <!-- /.row -->
        <div class="row send-rr">
            <div id="history_new">
                <div id="loading_history" class="none">
                    <img src="images/icons/loading.gif" alt="loading" />
                </div>
            </div>
            <?php
            // I changed to ajax because the loading speed was slow. (2020-05-28, YMJ)
            // The previous file was backed up there. : token_20200528.php
            ?>
        </div>
        <!-- /.row -->

    </div>
</div>
<!-- /#page-wrapper -->


<!-- Modal -->
<div class="modal fade" id="myModalReceive" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><img src="images/icons/tmenu_qrcorde.png" alt="barcode" /></button>
                <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                <h4 class="modal-title"><?php echo strtoupper($tokenName); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="receive_new">
                        <div class="barcode">
                            <img id="barcodeimage" src="<?php echo $barCodeUrl; ?>" />
                            <p><?php echo !empty($langArr['token_barcode_text1']) ? $langArr['token_barcode_text1'] : "My Wallet Address"; ?></p>
                            <span class="showtxtpop"><?php echo $walletAddress;?></span>
                        </div>
                        <div id="show_set_amount"></div>

                        <div class="btn1" onclick="myFunctionPop()"><?php echo !empty($langArr['token_barcode_text2']) ? $langArr['token_barcode_text2'] : "Copy Address"; ?></div>

                        <div class="amount_btn" onclick="showInputBox()"><span><?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?></span></div>
                        <div id="set_amt" style="display:none;" class="col-md-6 col-md-offset-3">
                            <input type="text" placeholder="<?php echo !empty($langArr['send_text2']) ? $langArr['send_text2'] : "Amount"; ?>" class="form-control" name="setamt" id="setamt" />
                            <input type="submit" onclick="submitClick()" class="btn btn-default" name="submit" value="<?php echo !empty($langArr['confirm']) ? $langArr['confirm'] : "Confirm"; ?>" id="confirm" />
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #history_new .token_history_page {
        text-align: center;
        padding: 10px 0;
    }
    #history_new .token_history_page a {
        width: 100%;
        display: inline-block;
        padding: 10px 0;
        color: #000000;
        font-size: 1.2rem;
        background-color: #FFFFFF;
        border: 1px solid #c0c0c0;
    }
    #history_new .token_history_page a.btn2 {
        margin-top: 10px;
        color: #000000;
        background-color: #ffea61;
        border: 1px solid #ffea61;
    }
</style>

<script>
    function view_message () {
        $("#e_info_msg").removeClass('none');
    }

    /*function myFunctionPop() {
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val($(".showtxtpop").text()).select();
      document.execCommand("copy");
      $temp.remove();
    }

    function showBarcode() {
        $("#myReceive").modal('show');
    }
    */
    function myFunction() {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(".showtxt").text()).select();
        document.execCommand("copy");
        $temp.remove();
    }
    function showReceive(){
        $("#myModalReceive").modal('show');
    }
    function showInputBox(){
        $("#set_amt").toggle();
    }

    function submitClick(){
        var getAmt = $("#setamt").val();
        if(getAmt <=0){
            return false;
        }
        var showSet = "+"+getAmt+" <?php echo strtoupper($tokenName); ?>";
        var barCodeUrl = "<?php echo $barCodeUrl; ?>?amount="+getAmt;
        $("#show_set_amount").html(showSet);
        $("#barcodeimage").attr('src',barCodeUrl);
        $("#set_amt").toggle();
    }



    $(function(){
        get_token_history('1', '');
        //get_token_history();
        $("#loading_history").removeClass('none');
    });
    function get_token_history(page, view_type) {
        $(".token_history_page").addClass('none');

        var waddr = $("#myModalReceive .showtxtpop").text();
        var waddr2 = "<?php echo $row[0]['wallet_address_change']; ?>";
        var token ="<?php echo $tokenName; ?>";
        $.ajax({
            url : 'send.pro.php',
            type : 'POST',
            data : {mode: 'get_token_history2', waddr : waddr, token : token, page : page, waddr2 : waddr2},
            success : function(resp){
                $("#loading_history").addClass('none');
                if (view_type == 'add') {
                    $("#history_new").append(resp);
                } else {
                    $("#history_new").html(resp);
                }
            },
            error : function(resp){
            }
        });

    }
    /*
    function get_token_history() {
        var waddr = $("#myModalReceive .showtxtpop").text();
        var waddr2 = "<?php echo $row[0]['wallet_address_change']; ?>";
	var token ="<?php echo $tokenName; ?>";
	$.ajax({
		url : 'send.pro.php',
		type : 'POST',
		data : {mode: 'get_token_history', waddr : waddr, token : token, waddr2 : waddr2},
		success : function(resp){
			$("#loading_history").addClass('none');
			$("#history_new").html(resp);
		},
		error : function(resp){
		}
	});
}
*/

</script>
<?php
$n_scroll_menu = 't';
include_once('includes/footer.php');
?>
