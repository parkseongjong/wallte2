<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;


// Sanitize if you want
$user_id = filter_input(INPUT_GET, 'admin_user_id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation',FILTER_SANITIZE_STRING); 
($operation == 'edit') ? $edit = true : $edit = false;
$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

 
  //Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
}
//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	
	if($_SESSION['admin_type']!='admin'){
		 $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
	}
    //Get customer id form query string parameter.
    $user_id = filter_input(INPUT_GET, 'admin_user_id', FILTER_SANITIZE_STRING);

    $walletLogger->info('사용자 수정 페이지 수정 완료',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$user_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);

    //Get input data
    //$data_to_update = filter_input_array(INPUT_POST);
   // $data_to_update['updated_at'] = date('Y-m-d H:i:s');

    $db = getDbInstance();
    $db->where('id',$user_id);
	$updateArr = [] ;
	$updateArr['phone'] =  $_POST['phone'];
	$updateArr['email'] =  $_POST['email']; // phone -> email (2020.05.15, YMJ)
	$updateArr['name'] =  $_POST['fname'];
	$updateArr['lname'] =  $_POST['lname'];
	$updateArr['gender'] =  $_POST['gender'];
	$updateArr['dob'] =  $_POST['dob'];
	$updateArr['swap_block'] =  $_POST['swap_block'];
	$updateArr['max_swap_quantity'] =  $_POST['max_swap_quantity']; 
	$updateArr['admin_type'] =  $_POST['admin_type'];
//	$updateArr['location'] =  $_POST['location'];
    $stat = $db->update('admin_accounts', $updateArr);


	// Bee point Start
	$bee_amount = isset($_POST['bee_amount']) ? $_POST['bee_amount'] : '';
	$bee_amount = trim($bee_amount);
	$bee_amount = str_replace(',', '', $bee_amount);
	if ( !empty($_POST['bee_user_id']) && !empty($bee_amount) ) {
		$updateArr2 = [];
		$updateArr2['user_id'] = $_POST['bee_user_id'];
		$updateArr2['user_wallet_address'] = $_POST['bee_user_wallet_address'];

		if ( !empty($_POST['bee_store_wallet_address']) ) {
			$db = getDbInstance();
			$db->where('store_wallet_address',$_POST['bee_store_wallet_address']);
			$stores_t = $db->getOne("stores");
			if ( isset($stores_t['id']) && !empty($stores_t['id']) ) {
				$updateArr2['store_id'] = $stores_t['id'];
			}
			$updateArr2['store_wallet_address'] = $_POST['bee_store_wallet_address'];
		}
		$updateArr2['points'] = $bee_amount;
		if ( !empty($_POST['bee_description']) ) {
			$updateArr2['description'] = $_POST['bee_description'];
		}
		if ( !empty($_POST['bee_tx_id']) ) {
			$updateArr2['tx_id'] = $_POST['bee_tx_id'];
		}

		$db = getDbInstance();
		$store_stat = $db->insert('store_transactions', $updateArr2);
	}
	// Bee point End





	

	if ( !empty($_POST['etoken_amount']) ) {
		$etoken_amount = $_POST['etoken_amount'];
		$etoken_amount = str_replace(',', '', $etoken_amount);
		$etoken_amount = str_replace(' ', '', $etoken_amount);
		if ( $etoken_amount <= 0 || !is_numeric($etoken_amount) ) {
			$_SESSION['failure'] = "eToken Not Saved!";
		} else {
			$token = $_POST['etoken_type'];
			$etoken_amount_type = $_POST['etoken_amount_type'];
			$user_wallet_address1 = $_POST['bee_user_wallet_address'];
			$adminId = $n_master_etoken_id;
			$adminWalletAddress = $n_master_etoken_wallet_address;


			if ( $etoken_amount_type == 'in' ) {
				$db = getDbInstance();
				$db->where("id", $user_id);
				$updateArr = [];
				$updateArr['etoken_'.$token] = $db->inc($etoken_amount);
				$last_id1 = $db->update('admin_accounts', $updateArr);
				if ( $last_id1 ) {
					$data_to_send_logs = [];
					$data_to_send_logs['user_id'] = $user_id;
					$data_to_send_logs['wallet_address'] = $user_wallet_address1;
					$data_to_send_logs['coin_type'] = $token;
					$data_to_send_logs['points'] = $etoken_amount;
					$data_to_send_logs['in_out'] = 'in';
					$data_to_send_logs['send_type'] = 'from_admin';
					$data_to_send_logs['send_user_id'] = $adminId;
					$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
					$data_to_send_logs['send_fee'] = '0';
					$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
					
					$db = getDbInstance();
					$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
				}

				$db = getDbInstance();
				$db->where("id", $adminId);
				$updateArr = [];
				$updateArr['etoken_'.$token] = $db->dec($etoken_amount);
				$last_id2 = $db->update('admin_accounts', $updateArr);
				if ( $last_id2 ) {
					$data_to_send_logs = [];
					$data_to_send_logs['user_id'] = $adminId;
					$data_to_send_logs['wallet_address'] = $adminWalletAddress;
					$data_to_send_logs['coin_type'] = $token;
					$data_to_send_logs['points'] = '-'.$etoken_amount;
					$data_to_send_logs['in_out'] = 'out';
					$data_to_send_logs['send_type'] = 'from_admin';
					$data_to_send_logs['send_user_id'] = $user_id;
					$data_to_send_logs['send_wallet_address'] = $user_wallet_address1;
					$data_to_send_logs['send_fee'] = '0';
					$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
					
					$db = getDbInstance();
					$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
				}


			} else {
				$db = getDbInstance();
				$db->where("id", $user_id);
				$updateArr = [];
				$updateArr['etoken_'.$token] = $db->dec($etoken_amount);
				$last_id1 = $db->update('admin_accounts', $updateArr);
				if ( $last_id1 ) {
					$data_to_send_logs = [];
					$data_to_send_logs['user_id'] = $user_id;
					$data_to_send_logs['wallet_address'] = $user_wallet_address1;
					$data_to_send_logs['coin_type'] = $token;
					$data_to_send_logs['points'] = '-'.$etoken_amount;
					$data_to_send_logs['in_out'] = 'out';
					$data_to_send_logs['send_type'] = 'from_admin';
					$data_to_send_logs['send_user_id'] = $adminId;
					$data_to_send_logs['send_wallet_address'] = $adminWalletAddress;
					$data_to_send_logs['send_fee'] = '0';
					$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
					
					$db = getDbInstance();
					$last_id_sl = $db->insert('etoken_logs', $data_to_send_logs);
				}

				$db = getDbInstance();
				$db->where("id", $adminId);
				$updateArr = [];
				$updateArr['etoken_'.$token] = $db->inc($etoken_amount);
				$last_id2 = $db->update('admin_accounts', $updateArr);
				if ( $last_id2 ) {
					$data_to_send_logs = [];
					$data_to_send_logs['user_id'] = $adminId;
					$data_to_send_logs['wallet_address'] = $adminWalletAddress;
					$data_to_send_logs['coin_type'] = $token;
					$data_to_send_logs['points'] = $etoken_amount;
					$data_to_send_logs['in_out'] = 'in';
					$data_to_send_logs['send_type'] = 'from_admin';
					$data_to_send_logs['send_user_id'] = $user_id;
					$data_to_send_logs['send_wallet_address'] = $user_wallet_address1;
					$data_to_send_logs['send_fee'] = '0';
					$data_to_send_logs['created_at'] = date("Y-m-d H:i:s");
					
					$db = getDbInstance();
					$last_id_sl2 = $db->insert('etoken_logs', $data_to_send_logs);
				}
			}
		}
	}



	// push, 20.10.19
	if ( !empty($_POST['push_product_name']) && !empty($_POST['push_message']) && !empty($_POST['onesignal_id']) ) {
		$push_message = sendPushText($_POST['push_product_name'], $_POST['push_message']);
		$return_response = sendPushMessage($push_message, $_POST['onesignal_id']);
	}



    if($stat)
    {
        $_SESSION['success'] = "Users updated successfully!";
        //Redirect to the listing page,
		if ( !empty($_POST['queries']) ) {
			header('location: admin_users.php?'.$_POST['queries']);
		} else {
			header('location: admin_users.php');
		}
        //Important! Don't execute the rest put the exit/die. 
        exit();
    }
}
else{
    $walletLogger->info('사용자 수정 페이지 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$user_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}


$id_auth = '';
//If edit variable is set, we are performing the update operation.
if($edit)
{
    $db->where('id',$user_id);
    //Get data to pre-populate the form.
    $users = $db->getOne("admin_accounts");

	// 실명인증여부 (2020.05.15, YMJ)
	$id_auth = $users['id_auth'];

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
        <h2 class="page-header">Update Users</h2>
    </div>
    <!-- Flash messages -->
    <?php
	include('./includes/flash_messages.php');

	if ($id_auth == 'Y') {
		?>
		<p>
			<?php echo $langArr['auth_finish']; ?><br />
			Name : <?php echo $users['auth_name']; ?><br />
			Phone : <?php echo $users['auth_phone']; ?>
		</p>
	<?php } ?>

    <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
		<?php
		if ( !empty($_GET) ) {
			$tmp = $_GET;
			unset($tmp['admin_user_id']);
			unset($tmp['operation']);
			$tmp = http_build_query($tmp);
		}
		?>
		<input type="hidden" name="queries" value="<?php echo $tmp; ?>" />
        
       <fieldset>
			<div class="form-group">
				<label for="l_name">Email*</label>
				<input type="text" name="email" value="<?php echo $edit ? $users['email'] : ''; ?>" placeholder="Email" class="form-control" required="required" id="email">
			</div> 

			<div class="form-group">
				<label for="l_name">Phone*</label>
				<input type="text" name="phone" value="<?php echo $edit ? $users['phone'] : ''; ?>" placeholder="Phone" class="form-control" id="phone">
			</div> 
			<p><?php echo $langArr['admin_edit_member_text1']; ?></p>

			<div class="form-group">
				<label for="l_name">Name*</label>
				<input type="text" name="fname" value="<?php echo $edit ? $users['name'] : ''; ?>" placeholder="Name" class="form-control" required="required" id="name">
			</div> 

			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?></label>
				  <input type="text" name="lname" value="<?php echo $users['lname']; ?>" placeholder="<?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?>" class="form-control" required="required" id = "lname">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['gender']) ? $langArr['gender'] : "Gender"; ?></label>
				  <select class="form-control" id="gender" name="gender">
				  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
				  <option <?php echo ($users['gender']=="male") ? "Selected" : ""; ?> value="male"><?php echo !empty($langArr['male']) ? $langArr['male'] : "Male"; ?></option>
				  <option <?php echo ($users['gender']=="female") ? "Selected" : ""; ?> value="female"><?php echo !empty($langArr['female']) ? $langArr['female'] : "Female"; ?></option>
				  <option <?php echo ($users['gender']=="other") ? "Selected" : ""; ?> value="other"><?php echo !empty($langArr['other']) ? $langArr['other'] : "Other"; ?></option>
				 </select>
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?></label>
				  <input type="text" name="dob" readonly value="<?php echo $users['dob']; ?>" placeholder="<?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?>" class="form-control" required="required" id = "dob">
			</div> 

			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['max_swap_quantity']) ? $langArr['max_swap_quantity'] : "Max Swap Quantity"; ?></label>
				  <input type="text" name="max_swap_quantity"  value="<?php echo $users['max_swap_quantity']; ?>" placeholder="<?php echo !empty($langArr['max_swap_quantity']) ? $langArr['max_swap_quantity'] : "Max Swap Quantity"; ?>" class="form-control" required="required" id = "max_swap_quantity">
			</div> 

			
			<div class="form-group">
			<label for="f_name"><?php echo !empty($langArr['swap_block']) ? $langArr['swap_block'] : "Block For Swapping"; ?></label>
				  <select class="form-control" id="swap_block" name="swap_block">
				  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
				  <option <?php echo ($users['swap_block']=="Y") ? "Selected" : ""; ?> value="Y"><?php echo !empty($langArr['S']) ? $langArr['S'] : "YES"; ?></option>
				  <option <?php echo ($users['swap_block']=="N") ? "Selected" : ""; ?> value="N"><?php echo !empty($langArr['NO']) ? $langArr['NO'] : "NO"; ?></option>
				
				 </select>
			</div> 

<!---
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?></label>
				  <input type="text" name="location" value="<?php echo $users['location']; ?>" placeholder="<?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?>" class="form-control" required="required" id = "location"">
			</div> 
--->

			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['user_role']) ? $langArr['user_role'] : "User Role"; ?></label>
				  <select class="form-control" required id="role" name="admin_type">
				  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
				  <option <?php echo ($users['admin_type']=="user") ? "Selected" : ""; ?> value="user"><?php echo !empty($langArr['user']) ? $langArr['user'] : "User"; ?></option>
				  <option <?php echo ($users['admin_type']=="admin") ? "Selected" : ""; ?> value="admin"><?php echo !empty($langArr['admin']) ? $langArr['admin'] : "Admin"; ?></option>
				  <option <?php echo ($users['admin_type']=="store") ? "Selected" : ""; ?> value="store"><?php echo !empty($langArr['store_tx1']) ? $langArr['store_tx1'] : "Shopping mall seller"; ?></option>
				 
				 </select>
			</div>


			<h3>Bee point</h3>
			<div class="form-group">
				<label for="bee_amount">Bee point</label>
				  <input type="text" name="bee_amount" value="" placeholder="Bee point (only number : -100, 300, ...)" class="form-control" id = "bee_amount">
			</div>
			<div class="form-group">
				<label for="bee_description">Description</label>
				  <input type="text" name="bee_description" value="" placeholder="Description" class="form-control" id = "bee_description">
			</div> 
			<div class="form-group">
				<label for="bee_store_wallet_address">Store Wallet Address</label>
				  <input type="text" name="bee_store_wallet_address" value="" placeholder="Store Wallet Address" class="form-control" id = "bee_store_wallet_address">
			</div> 
			<div class="form-group">
				<label for="bee_tx_id">Transaction Hash</label>
				  <input type="text" name="bee_tx_id" value="" placeholder="Transaction Hash" class="form-control" id = "bee_tx_id">
			</div>

			<input type="hidden" name="bee_user_id" value="<?php echo $users['id']; ?>" />
			<input type="hidden" name="bee_user_wallet_address" value="<?php echo $users['wallet_address']; ?>" />

			<?php
			$db = getDbInstance();
			$db->where('user_id',$users['id']);
			$store_transactions_row = $db->get('store_transactions');
			if($db->count > 0){
				?><ul><?php
				foreach ($store_transactions_row as $row) {
					?><li>Point : <?php echo number_format($row['points'], 8);?>, Date : <?php echo $row['created_at']; ?> <?php if ( !empty($row['description']) ) { echo ' ('.$row['description'].')'; } ?></li><?php
				} // foreach
				?></ul><?php
			} // if
			?>


			<hr />

			<h3>eToken</h3>
			
			<div class="form-group">
				<label for="etoken_type">eToken</label>
				<select name="etoken_type" id="etoken_type">
					<?php
					foreach($n_full_name_array2 as $k1=>$v1) {
						?><option value="<?php echo $k1; ?>"><?php echo lcfirst(strtoupper($k1)); ?></option><?php
					} ?>
				</select>
			</div>
			<div class="form-group">
				<label for="">Point</label>
				<input type="radio" name="etoken_amount_type" id="etoken_amount_type_in" value="in" checked /> <label for="etoken_amount_type_in"> Add(+)</label>
				<input type="radio" name="etoken_amount_type" id="etoken_amount_type_out" value="out" /> <label for="etoken_amount_type_out"> Point recovery(-)</label>
			</div>
			<div class="form-group">
				<label for="etoken_amount">Point</label>
				<input type="text" name="etoken_amount" value="" class="form-control" id = "etoken_amount" placeholder="Enter only numbers greater than 0 (100, 200, ...)">
			</div>

			<ul>
			<?php
			foreach($n_full_name_array2 as $k=>$v) {
				?><li><?php echo lcfirst(strtoupper($k)).' : '.new_number_format($users['etoken_'.$k], $n_decimal_point_array2[$k]); ?></li><?php
			} ?>
			</ul>

			<?php
			if ( !empty($users['onesignal_id']) ) { ?>
				<h3>Push</h3>
				<input type="hidden" name="onesignal_id" value="<?php echo $users['onesignal_id']; ?>" />
				<div class="form-group">
					<label for="">상품명</label>
					<input type="text" name="push_product_name" id="push_product_name" value="" />
				</div>
				<div class="form-group">
					<label for="">Message</label>
					<select name="push_message">
						<option value="">선택</option>
						<?php
							foreach($push_message_array as $k=>$v) {
								?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
							}
						?>
					</select>
				</div>
			<?php } ?>


			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
			</div>
		</fieldset>
    </form>
</div>


<script type="text/javascript">
$(document).ready(function(){

	$('#dob').datepicker({format: "yyyy/mm/dd"});
});

</script>

<?php include_once 'includes/footer.php'; ?>