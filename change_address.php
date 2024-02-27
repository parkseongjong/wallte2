<?php
// Test Page
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$wallet_change_apply = $row[0]['wallet_change_apply'];

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	if ( !empty($_POST['wallet_change_apply_chk']) ) {
		$updateArr = [] ;
		$updateArr['wallet_change_apply'] =  'W';
		$updateArr['wallet_change_apply_at'] =  date("Y-m-d H:i:s");
		$db = getDbInstance();
		$db->where("id", $_SESSION['user_id']);
		$last_id = $db->update('admin_accounts', $updateArr);
		
		if( !empty($last_id) ) {
			$_SESSION['success'] = !empty($langArr['change_fee_message1']) ? $langArr['change_fee_message1'] : "Application completed."; 
		}  else {
			$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : "Some error are occurred"; 
		}
	}
   	header('location: change_address.php');
   	exit();
	
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>

<link  rel="stylesheet" href="css/member.css" />
<style>


#change_address .finish {
	margin-top: 130px;
	overflow: hidden;
}
#change_address .finish img {
	width: 40px;
	height: auto;
	margin-bottom: 20px;
}
#change_address .btn {
	margin-top: 105px;
	border-radius: 0;
}
#change_address .finish .btn2 {
	width: 100%;
	font-size: 1.47rem;
	color: #FFFFFF;
	padding: 19px 0;
	background-color: #d7d7d7;
	border: 1px solid #d7d7d7;
	font-weight: 500;
	margin-top: 105px;
}

#change_address .finish .text {
	font-size: 2.205rem;
	line-height: 120%;
}
#change_address .bold {
	font-weight: bold;
}
#change_address .finish .text2 {
	font-size: 1.47rem;
	margin-top: 10px;
}

</style>
<div id="page-wrapper">
	<div id="change_address" class="member_common">
		
		<?php include('./includes/flash_messages.php') ?>

		<div class="tab-content" >
			<div class="col-md-3"></div>
			<div class="col-md-6 tab-pane fade in active" >

				<!--<p class="profile_subject"><?php echo !empty($langArr['change_fee_subject']) ? $langArr['change_fee_subject'] : "application for conversion of CTC fees"; ?></p>-->

				
					<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
						
						<?php if ( $wallet_change_apply == 'N' ) { // 신청 전 ?>
							<div class="finish">
								<div class="text">
									<span><?php echo !empty($langArr['change_address_message2']) ? $langArr['change_address_message2'] : "Get my wallet address."; ?></span>
								</div>
								<input type="hidden" name="wallet_change_apply_chk" value="W" />
								
								<div class="text-center">
									<button type="submit" class="btn" ><?php echo !empty($langArr['change_address_btn2']) ? $langArr['change_address_btn2'] : "Get"; ?></button>
								</div>
							</div>
						<?php } else if ( $wallet_change_apply == 'Y' ) { // 변경완료 ?>
							<div class="finish">
								<img src="images/icons/change_fee_icon.png" alt="fee" />
								<div class="text">
									<span><?php echo !empty($langArr['change_address_message1']) ? $langArr['change_address_message1'] : "Change has been completed."; ?></span>
								</div>

								<div class="text-center">
									<div class="btn2" ><?php echo !empty($langArr['change_address_btn1']) ? $langArr['change_address_btn1'] : "change completion"; ?></div>
								</div>
							</div>
						<?php } else if ( $wallet_change_apply == 'W' ) { // 신청완료 ?>
							<div class="finish">
								<img src="images/icons/change_fee_icon.png" alt="fee" />
								<div class="text">
									<span><?php echo !empty($langArr['change_fee_message1']) ? $langArr['change_fee_message1'] : "Application completed."; ?></span>
								</div>
								<div class="text-center">
									<div class="btn2" ><?php echo !empty($langArr['change_fee_btn2']) ? $langArr['change_fee_btn2'] : "Application completed"; ?></div>
								</div>
							</div>
						<?php } ?>
					</form>

			</div>
		</div>
	</div>
</div>

<?php include_once 'includes/footer.php'; ?>