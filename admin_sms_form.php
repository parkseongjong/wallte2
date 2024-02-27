<?php
// Page in use : SMS 발송
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:index.php');
}
include_once 'includes/header.php';

$type = !empty($_GET['type']) ? $_GET['type'] : 'sms';

if ( !empty($_GET['user_id']) ) {
	$db = getDbInstance();
	$db->where('id', $_GET['user_id']);
	$userData = $db->getOne('admin_accounts', 'id, n_country, n_phone, auth_name, name, lname, onesignal_id, onesignal_id2');
	$name = get_user_real_name($userData['auth_name'], $userData['name'], $userData['lname']);
}

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);
?>

<link href="css/admin.css?ver=1.0" rel="stylesheet">
<script src="js/sms.js" type="text/javascript"></script>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if ( $_POST['type'] == 'sms' ) {

		require_once BASE_PATH.'/lib/SendMail.php';
		$wi_send_mail = new SendMail();

		$country = $_POST['country'];
		$phone = $_POST['phone'];
		$contents = $_POST['contents'];
		
		
		if ( !empty($country) && !empty($phone) && !empty($contents) ) {
			$result_sms = $wi_send_mail->send_sms ($country, $phone, $contents);
			if ( !empty($result_sms) ) {

				$insertArr = [];
				if ( !empty($_POST['user_id']) ) {
					$insertArr['user_id'] = $_POST['user_id'];
				}
				$insertArr['country_code'] = $country;
				$insertArr['phone_number'] = $phone;
				$insertArr['contents'] = htmlspecialchars($contents);
				$insertArr['created'] = date("Y-m-d H:i:s");
				$db = getDbInstance();
				$last_id = $db->insert('send_sms_logs', $insertArr);

				$_SESSION['success'] = 'Send success';
			} else {
				$_SESSION['failure'] = 'Send failure';
			}
		}
	} else {
		// push
		$title = array('ko' => $_POST['title_ko'], 'en' => $_POST['title_en']);
		$contents = array('ko' => $_POST['contents_ko'], 'en' => $_POST['contents_en']);
		
		if ( !empty($_POST['site']) && !empty($_POST['onesignal_value']) ) {
			$return_response = sendPushMessage2($_POST['site'], $title, $contents, $_POST['onesignal_value']);
		}

	}
    $walletLogger->info('관리자 모드 > Send SMS, Push > 발송 / 종류 : '.$_POST['type'],['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userData['id'],'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
	unset($_POST);

}
else{
    $walletLogger->info('관리자 모드 > Send SMS, Push form > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$userData['id'],'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}



?>
<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-6">
			<h1 class="page-header">Send SMS / Push</h1>
		</div>
	</div>
	 <?php include('./includes/flash_messages.php') ?>

	<div id="admin_sms_form">
		
		<?php if ( $type == 'sms' && !empty($_GET['user_id']) ) { ?>
			<p>※ <?php echo !empty($langArr['admin_sms_form_text']) ? $langArr['admin_sms_form_text'] : "Please check phone number before sending"; ?></p>
			<p><a href="admin_sms_form.php" title="sms send"><?php echo !empty($langArr['admin_sms_form_text2']) ? $langArr['admin_sms_form_text2'] : "Directly enter and send phone number"; ?></a></p>
		<?php } ?>

		<form method="post" name="send_sms">
			
			<input type="hidden" name="user_id" value="<?php echo !empty($_GET['user_id']) ? $_GET['user_id'] : ''; ?>" />
			<input type="hidden" name="type" value="<?php echo !empty($_GET['type']) ? $_GET['type'] : ''; ?>" />
			
			<?php
			if ( !empty($name) ) {
				echo '<p>';
				echo !empty($langArr['name']) ? $langArr['name'] : "Name";
				echo ' : '.$name.'</p>';
			}
			
			if ( $type == 'sms' ) {
				?>

				<label for="country"><?php echo !empty($langArr['admin_sms_form_country']) ? $langArr['admin_sms_form_country'] : "Country Code"; ?></label>
				<input type="number" name="country" id="country" value="<?php echo !empty($userData['n_country']) ? $userData['n_country'] : '82'; ?>" required /><br />

				<label for="phone"><?php echo !empty($langArr['admin_sms_form_phone']) ? $langArr['admin_sms_form_phone'] : "Phone Number"; ?></label>
				<input type="text" name="phone" id="phone" placeholder="Phone Number" value="<?php echo !empty($userData['n_phone']) ? $userData['n_phone'] : ''; ?>"required /><br />

				<label for="contents"><?php echo !empty($langArr['admin_sms_form_message']) ? $langArr['admin_sms_form_message'] : "Message"; ?></label><br />
				<textarea name="contents" id="contents" rows="10" onKeyUp="adm_updateChar(send_sms.contents, contents_byte);" required placeholder="안녕하세요 CyberTChain입니다.
여기에 메시지를 적어주세요.
최대 80 Byte 까지 입력 가능합니다."></textarea><br />
				<div class="contents_byte"><span id="contents_byte"></span> / 80 Byte</div>

				<input type="submit" value="<?php echo !empty($langArr['token_history_text1']) ? $langArr['token_history_text1'] : "Send"; ?>" />
			<?php } else {
	
				if ( !empty($userData['onesignal_id']) ) { ?>
					<input type="radio" name="site" value="wallet" id="site_1" checked required onChange="admin_push('<?php echo $userData['onesignal_id']; ?>')" /><label for="site_1">[Wallet]<br /><?php echo $userData['onesignal_id']; ?></label>
					<?php if ( !empty($userData['onesignal_id2']) ) { ?>
						<input type="radio" name="site" value="barrybarries" id="site_2" required onChange="admin_push('<?php echo $userData['onesignal_id2']; ?>')" /><label for="site_2">[Barrybarries]<br /><?php echo $userData['onesignal_id2']; ?></label>
					<?php } ?>


					<label for="title_ko">Title (Korean)</label><br />
					<input type="text" name="title_ko" id="title_ko" required value="CTC Wallet" /><br />

					<label for="title_en">Title (English)</label><br />
					<input type="text" name="title_en" id="title_en" required value="CTC Wallet" /><br />
		
					<label for="contents_ko"><?php echo !empty($langArr['admin_sms_form_message']) ? $langArr['admin_sms_form_message'] : "Message"; ?> (Korean)</label><br />
					<textarea name="contents_ko" id="contents_ko" rows="5" required placeholder="Push Message"></textarea><br />

					<label for="contents_en"><?php echo !empty($langArr['admin_sms_form_message']) ? $langArr['admin_sms_form_message'] : "Message"; ?> (English)</label><br />
					<textarea name="contents_en" id="contents_en" rows="5" required placeholder="Push Message"></textarea><br />

					<input type="text" name="onesignal_value" id="onesignal_value" required value="<?php echo $userData['onesignal_id']; ?>" />

					<input type="submit" value="<?php echo !empty($langArr['token_history_text1']) ? $langArr['token_history_text1'] : "Send"; ?>" />
					<?php
				} else {
					?><p>전송불가</p><?php
				}
				?>

			<?php } ?>
			
			
		</form>


	</div>
</div>


<?php 
include_once 'includes/footer.php'; ?>
