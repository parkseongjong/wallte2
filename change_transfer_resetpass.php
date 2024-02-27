<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

use wallet\common\Log as walletLog;
use wallet\common\Filter as walletFilter;

require __DIR__ .'/vendor/autoload.php';

if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}

//2021-07-31 LOG 기능 추가 By.OJT
$log = new walletLog();


$log->info('프로필 > 결제 비밀번호 초기화 조회',['target_id'=>0,'action'=>'S']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = walletFilter::getInstance();

    //2021-11-09 XSS Filter by.ojt
    $targetPostData = array(
        'vcode' => 'string',
    );

    $filterData = $filter->postDataFilter($_POST,$targetPostData);
    unset($targetPostData);

	$getVode = $filterData['vcode'];
    
	if(empty($getVode)){
		$_SESSION['failure'] = !empty($langArr['invalid_verification_code']) ? $langArr['invalid_verification_code'] : 'Invalid Verification Code';
		header('Location:change_transfer_resetpass.php');
		exit;
	}
	
	$db = getDbInstance();
    $db->where ("id", $_SESSION['user_id']);
    $db->where ("vcode", $getVode);
    $row = $db->get('admin_accounts');
	if($db->count == 0) {
		$_SESSION['failure'] = !empty($langArr['invalid_verification_code']) ? $langArr['invalid_verification_code'] : 'Invalid Verification Code';
		header('Location:change_transfer_resetpass.php');
		exit;		
	}
	
	$db = getDbInstance();
	$db->where ("id", $_SESSION['user_id']);
	$db->where("vcode", $getVode);
	
	$last_id = $db->update('admin_accounts', ['vcode'=>"",'transfer_passwd'=>NULL]);
    $log->info('프로필 > 결제 비밀번호 초기화 > SMS CODE 확인 > 결제 비밀번호 초기화 처리',['target_id'=>0,'action'=>'E']);
	if ( $last_id ) {
		header('location: set_transferpw_frm.php');
		exit();
	} else {
		$_SESSION['failure'] = !empty($langArr['profile_err_occurred']) ? $langArr['profile_err_occurred'] : 'Some error are occurred';
		header('Location:change_transfer_resetpass.php');
		exit();
	}
}

include_once 'includes/header.php';
?>


<link  rel="stylesheet" href="css/member.css"/>

<div id="page-wrapper">
	<div id="change_transfer_resetpass" class="member_common">

		<form method="POST" action="">

			<div class="login-panel panel-default">
				<?php
				include "includes/flash_messages.php";
				?>
				<div class="panel-body">
					
					<div class="form-group">
						<label class="control-label"><?php echo !empty($langArr['enter_verification_code']) ? $langArr['enter_verification_code'] : "Enter Verification Code"; ?></label>
						<input type="text" id="vcode"  name="vcode" title="<?php echo $langArr['this_field_is_required']; ?>" class="input" required="required" />
					</div>
									
					<button type="submit" class="btn" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?></button>
				</div>
			</div>
		</form>
	</div>
</div>

	
<?php include_once 'includes/footer.php'; ?>
