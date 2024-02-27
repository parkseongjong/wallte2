<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

// Sanitize if you want
$get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation',FILTER_SANITIZE_STRING); 
$page = filter_input(INPUT_GET, 'page ',FILTER_VALIDATE_INT); 

($operation == 'edit') ? $edit = true : $edit = false;
$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

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
	//Get customer id form query string parameter.
    $get_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);

    $db = getDbInstance();
	$db->where('id', $get_id);
	$insertArr = [] ;
	$insertArr['kind'] =  $_POST['kind'];
	$insertArr['coin_type'] =  $_POST['coin_type'];
	$insertArr['name'] =  $_POST['name'];
	$insertArr['amount'] =  $_POST['amount'];
	$insertArr['coin_amount'] =  $_POST['coin_amount'];
	$insertArr['used'] =  $_POST['used'];
	$last_id = $db->update('coupon_list', $insertArr);
    
    if($last_id)
    {
    	$_SESSION['success'] ="Coupon Updated Successfully";
        $walletLogger->info('관리자 모드 > 쿠폰 목록 > 쿠폰 수정 / 고유 ID : '.$get_id,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
    	header('location: admin_coupon_config.php?page='.$page);
    	exit();
    }  
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
//If edit variable is set, we are performing the update operation.
if($edit)
{
    $db->where('id',$get_id);
    //Get data to pre-populate the form.
    $getData = $db->getOne("coupon_list");
}

require_once 'includes/header.php'; 
?>
<link  rel="stylesheet" href="css/admin.css"/>

<div id="page-wrapper">
	<div class="row">
		     <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['edit_Store']) ? $langArr['edit_Store'] : "Edit Store"; ?></h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
    <div class="col-md-3"></div>
    <div class="col-md-6">
	<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
		<fieldset>

			<div class="form-group">
				<label><?php echo !empty($langArr['coupon_list_text8']) ? $langArr['coupon_list_text8'] : "Kind"; ?></label>				
				<input type="radio" name="kind" value="fee" id="kind_fee" <?php if ( $getData['kind'] == 'fee' ) echo 'checked'; ?> /><label for="kind_fee"><?php echo !empty($langArr['coupon_adm_text1']) ? $langArr['coupon_adm_text1'] : "Fees"; ?></label>
				<input type="radio" name="kind" value="fee_change" id="kind_fee_change" <?php if ( $getData['kind'] == 'fee_change' ) echo 'checked'; ?> /><label for="kind_fee_change"><?php echo !empty($langArr['change_fee_text4']) ? $langArr['change_fee_text4'] : "Fee conversion"; ?></label>
			</div>
			<div class="form-group">
				<label>Coin</label>
				<input type="radio" name="coin_type" value="ectc" id="coin_type_ectc" <?php if ( $getData['coin_type'] == 'ectc' ) echo 'checked'; ?> /><label for="coin_type_ectc">eCTC</label>
				<input type="radio" name="coin_type" value="none" id="coin_type_none" <?php if ( $getData['coin_type'] == 'none' ) echo 'checked'; ?> /><label for="coin_type_none">not used</label>
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['coupon_adm_text2']) ? $langArr['coupon_adm_text2'] : "Coupon name"; ?></label>
				  <input type="text" name="name"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "name" value="<?php echo $getData['name']; ?>" />
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['coupon_adm_text3']) ? $langArr['coupon_adm_text3'] : "Amount of payment(unit: WON)"; ?></label>
				  <input type="number" name="amount"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "amount" value="<?php echo $getData['amount']; ?>" />
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['coupon_adm_text4']) ? $langArr['coupon_adm_text4'] : "Coins to be received"; ?></label>
				  <input type="number" name="coin_amount"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "coin_amount" value="<?php echo $getData['coin_amount']; ?>" />
			</div> 
			<div class="form-group">
				<input type="radio" name="used" value="Y" id="used_y" <?php if ( $getData['used'] == 'Y' ) echo 'checked'; ?> /><label for="used_y"><?php echo !empty($langArr['coupon_adm_text5']) ? $langArr['coupon_adm_text5'] : "Used"; ?></label>
				<input type="radio" name="used" value="N" id="used_n" <?php if ( $getData['used'] == 'N' ) echo 'checked'; ?> /><label for="used_n"><?php echo !empty($langArr['coupon_adm_text6']) ? $langArr['coupon_adm_text6'] : "not used"; ?></label>
			</div>
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning submit-button" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?> <span class="glyphicon glyphicon-send"></span></button>
			</div>            
		</fieldset>
	</form>
</div></div>


<?php include_once 'includes/footer.php'; ?>