<?php
// Page in use
session_start();
require_once './config/config.php';
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



// Sanitize if you want
$get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation',FILTER_SANITIZE_STRING); 
($operation == 'edit') ? $edit = true : $edit = false;
 $db = getDbInstance();
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

    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
    //Insert timestamp
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();
	$db->where('id',$get_id);
	$insertArr = [] ;
	$insertArr['store_name'] =  $data_to_store['store_name'];
	$insertArr['store_cat'] =  $data_to_store['store_cat'];
	$insertArr['store_region'] =  $data_to_store['store_region'];
	$insertArr['store_address'] =  $data_to_store['store_address'];
	$insertArr['store_phone'] =  $data_to_store['store_phone'];
	$insertArr['store_wallet_address'] =  $data_to_store['store_wallet_address'];
	$insertArr['store_description'] =  $data_to_store['store_description'];
	$last_id = $db->update('stores', $insertArr);
    
    if($last_id)
    {
    	$_SESSION['success'] ="Store Updated Successfully";
        $walletLogger->info('관리자 모드 > 매장 관리 > 매장 수정/매장 고유 ID:'.$get_id,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
    	header('location: stores.php');
    	exit();
    }  
}
else{
    $walletLogger->info('관리자 모드 > 매장 관리 > 매장 수정 페이지 조회/매장 고유 ID:'.$get_id,['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
//If edit variable is set, we are performing the update operation.
if($edit)
{
    $db->where('id',$get_id);
    //Get data to pre-populate the form.
    $getData = $db->getOne("stores");
}

require_once 'includes/header.php'; 
?>
   <!-- MetisMenu CSS -->
        <link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
		 <script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 
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
				<label for="f_name"><?php echo !empty($langArr['store_name']) ? $langArr['store_name'] : "Store Name"; ?></label>
				  <input type="text" name="store_name" value="<?php echo $getData['store_name'] ?>"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_name">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_cat']) ? $langArr['store_cat'] : "Store Category"; ?></label>
				  <input type="text" name="store_cat" value="<?php echo $getData['store_cat'] ?>"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_cat">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_region']) ? $langArr['store_region'] : "Store Region"; ?></label>
				  <input type="text" name="store_region" value="<?php echo $getData['store_region'] ?>"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_region">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_address']) ? $langArr['store_address'] : "Store Address"; ?></label>
				  <input type="text" name="store_address" value="<?php echo $getData['store_address'] ?>"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_address">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_phone']) ? $langArr['store_phone'] : "Store Phone"; ?></label>
				  <input type="text" name="store_phone" value="<?php echo $getData['store_phone'] ?>"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_phone">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_wallet_address']) ? $langArr['store_wallet_address'] : "Store Wallet Address"; ?></label>
				  <input type="text" name="store_wallet_address" value="<?php echo $getData['store_wallet_address'] ?>"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_wallet_address">
			</div> 

			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_description']) ? $langArr['store_description'] : "Store Description"; ?></label>
				<textarea class="form-control" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" id = "store_description" name = "store_description"><?php echo $getData['store_description'] ?></textarea>
				
			</div> 
			 <br/>
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning submit-button" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?> <span class="glyphicon glyphicon-send"></span></button>
			</div>            
		</fieldset>
	</form>
</div></div>


<script type="text/javascript">
$(document).ready(function(){
   $("#customer_form").validate({
       rules: {
            f_name: {
                required: true,
                minlength: 3
            },
            l_name: {
                required: true,
                minlength: 3
            },   
        }
    });
	$('#dob').datepicker({format: "yyyy/mm/dd"});
	

});

</script>

<?php include_once 'includes/footer.php'; ?>