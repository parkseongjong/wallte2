<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}
//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    $db = getDbInstance();
	$insertArr = [] ;
	$insertArr['kind'] =  $_POST['kind'];
	$insertArr['coin_type'] =  $_POST['coin_type'];
	$insertArr['name'] =  $_POST['name'];
	$insertArr['amount'] =  $_POST['amount'];
	$insertArr['coin_amount'] =  $_POST['coin_amount'];
	$insertArr['used'] =  $_POST['used'];
	$last_id = $db->insert('coupon_list', $insertArr);
    
    if($last_id)
    {
    	$_SESSION['success'] = "Coupon Addeded Successfully";
        $walletLogger->info('관리자 모드 > 쿠폰 목록 > 쿠폰 추가 고유 ID : '.$last_id,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'A']);
    	header('location: admin_coupon_config.php');
    	exit();
    }  
}
else{
    $walletLogger->info('관리자 모드 > 쿠폰 목록 > 쿠폰 추가 페이지 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>

<link  rel="stylesheet" href="css/admin.css"/>

<div id="page-wrapper">
	<div class="row">
		     <div class="col-lg-12">
				<h2 class="page-header">Add Coupon</h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
    <div class="col-md-3"></div>
    <div class="col-md-6">
	<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">

		<fieldset>
			<div class="form-group">
				<label><?php echo !empty($langArr['coupon_list_text8']) ? $langArr['coupon_list_text8'] : "Kind"; ?></label>				
				<input type="radio" name="kind" value="fee" id="kind_fee" checked /><label for="kind_fee"><?php echo !empty($langArr['coupon_adm_text1']) ? $langArr['coupon_adm_text1'] : "Fees"; ?></label>
				<input type="radio" name="kind" value="fee_change" id="kind_fee_change" /><label for="kind_fee_change"><?php echo !empty($langArr['change_fee_text4']) ? $langArr['change_fee_text4'] : "Fee conversion"; ?></label>
			</div> 
			<div class="form-group">
				<label>Coin</label>
				<input type="radio" name="coin_type" value="ectc" id="coin_type_ectc" checked /><label for="coin_type_ectc">eCTC</label>
				<input type="radio" name="coin_type" value="none" id="coin_type_none" /><label for="coin_type_none">Not Used</label>
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['coupon_adm_text2']) ? $langArr['coupon_adm_text2'] : "Coupon name"; ?></label>
				  <input type="text" name="name"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "name" placeholder="2만원권" />
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['coupon_adm_text3']) ? $langArr['coupon_adm_text3'] : "Amount of payment(unit: WON)"; ?></label>
				  <input type="number" name="amount"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "amount">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['coupon_adm_text4']) ? $langArr['coupon_adm_text4'] : "Coins to be received"; ?></label>
				  <input type="number" name="coin_amount"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "coin_amount">
			</div> 
			<div class="form-group">
				<input type="radio" name="used" value="Y" id="used_y" checked /><label for="used_y"><?php echo !empty($langArr['coupon_adm_text5']) ? $langArr['coupon_adm_text5'] : "Used"; ?></label>
				<input type="radio" name="used" value="N" id="used_n" /><label for="used_n"><?php echo !empty($langArr['coupon_adm_text6']) ? $langArr['coupon_adm_text6'] : "not used"; ?></label>
			</div> 
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning submit-button" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?> <span class="glyphicon glyphicon-send"></span></button>
			</div>            
		</fieldset>
	</form>
</div></div>

<?php include_once 'includes/footer.php'; ?>