<?php
// Page in use : copy (token.php) 21.02.23 12:25 => admin only
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;
use wallet\common\Info as walletInfo;

require __DIR__ .'/vendor/autoload.php';

if(!isset($_GET['token']) || empty($_GET['token'])){
	header("Location:index.php");
}
if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}
if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:index.php');
}


require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;


$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/'); // Changed it to set it at once on that page : config/new_config.php
$eth = $web3->eth;


$walletAddress = '';
$walletAddress_old = '';
$user_name = '';

$tokenName = $_GET['token'];
$user_id = $_GET['user_id'];

// for check walletAddress is empty or not start 
$db = getDbInstance();
$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$walletLogger->info('COIN 조회!/token:'.$tokenName,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$user_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
$db->where("id", $user_id);
$row = $db->get('admin_accounts');
$userEmail = $row[0]['email'];
if ($db->count > 0) {
	$walletAddress = $row[0]['wallet_address'];
	$walletAddress_old = $row[0]['wallet_address_change'];
	$user_name = get_user_real_name($row[0]['auth_name'], $row[0]['name'], $row[0]['lname']);
}
else
{
	return;
	exit;
}


// 잔액조회 부분
//require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new walletInfo();
$getBalance = $wi_wallet_infos->wi_get_balance('2', $tokenName, $walletAddress, $contractAddressArr);
$getBalance = new_number_format($getBalance,$n_decimal_point_array[$tokenName]); 

// for check walletAddress is empty or not end

include_once('includes/header.php');

//$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=ethereum:".$walletAddress;
$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$walletAddress;

?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>
<div id="page-wrapper">
	<div id="token">		
		<div class="alert alert-info alert-dismissable none" id="e_info_msg"><a href="#" class="close" data-dismiss="alert" aria-label="close">x</a><?php echo !empty($langArr['plz_use_e_coin_message1']) ? $langArr['plz_use_e_coin_message1'] : "Can't be transferred. Please use e-Pay."; ?></div>

		<div class="history_top">
			<div class="top">
				<p><?php echo $user_name; ?><?php echo !empty($langArr['token_history_text6']) ? $langArr['token_history_text6'] : "'s Balance"; ?></p>
				<div class="top_amount">
					<div class="img2"><div><img src="images/logo2/<?php echo $tokenName; ?>.png" alt="<?php echo $tokenName; ?>" /></div></div>
					<span><?php echo $getBalance.' '.strtoupper($tokenName); ?></span>
				</div>
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
		</div>
		<!-- /.row -->

	</div>
</div>
<!-- /#page-wrapper -->


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




$(function(){
	get_token_history('1', '');
	$("#loading_history").removeClass('none');
});
function get_token_history(page, view_type) {
	$(".token_history_page").addClass('none');

	var waddr = "<?php echo $walletAddress; ?>";
	var waddr2 = "<?php echo $walletAddress_old; ?>";
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

</script>
<?php
$n_scroll_menu = 't';
include_once('includes/footer.php');
?>
