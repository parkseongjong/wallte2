<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

// Sanitize if you want
$coupon_result_id = filter_input(INPUT_GET, 'coupon_result_id', FILTER_SANITIZE_STRING);
$operation = filter_input(INPUT_GET, 'operation',FILTER_SANITIZE_STRING); 
($operation == 'edit') ? $edit = true : $edit = false;


 
  //Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
}

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	
	if($_SESSION['admin_type']!='admin'){
		 $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
	}
	
    $coupon_result_id = filter_input(INPUT_GET, 'coupon_result_id', FILTER_SANITIZE_STRING);

    $db = getDbInstance();
    $db->where('id', $coupon_result_id);
	$updateArr = [] ;
	$updateArr['etc'] =  htmlspecialchars(addslashes($_POST['etc']));
    $stat = $db->update('coupon_result', $updateArr);

    if($stat)
    {
        $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 수수료 변환신청 결제 목록 > Message > Message 수정',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$coupon_result_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
        $_SESSION['success'] = "Message updated successfully!";
        //Redirect to the listing page,
		if ( !empty($_POST['queries']) ) {
			header('location: admin_users_fee_payment.php?'.$_POST['queries']);
		} else {
			header('location: admin_users_fee_payment.php');
		}
        //Important! Don't execute the rest put the exit/die. 
        exit();
    }
}
else{
    $walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 수수료 변환신청 결제 목록 > Message > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}

$id_auth = '';
//If edit variable is set, we are performing the update operation.
if($edit)
{
	$db = getDbInstance();
    $db->where('id', $coupon_result_id);
    $coupon_infos = $db->getOne("coupon_result");
}

?>


<?php
    include_once 'includes/header.php';
?>
   <!-- MetisMenu CSS -->
        <link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
		 <script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 
<div id="page-wrapper">
    <div class="row">
        <h2 class="page-header">Update Message</h2>
    </div>
    <!-- Flash messages -->
    <?php
	include('./includes/flash_messages.php');
	?>

    <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
		<?php
		if ( !empty($_GET) ) {
			$tmp = $_GET;
			unset($tmp['coupon_result_id']);
			unset($tmp['operation']);
			$tmp = http_build_query($tmp);
		}
		?>
		<input type="hidden" name="queries" value="<?php echo $tmp; ?>" />
        
       <fieldset>
			<div class="form-group">
				<label for="etc">Message</label>
				<input type="text" name="etc" value="<?php echo $edit ? $coupon_infos['etc'] : ''; ?>" placeholder="Message" class="form-control" id="etc" maxlength="150">
			</div> 
		
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
			</div>
		</fieldset>
    </form>
</div>


<?php include_once 'includes/footer.php'; ?>