<?php
// Test Page
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

use wallet\common\Log as walletLog;

require __DIR__ .'/vendor/autoload.php';

if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

$log->info('수수료 CTC 변환 신청 하기 조회',['target_id'=>$row[0]['id'],'action'=>'S']);

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	if ( !empty($_POST['transfer_approved_chk']) ) {
		$updateArr = [] ;
		$updateArr['transfer_approved'] =  'W';
		$updateArr['transfer_approved_date'] =  date("Y-m-d H:i:s");
		$db = getDbInstance();
		$db->where("id", $_SESSION['user_id']);
		$last_id = $db->update('admin_accounts', $updateArr);

        $log->info('수수료 CTC 변환 신청 변경',['target_id'=>$row[0]['id'],'action'=>'E']);

		if( !empty($last_id) ) {
			//$_SESSION['success'] = !empty($langArr['change_fee_message1']) ? $langArr['change_fee_message1'] : "Application completed."; 
			header('location: coupon_buy2.php');
		}  else {
			$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
			header('location: change_fee.php');
		}
	}
   	//header('location: change_fee.php');
	header('location: coupon_buy2.php');
   	exit();
	
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

if ( $row[0]['transfer_approved'] == 'W' ) {


	$db = getDbInstance();
	$db->where('user_id', $_SESSION['user_id']);
	$db->where('coupon_kind', 'fee_change');
	$db->where('status', 'canceled', '!=');
	//$db->where('gstatus', '0'); // 가상계좌 입금기한 만료일 경우가 있기 때문에 결제완료 건만 카운트함
	$db->orderBy('id', 'desc');
	$couponResultData = $db->get('coupon_result');
	//$db->pageLimit = 1;
	//$couponResultData = $db->arraybuilder()->paginate("coupon_result", 1);


	if ( empty($couponResultData) ) { // 결제한적이 없으면 결제페이지로 이동
		header('location: coupon_buy2.php');
		exit();
	}
}

require_once 'includes/header.php'; 
?>

<link  rel="stylesheet" href="css/member.css" />
<style>

#change_fee .app {
	overflow: hidden;
	margin-top: 75px;
}
#change_fee .app p {
	font-size: 1.323rem;
	font-weight:  500;
}
#change_fee .agree_box {
	overflow: scroll;
	-webkit-overflow-scrolling: touch;
	height: 200px;
	width: 100%;
	border: 1px solid #707070; 
	margin-bottom: 40px;
	padding: 5px;
	font-size: 1.176rem;
	color: #919191;
	line-height: 19px;
}

#change_fee .agree_check {
	text-align: center;
	margin-bottom: 28px;
}
#change_fee .agree_check label {
	font-weight:  500;
	font-size: 1.323rem;
}

#change_fee .finish {
	margin-top: 130px;
	overflow: hidden;
}
#change_fee .finish img {
	width: 40px;
	height: auto;
	margin-bottom: 20px;
}
#change_fee .btn {
	margin-top: 0;
	border-radius: 0;
}
#change_fee .finish .btn2 {
	width: 100%;
	font-size: 1.47rem;
	color: #FFFFFF;
	padding: 19px 0;
	background-color: #d7d7d7;
	border: 1px solid #d7d7d7;
	font-weight: 500;
	margin-top: 105px;
}

#change_fee .finish .text {
	font-size: 2.205rem;
	line-height: 120%;
}
#change_fee .bold {
	font-weight: bold;
}
#change_fee .finish .text2 {
	font-size: 1.47rem;
	margin-top: 10px;
}


.payment {
	margin-top: 20px;
	border: 1px solid #ddd;
	padding: 10px 20px;
}
.payment .btn {
	margin-top: 20px !important;
}
.receipt_btn {
	white-space: nowrap;
	border: 1px solid #DDDDDD;
	padding: 5px;
	margin-left: 10px;
	display: inline-block;
}
.payment_sub {
	padding: 10px 0;
	border-bottom: 1px solid #DDDDDD;
}
.payment_sub:last-child {
	border-bottom: none;
}
</style>

<div id="page-wrapper">
	<div id="change_fee" class="member_common">
		
		<?php include('./includes/flash_messages.php') ?>

		<div class="tab-content" >
			<div class="col-md-3"></div>
			<div class="col-md-6 tab-pane fade in active" >

				<!--<p class="profile_subject"><?php echo !empty($langArr['change_fee_subject']) ? $langArr['change_fee_subject'] : "application for conversion of CTC fees"; ?></p>-->

				
					<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
					
						<?php if ( $row[0]['transfer_approved'] == 'E' ) { ?>
							<div class="app">
								<p><?php echo !empty($langArr['change_fee_agree_subject1']) ? $langArr['change_fee_agree_subject1'] : "Fee conversion terms and conditions"; ?></p>
								<div class="agree_box">
수수료변환 이용약관<br /><br />

제1조 【목적】<br />
이 『수수료변환 이용 약관』(이하 약관이라 함)은 서비스의 제공자인 ㈜한가족몰(이하 ctc월렛이라 함)과 이용자인 고객간에 수수료변환 이용에 관한 제반 사항을 정함을 목적으로 합니다.<br /><br />

제2조 【서비스와 이용업무】<br />
서비스의 종류는 코인이동에제공되는 수수료변환으로 구분하며 제공되는 서비스는 다음과 같습니다.<br />
1. 본서비스는 현재 사용하고 있는 ETH코인 수수료를 CTC 코인으로 교체 합니다.<br /><br />

제3조 【서비스 이용매체】<br />
서비스별로 이용할 수 있는 매체는 다음과 같습니다.<br />
1. CTC월렛<br /><br />

제4조 【서비스의 개시 및 종료】<br />
① 고객은 『수수료변환 이용 약관』모두 동의하여 당사에서 이를 승낙한 후 고객이 직접 각종 비밀번호를 입력함으로써 개시됩니다. <br />
② 고객은 이용 업무에 따라 별도의 약정이 필요한 경우에는 별도의 개별 약정을 체결하셔야 합니다.<br />
③ 고객이 서비스 이용을 중단하고자 할 경우에는 해지할수 없으며 가입탈퇴 신청을 하셔야 합니다.<br /><br />

제5조 【본인 확인방법】<br />
휴대폰본인인증<br /><br />

제6조 【서비스 이용시간】<br />
고객은 서비스별로 당사가 정한 시간 이내에서 서비스를 이용할 수 있으며, 이용시간은 당사의 전산시스템 운용 사정에 따라 달라질 수 있습니다.<br /><br />

제7조 【서비스 이용수수료】<br />
① 수수료의 요율 및 계산방법은 당행에서 정한 바에 따르기로 합니다.<br />
② 수수료는 서비스의 실행과 동시에 또는 당일실시간 중 실제 발생된 건수를 기준으로 ETH서버의 수수료부가의 기준 산정비율로 지정한다. 거래 실시간 고객의 출금계좌에서 자동 출금합니다.<br /><br />

제8조 【코인이체】<br />
① 즉시이체<br />
   1. 코인이체는 지갑의 이용매체를 조작하면 코인주소에 등록한 고객명의의 출금주소에서 이체주소로 이체합니다.<br />
   2. 코인이체서비스를 받으신 후의 거래내용은 정정 또는 취소하실 수 없으며, 고객의 부주의로 인하여 잘못된 거래내용은 고객의 책임으로 합니다.
								</div>
								<fieldset>
									<div class="agree_check">
										<input type="checkbox" name="transfer_approved_chk" id="transfer_approved_chk" value="C" required />
										<label for="transfer_approved_chk"><?php echo !empty($langArr['change_fee_agree']) ? $langArr['change_fee_agree'] : "I agree"; ?></label>
									</div> 

									<div class="text-center">
										<button type="submit" class="btn" ><?php echo !empty($langArr['change_fee_btn']) ? $langArr['change_fee_btn'] : "Application"; ?></button>
									</div>            
								</fieldset>
							</div>
						<?php } else if ( $row[0]['transfer_approved'] == 'C' ) { ?>
							<div class="finish">
								<img src="images/icons/change_fee_icon.png" alt="fee" />
								<div class="text">
									<span><?php echo !empty($langArr['change_fee_text1']) ? $langArr['change_fee_text1'] : "You are already using"; ?></span>
									<span class="bold" ><?php echo !empty($langArr['change_fee_text2']) ? $langArr['change_fee_text2'] : "CTC"; ?></span><span><?php echo !empty($langArr['change_fee_text3']) ? $langArr['change_fee_text3'] : " as a commission."; ?></span>
								</div>

								<div class="text-center">
									<div class="btn2" ><?php echo !empty($langArr['change_fee_btn3']) ? $langArr['change_fee_btn3'] : "Approve completed"; ?></div>
								</div>
							</div>
						<?php } else if ( $row[0]['transfer_approved'] == 'W' ) {
							?>
								
							<div class="finish">
								<img src="images/icons/change_fee_icon.png" alt="fee" />
								<div class="text">
									<span><?php echo !empty($langArr['change_fee_message1']) ? $langArr['change_fee_message1'] : "Application completed."; ?></span>
								</div>
								<div class="text-center">
									<div class="btn2" ><?php echo !empty($langArr['change_fee_btn2']) ? $langArr['change_fee_btn2'] : "Application completed"; ?></div>
								</div>
							</div>

							<div class="payment">
								<?php
								$p = 0;
								foreach($couponResultData as $row) {
									$db = getDbInstance();
									$db->where('order_num',  $row['payment_id']);
									$row2 = $db->getOne('kcp_order');
									
									$receipt_url = '';
									$receipt_url_lang = '';
									if ( $_SESSION['lang'] == 'en' ) {
										$receipt_url_lang = '_eng';
									}
									switch($row2['order_pay']) {
										case '카드결제':
											$receipt_url = 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=card_bill'.$receipt_url_lang.'&tno='.$row2['tno'].'&order_no='.$row2['order_num'].'&trade_mony='.$row2['order_amount'];
											$width = 470;
											$height = 815;
											break;
										case '계좌이체':
											$receipt_url = 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=acnt_bill'.$receipt_url_lang.'&tno='.$row2['tno'].'&order_no='.$row2['order_num'].'&trade_mony='.$row2['order_amount'];
											$width = 470;
											$height = 695;
											break;
										case '가상계좌':
											$receipt_url = 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=vcnt_bill'.$receipt_url_lang.'&tno='.$row2['tno'].'&order_no='.$row2['order_num'].'&trade_mony='.$row2['order_amount'];
											$width = 470;
											$height = 695;
											break;
									}
									
									?><div class="payment_sub"><?php
									echo '['.new_pay_type_change($_SESSION['lang'], $row2['order_pay']).']';
									?><a href="javascript:;" onclick="window.open('<?php echo $receipt_url; ?>','receipt','width=<?php echo $width; ?>,height=<?php echo $height; ?>');" class="receipt_btn" ><?php echo !empty($langArr['coupon_payment_receipt1']) ? $langArr['coupon_payment_receipt1'] : "Receipt"; ?> <?php echo !empty($langArr['confirm']) ? $langArr['confirm'] : "Confirm"; ?></a><br /><?php

									if($row['gstatus']!="0"){
										if ( $row2['gva_date'] >= date("Y-m-d H:i:s") ) {
											echo !empty($langArr['coupon_list_text3']) ? $langArr['coupon_list_text3'] : "It is before deposit.";
											?>
											<br /><span><?=$row2['gbank_name']?></span> (<?=$row2['gaccount']?>)
											<br /><?php echo !empty($langArr['coupon_list_text4']) ? $langArr['coupon_list_text4'] : "Depositor"; ?> : <?=$row2['gdepositor']?>
											<br /><?php echo !empty($langArr['coupon_list_text5']) ? $langArr['coupon_list_text5'] : "Deposit amount"; ?> : <span class="virtual_account_font1"><?=number_format($row2['order_amount'],0)?></span> <?php echo !empty($langArr['krw_unit']) ? $langArr['krw_unit'] : "WON"; ?>
											<br /><?php echo !empty($langArr['coupon_list_text6']) ? $langArr['coupon_list_text6'] : "Deposit deadline"; ?> : <span class="virtual_account_font2"><?=$row2['gva_date']?></span>

											<?php
											$p = $p + 1;
										} else {
											echo !empty($langArr['coupon_list_text13']) ? $langArr['coupon_list_text13'] : "The deposit deadline has passed.";
											echo !empty($langArr['coupon_buy_message7']) ? $langArr['coupon_buy_message7'] : "Please pay again.";
											?>
											<br /><?php echo !empty($langArr['coupon_list_text6']) ? $langArr['coupon_list_text6'] : "Deposit deadline"; ?> : <span class="virtual_account_font2"><?=$row2['gva_date']?></span>
											<?php
										}
									} else {
										$p = $p + 1;
									}
									?></div><?php
								}

								if ( $p == 0 ) { // 결제 성공한게 있으면 결제버튼 안보여도 됨
									?><br /><a href="coupon_buy2.php" alt="payment" class="btn"><?php echo !empty($langArr['coupon_list_btn2']) ? $langArr['coupon_list_btn2'] : "Payment"; ?></a><?php
								}
								?>
								

							</div><?php
									
						} ?>
					</form>

			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
/*$(document).ready(function(){
	$(".form").submit(function(){
		if ($("#transfer_approved_chk").prop("checked") == false ) {
		//if ($("#transfer_approved_chk").is(":checked") == true ) {
			return false;
		}
		
	});
});
*/
</script>


<?php include_once 'includes/footer.php'; ?>