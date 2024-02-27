<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

use wallet\common\Log as walletLog;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

$filter = walletFilter::getInstance();

//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'token' => 'string',
);

$filterData = $filter->postDataFilter($_GET,$targetPostData);
unset($targetPostData);

if(!isset($filterData['token']) || empty($filterData['token'])){
	header("Location:index.php");
}

if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$log->info('epay 목록 조회',['target_id'=>0,'action'=>'S']);

$walletAddress = '';

$tokenName = $filterData['token'];

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$userEmail = $row[0]['email'];
if ($db->count > 0) {
	$walletAddress = $row[0]['wallet_address'];
	$user_name = '';
	$user_name = get_user_real_name($row[0]['auth_name'], $row[0]['name'], $row[0]['lname']);
}
else {
	header("Location:index.php");
}

// �ܾ���ȸ
$getBalance = new_number_format($row[0]['etoken_'.$tokenName], $n_decimal_point_array2[$tokenName]);

if ( $tokenName == 'ectc' ) {
	$exchange_url = 'exchange_'.$tokenName.'.php';
} else {
	$exchange_url = 'exchange_etoken.php?token='.substr($tokenName, 1);
}
//$exchange_url = 'exchange_'.$tokenName.'.php';
include_once('includes/header.php');

$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$walletAddress;
$show_tokenName = $n_epay_name_array[$tokenName];
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
					<span><?php echo $getBalance.' '.$show_tokenName; ?></span>
				</div>
					<ul>
                        <?php if($_GET['token'] == 'ekrw'){ ?>

                            <li class="send_btn"><a href="send_etoken.php?token=<?php echo $tokenName; ?>"  title="send"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></a></li>
                        <?php }else{ ?>
                            <li class="send_btn"><a href="#" onclick="returnf()" title="send"><?php echo !empty($langArr['send']) ? $langArr['send'] : "Send"; ?></a></li>
                        <?php }?>
                        <!--
                        send_etoken.php?token=<?//php echo $tokenName; ?>
                        -->

						<!--<?php if ( $tokenName == 'ectc' || $tokenName == 'etp3' || $tokenName == 'emc') { ?>
							<li class="send_btn2"><a href="<?php echo $exchange_url; ?>" title="send"><?php echo !empty($langArr['etoken_charge']) ? $langArr['etoken_charge'] : "Charge"; ?></a></li>
						<?php } ?>-->
						<li onClick="showReceive();" class="receive_btn"><?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></li>
					</ul>
					
			</div>
		</div>
<script>
    function returnf(){
        alert("점검중입니다.");
        return;
    }
</script>
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

<!-- Modal -->
<div class="modal fade" id="myModalReceive" role="dialog">
	<div class="modal-dialog">

	  <!-- Modal content-->
	  <div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><img src="images/icons/tmenu_qrcorde.png" alt="barcode" /></button>
			<!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
		  <h4 class="modal-title"><?php echo $show_tokenName; ?></h4>
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

$(function(){
	get_token_history('1','');
	$("#loading_history").removeClass('none');
});
function get_token_history(page, view_type) {
	$(".token_history_page").addClass('none');
	var token ="<?php echo $tokenName; ?>";
	$.ajax({
		url : 'send.pro.php',
		type : 'POST',
		data : {mode: 'get_etoken_history2', token : token, page: page},
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
	var showSet = "+"+getAmt+" <?php echo $show_tokenName; ?>";
	var token = "<?php echo $tokenName; ?>";
	var barCodeUrl = "<?php echo $barCodeUrl; ?>?amount="+getAmt+"|"+token;
	$("#show_set_amount").html(showSet);
	$("#barcodeimage").attr('src',barCodeUrl);
	$("#set_amt").toggle();
}

</script>
<?php
$n_scroll_menu = 't';
include_once('includes/footer.php');
?>
