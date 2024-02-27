<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

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


$walletAddress = '';

$tokenName = $_GET['token'];
$user_id = $_GET['user_id'];
$user_type = $_GET['user_type'];

$db = getDbInstance();
$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$walletLogger->info('e-COIN 조회!/token:'.$tokenName.'/type:'.$user_type,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$user_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
$db->where("id", $user_id);
$row = $db->get('admin_accounts');
$userEmail = $row[0]['email'];
if ($db->count > 0) {
	$walletAddress = $row[0]['wallet_address'];
	if ( $user_type == 'virtual' && !empty($row[0]['virtual_wallet_address'])) {
		$walletAddress = $row[0]['virtual_wallet_address'];
	}
	$user_name = '';
	$user_name = get_user_real_name($row[0]['auth_name'], $row[0]['name'], $row[0]['lname']);
	if ( $user_type == 'virtual' ) {
		$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
		$user_name .= $virtual_account_tx1;
	}
}
else {
	header("Location:index.php");
}

// 잔액조회
$getBalance = new_number_format($row[0]['etoken_'.$tokenName], $n_decimal_point_array2[$tokenName]);

include_once('includes/header.php');

$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$walletAddress;

?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>
<div id="page-wrapper">
	<?php include('./includes/flash_messages.php') ?>

	<div id="token">

		<div class="history_top">
			<div class="top">
				<p><?php echo $user_name; ?><?php echo !empty($langArr['token_history_text6']) ? $langArr['token_history_text6'] : "'s Balance"; ?></p>
				<div class="top_amount">
					<!--<div class="img"><div><img src="images/logo/<?php echo $tokenName; ?>.png" alt="<?php echo $tokenName; ?>" /></div></div>-->
					<div class="img2"><div><img src="images/logo2/<?php echo $tokenName; ?>.png" alt="<?php echo $tokenName; ?>" /></div></div>
					<span><?php if ( $user_type != 'virtual' ) {  echo $getBalance.' '.$n_epay_name_array[$tokenName]; } else { echo $n_epay_name_array[$tokenName]; } ?></span>
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

$(function(){
	get_token_history('1','');
	$("#loading_history").removeClass('none');
});
function get_token_history(page, view_type) {
	$(".token_history_page").addClass('none');
	var token ="<?php echo $tokenName; ?>";
	var user_id = "<?php echo $user_id; ?>";
	var user_type = "<?php echo $user_type; ?>";
	$.ajax({
		url : 'send.pro.php',
		type : 'POST',
		data : {mode: 'get_etoken_history3', token : token, page: page, user_id: user_id, user_type: user_type},
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
