<?php
// Page in use
error_reporting("E_ALL");
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
 //Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
   $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
}

$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$setDatas = $db->get('settings', NULL, array('id', 'module_name', 'show_name', 'value'));
$setData = array();
if ( !empty($setDatas) ) {
	foreach($setDatas as $row) {
		$setData[$row['module_name']] = array(
			'id' => $row['id'],
			'show_name' => $row['show_name'],
			'value' => $row['value']
		);
	}
}
$db = getDbInstance();
$db->where('module_name', 'krw_per_coin', '!=');
$setDatas2 = $db->get('settings2', NULL, array('id', 'module_name', 'value'));
$setData2 = array();
if ( !empty($setDatas2) ) {
	foreach($setDatas2 as $row2) {
		$setData2[$row2['module_name']] = array(
			'id' => $row2['id'],
			'value' => $row2['value']
		);
	}
}


$db = getDbInstance();
$getTokenList = $db->get('tokenlist');
$allowArr = [];
foreach($getTokenList as $singleToken){
	$allowArr[]= $singleToken['token_name'];
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    $data_to_store = filter_input_array(INPUT_POST);
	$data_to_store['created_at'] = date('Y-m-d H:i:s');
	
	
	getDbInstance()->where("id", $setData['exchange_rate']['id'])->update('settings', ['value'=>$data_to_store['exchange_rate']]);
	getDbInstance()->where("id", $setData['exchange_rate_tp3']['id'])->update('settings', ['value'=>$data_to_store['exchange_rate_tp3']]);
	getDbInstance()->where("id", $setData['points_exchange']['id'])->update('settings', ['value'=>$data_to_store['points_exchange']]);
	getDbInstance()->where("id", $setData['send_ctc_fee']['id'])->update('settings', ['value'=>$data_to_store['send_ctc_fee']]);
	getDbInstance()->where("id", $setData['send_token_fee']['id'])->update('settings', ['value'=>$data_to_store['send_token_fee']]);
	getDbInstance()->where("id", $setData['send_free_ctc']['id'])->update('settings', ['value'=>$data_to_store['send_free_ctc']]);
	getDbInstance()->where("id", $setData['send_free_eth']['id'])->update('settings', ['value'=>$data_to_store['send_free_eth']]);
	getDbInstance()->where("id", $setData['send_free_tp3']['id'])->update('settings', ['value'=>$data_to_store['send_free_tp3']]);
	getDbInstance()->where("id", $setData['min_transfer_amount_tp3']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_amount_tp3']]);
	getDbInstance()->where("id", $setData['min_transfer_amount_mc']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_amount_mc']]);


	getDbInstance()->where("id", $setData['exchange_fee_in_eth']['id'])->update('settings', ['value'=>$data_to_store['exchange_fee_in_eth']]);

	getDbInstance()->where("id", $setData['min_exchange_amount_ctc']['id'])->update('settings', ['value'=>$data_to_store['min_exchange_amount_ctc']]);
	getDbInstance()->where("id", $setData['min_exchange_amount_tp3']['id'])->update('settings', ['value'=>$data_to_store['min_exchange_amount_tp3']]);
	
	getDbInstance()->where("id", $setData['lock_sending']['id'])->update('settings', ['value'=>$data_to_store['lock_sending']]);
	//getDbInstance()->where("id", $setData['conversion_unit_tp3']['id'])->update('settings', ['value'=>$data_to_store['conversion_unit_tp3']]);


	getDbInstance()->where("id", $setData['exchange_ectc_per_ctc']['id'])->update('settings', ['value'=>$data_to_store['exchange_ectc_per_ctc']]);
	getDbInstance()->where("id", $setData['exchange_etp3_per_tp3']['id'])->update('settings', ['value'=>$data_to_store['exchange_etp3_per_tp3']]);
	getDbInstance()->where("id", $setData['exchange_ekrw_per_krw']['id'])->update('settings', ['value'=>$data_to_store['exchange_ekrw_per_krw']]);
	getDbInstance()->where("id", $setData['exchange_emc_per_mc']['id'])->update('settings', ['value'=>$data_to_store['exchange_emc_per_mc']]);
	getDbInstance()->where("id", $setData['exchange_eusdt_per_usdt']['id'])->update('settings', ['value'=>$data_to_store['exchange_eusdt_per_usdt']]);
	getDbInstance()->where("id", $setData['exchange_eeth_per_eth']['id'])->update('settings', ['value'=>$data_to_store['exchange_eeth_per_eth']]);

	getDbInstance()->where("id", $setData['min_transfer_ctc_to_ectc']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_ctc_to_ectc']]);
	getDbInstance()->where("id", $setData['min_transfer_tp3_to_etp3']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_tp3_to_etp3']]);
	getDbInstance()->where("id", $setData['min_transfer_krw_to_ekrw']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_krw_to_ekrw']]);
	getDbInstance()->where("id", $setData['min_transfer_mc_to_emc']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_mc_to_emc']]);
	getDbInstance()->where("id", $setData['min_transfer_usdt_to_eusdt']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_usdt_to_eusdt']]);
	getDbInstance()->where("id", $setData['min_transfer_eth_to_eeth']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_eth_to_eeth']]);

	getDbInstance()->where("id", $setData['min_transfer_ectc_to_ctc']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_ectc_to_ctc']]);
	getDbInstance()->where("id", $setData['min_transfer_etp3_to_tp3']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_etp3_to_tp3']]);
	getDbInstance()->where("id", $setData['min_transfer_ekrw_to_krw']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_ekrw_to_krw']]);
	getDbInstance()->where("id", $setData['min_transfer_emc_to_mc']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_emc_to_mc']]);
	getDbInstance()->where("id", $setData['min_transfer_eusdt_to_usdt']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_eusdt_to_usdt']]);
	getDbInstance()->where("id", $setData['min_transfer_eeeth_to_eeth']['id'])->update('settings', ['value'=>$data_to_store['min_transfer_eeeth_to_eeth']]);

	getDbInstance()->where("id", $setData['min_send_amount_ectc']['id'])->update('settings', ['value'=>$data_to_store['min_send_amount_ectc']]);
	getDbInstance()->where("id", $setData['min_send_amount_etp3']['id'])->update('settings', ['value'=>$data_to_store['min_send_amount_etp3']]);
	getDbInstance()->where("id", $setData['min_send_amount_ekrw']['id'])->update('settings', ['value'=>$data_to_store['min_send_amount_ekrw']]);
	getDbInstance()->where("id", $setData['min_send_amount_emc']['id'])->update('settings', ['value'=>$data_to_store['min_send_amount_emc']]);
	getDbInstance()->where("id", $setData['min_send_amount_eusdt']['id'])->update('settings', ['value'=>$data_to_store['min_send_amount_eusdt']]);
	getDbInstance()->where("id", $setData['min_send_amount_eeth']['id'])->update('settings', ['value'=>$data_to_store['min_send_amount_eeth']]);


	getDbInstance()->where("id", $setData['send_etoken_fee']['id'])->update('settings', ['value'=>$data_to_store['send_etoken_fee']]);
	getDbInstance()->where("id", $setData['send_etoken_fee2']['id'])->update('settings', ['value'=>$data_to_store['send_etoken_fee2']]);
	getDbInstance()->where("id", $setData['send_etoken_fee_h']['id'])->update('settings', ['value'=>$data_to_store['send_etoken_fee_h']]);
	getDbInstance()->where("id", $setData['send_etoken_fee2_h']['id'])->update('settings', ['value'=>$data_to_store['send_etoken_fee2_h']]);
	getDbInstance()->where("id", $setData['send_etoken_fee_eth']['id'])->update('settings', ['value'=>$data_to_store['send_etoken_fee_eth']]);


	getDbInstance()->where("id", $setData['send_free_ectc']['id'])->update('settings', ['value'=>$data_to_store['send_free_ectc']]);
	getDbInstance()->where("id", $setData['send_free_etp3']['id'])->update('settings', ['value'=>$data_to_store['send_free_etp3']]);

	getDbInstance()->where("id", $setData['krw_per_ctc_kiosk']['id'])->update('settings', ['value'=>$data_to_store['krw_per_ctc_kiosk']]);
	getDbInstance()->where("id", $setData['krw_per_tp3_kiosk']['id'])->update('settings', ['value'=>$data_to_store['krw_per_tp3_kiosk']]);
	getDbInstance()->where("id", $setData['krw_per_mc_kiosk']['id'])->update('settings', ['value'=>$data_to_store['krw_per_mc_kiosk']]);
	getDbInstance()->where("id", $setData['krw_per_usdt_kiosk']['id'])->update('settings', ['value'=>$data_to_store['krw_per_usdt_kiosk']]);
	getDbInstance()->where("id", $setData['krw_per_eth_kiosk']['id'])->update('settings', ['value'=>$data_to_store['krw_per_eth_kiosk']]);

	getDbInstance()->where("id", $setData['tp3_ctctm_rate']['id'])->update('settings', ['value'=>$data_to_store['tp3_ctctm_rate']]);
	getDbInstance()->where("id", $setData['mc_ctctm_rate']['id'])->update('settings', ['value'=>$data_to_store['mc_ctctm_rate']]);
	getDbInstance()->where("id", $setData['ctc_ctctm_rate']['id'])->update('settings', ['value'=>$data_to_store['ctc_ctctm_rate']]);


	getDbInstance()->where("id", $setData['min_ctctm_swap_one_time']['id'])->update('settings', ['value'=>$data_to_store['min_ctctm_swap_one_time']]);
	getDbInstance()->where("id", $setData['max_ctctm_swap_one_time']['id'])->update('settings', ['value'=>$data_to_store['max_ctctm_swap_one_time']]);
	getDbInstance()->where("id", $setData['max_ctctm_swap_per_user']['id'])->update('settings', ['value'=>$data_to_store['max_ctctm_swap_per_user']]);

	getDbInstance()->where("id", $setData['payment_service_available']['id'])->update('settings', ['value'=>$data_to_store['payment_service_available']]);
	
	
	foreach($data_to_store as $dataKey => $data_to_store_single){
		if(in_array($dataKey,$allowArr)){
			getDbInstance()->where("token_name", $dataKey)->update('tokenlist', ['monthly_transfer_limit'=>$data_to_store_single]);
		}

	}


	/*
	if ( !empty($data_to_store['won2_send_token_fee']) ) {
		getDbInstance()->where("id", $setData2['send_token_fee']['id'])->update('settings2', ['value'=>$data_to_store['won2_send_token_fee']]);
	}
	if ( !empty($data_to_store['won2_send_etoken_fee']) ) {
		getDbInstance()->where("id", $setData2['send_etoken_fee']['id'])->update('settings2', ['value'=>$data_to_store['won2_send_etoken_fee']]);
	}
	if ( !empty($data_to_store['won2_send_etoken_fee_eth']) ) {
		getDbInstance()->where("id", $setData2['send_etoken_fee_eth']['id'])->update('settings2', ['value'=>$data_to_store['won2_send_etoken_fee_eth']]);
	}
	if ( !empty($data_to_store['won2_send_etoken_fee2']) ) {
		getDbInstance()->where("id", $setData2['send_etoken_fee2']['id'])->update('settings2', ['value'=>$data_to_store['won2_send_etoken_fee2']]);
	}
	if ( !empty($data_to_store['won2_send_etoken_fee_h']) ) {
		getDbInstance()->where("id", $setData2['send_etoken_fee_h']['id'])->update('settings2', ['value'=>$data_to_store['won2_send_etoken_fee_h']]);
	}
	if ( !empty($data_to_store['won2_send_etoken_fee2_h']) ) {
		getDbInstance()->where("id", $setData2['send_etoken_fee2_h']['id'])->update('settings2', ['value'=>$data_to_store['won2_send_etoken_fee2_h']]);
	}
	*/
	
	


	$_SESSION['success'] = "Settings updated successfully!";
    $walletLogger->info('관리자 모드 > 설정 > 수정',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
	header('location: settings.php');
	exit();

}
else{
    $walletLogger->info('관리자 모드 > 설정 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);
}




//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 


?>
<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['settings']) ? $langArr['settings'] : 'Settings'; ?></h2>
			</div>			
	</div>
	<?php include('./includes/flash_messages.php') ?>
	
	<?php
	if ( $_SESSION['user_id'] != '93' && $_SESSION['user_id'] != '5137' && $_SESSION['user_id'] != '11863' && $_SESSION['user_id'] != '17417') {
		 $_SESSION['failure'] = !empty($langArr['you_don_have_access']) ? $langArr['you_don_have_access'] : "You don't have access.";
		 exit();
	}
	?>

	<div class="row">
		 <div class="col-lg-8">
			
			<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
				<fieldset>
					
					<div class="form-group">
						<label for="exchange_rate"><?php echo $setData['exchange_rate']['show_name']; ?> (1 ETH = <?php echo $setData['exchange_rate']['value']; ?> CTC)</label>
						  <input type="text" name="exchange_rate" value="<?php echo $setData['exchange_rate']['value']; ?>"  class="form-control" required="required" id="exchange_rate" readonly>
					</div> 
					
					<div class="form-group">
						<label for="exchange_rate_tp3"><?php echo $setData['exchange_rate_tp3']['show_name']; ?> (1 ETH = <?php echo $setData['exchange_rate_tp3']['value']; ?> TP3)</label>
						  <input type="text" name="exchange_rate_tp3" value="<?php echo $setData['exchange_rate_tp3']['value']; ?>"  class="form-control" required="required" id="exchange_rate_tp3" readonly>
					</div> 
					<div class="form-group">
						<label for="points_exchange">Points Exchange (1 Bee Points = ₩<?php echo $setData['points_exchange']['value']; ?>)</label>
						  <input type="text" name="points_exchange" value="<?php echo $setData['points_exchange']['value']; ?>"  class="form-control" required="required" id="points_exchange" readonly >
					</div>
					<div class="form-group">
						<label for="exchange_rate"><?php echo $setData['exchange_fee_in_eth']['show_name']; ?> (in ETH)</label>
						  <input type="text" name="exchange_fee_in_eth" value="<?php echo $setData['exchange_fee_in_eth']['value']; ?>"  class="form-control" required="required" id="exchange_fee_in_eth" >
					</div> 
					<div class="form-group">
						<label for="send_ctc_fee"><?php echo $setData['send_ctc_fee']['show_name']; ?> (%) </label>
						  <input type="text" name="send_ctc_fee" value="<?php echo $setData['send_ctc_fee']['value']; ?>"  class="form-control" required="required" id="send_ctc_fee" readonly >
					</div>
					<div class="form-group">
						<label for="send_free_eth"><?php echo $setData['send_free_eth']['show_name']; ?> (In ETH)</label>
						  <input type="text" name="send_free_eth" value="<?php echo $setData['send_free_eth']['value']; ?>"  class="form-control" required="required" id="send_free_eth" readonly>
					</div>
					<div class="form-group">
						<label for="send_token_fee">*<?php echo $setData['send_token_fee']['show_name']; ?> (In CTC) <?php echo !empty($langArr['settings_text1']) ? $langArr['settings_text1'] : ''; ?></label>
						<!--<br />₩<input type="text" name="won2_send_token_fee" value="<?php echo $setData2['send_token_fee']['value']; ?>" class="form-control settings2" id="won2_send_token_fee">-->
						<input type="text" name="send_token_fee" value="<?php echo $setData['send_token_fee']['value']; ?>"  class="form-control" required="required" id="send_token_fee">
					</div>	
					<div class="form-group">
						<label for="send_free_ctc"><?php echo $setData['send_free_ctc']['show_name']; ?> (airdrop)</label>
						  <input type="text" name="send_free_ctc" value="<?php echo $setData['send_free_ctc']['value']; ?>"  class="form-control" required="required" id="send_free_ctc" readonly>
					</div>
					<div class="form-group">
						<label for="send_free_tp3"><?php echo $setData['send_free_tp3']['show_name']; ?> (airdrop)</label>
						  <input type="text" name="send_free_tp3" value="<?php echo $setData['send_free_tp3']['value']; ?>"  class="form-control" required="required" id="send_free_tp3" readonly>
					</div>
					<div class="form-group">
						<label for="min_transfer_amount_tp3"><?php echo $setData['min_transfer_amount_tp3']['show_name']; ?> <?php echo !empty($langArr['settings_text2']) ? $langArr['settings_text2'] : ''; ?></label>
						  <input type="text" name="min_transfer_amount_tp3" value="<?php echo $setData['min_transfer_amount_tp3']['value']; ?>"  class="form-control" required="required" id="min_transfer_amount_tp3" >
					</div>
					<div class="form-group">
						<label for="min_transfer_amount_mc"><?php echo $setData['min_transfer_amount_mc']['show_name']; ?> <?php echo !empty($langArr['settings_text2']) ? $langArr['settings_text2'] : ''; ?></label>
						  <input type="text" name="min_transfer_amount_mc" value="<?php echo $setData['min_transfer_amount_mc']['value']; ?>"  class="form-control" required="required" id="min_transfer_amount_mc" >
					</div>
					<div class="form-group">
						<label for="min_exchange_amount_ctc"><?php echo $setData['min_exchange_amount_ctc']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?></label>
						  <input type="text" name="min_exchange_amount_ctc" value="<?php echo $setData['min_exchange_amount_ctc']['value']; ?>"  class="form-control" required="required" id="min_exchange_amount_ctc" >
					</div>
					<div class="form-group">
						<label for="min_exchange_amount_tp3"><?php echo $setData['min_exchange_amount_tp3']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?></label>
						  <input type="text" name="min_exchange_amount_tp3" value="<?php echo $setData['min_exchange_amount_tp3']['value']; ?>"  class="form-control" required="required" id="min_exchange_amount_tp3" >
					</div>
					<fieldset>
					<legend>Swap Settings</legend>
					

					<div class="form-group">
						<label for="tp3_ctctm_rate"><?php echo $setData['tp3_ctctm_rate']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?>(1 TP3 = <?php echo $setData['tp3_ctctm_rate']['value']; ?> CTCtm)</label>
						  <input type="text" name="tp3_ctctm_rate" value="<?php echo $setData['tp3_ctctm_rate']['value']; ?>"  class="form-control" required="required" id="tp3_ctctm_rate" >
					</div>


					<div class="form-group">
						<label for="mc_ctctm_rate"><?php echo $setData['mc_ctctm_rate']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?>(1 MC = <?php echo $setData['mc_ctctm_rate']['value']; ?> CTCtm)</label>
						  <input type="text" name="mc_ctctm_rate" value="<?php echo $setData['mc_ctctm_rate']['value']; ?>"  class="form-control" required="required" id="mc_ctctm_rate" >
					</div>

					<div class="form-group">
						<label for="ctc_ctctm_rate"><?php echo $setData['ctc_ctctm_rate']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?>(1 CTC = <?php echo $setData['ctc_ctctm_rate']['value']; ?> CTCtm)</label>
						  <input type="text" name="ctc_ctctm_rate" value="<?php echo $setData['ctc_ctctm_rate']['value']; ?>"  class="form-control" required="required" id="ctc_ctctm_rate" >
					</div>


					<div class="form-group">
						<label for="min_ctctm_swap_one_time"><?php echo $setData['min_ctctm_swap_one_time']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?></label>
						  <input type="text" name="min_ctctm_swap_one_time" value="<?php echo $setData['min_ctctm_swap_one_time']['value']; ?>"  class="form-control" required="required" id="min_ctctm_swap_one_time" >
					</div>

					<div class="form-group">
						<label for="max_ctctm_swap_one_time"><?php echo $setData['max_ctctm_swap_one_time']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?></label>
						  <input type="text" name="max_ctctm_swap_one_time" value="<?php echo $setData['max_ctctm_swap_one_time']['value']; ?>"  class="form-control" required="required" id="max_ctctm_swap_one_time" >
					</div>

					<div class="form-group">
						<label for="max_ctctm_swap_per_user"><?php echo $setData['max_ctctm_swap_per_user']['show_name']; ?> <?php echo !empty($langArr['settings_text3']) ? $langArr['settings_text3'] : ''; ?></label>
						  <input type="text" name="max_ctctm_swap_per_user" value="<?php echo $setData['max_ctctm_swap_per_user']['value']; ?>"  class="form-control" required="required" id="max_ctctm_swap_per_user" >
					</div>



					</fieldset>

					<div class="form-group">
						<label for="lock_sending"><?php echo $setData['lock_sending']['show_name']; ?></label><br />
						<input type="radio" name="lock_sending" value="C" <? if ( $setData['lock_sending']['value'] == 'C' ) echo 'checked'; ?> id="lock_sending_c" /><label for="lock_sending_c"><?php echo $langArr['settings_text4_label']; ?></label><br />
						<?php echo $langArr['settings_text4']; ?> : send, charge(exchange), sendTransaction(old user), approve<br />
						<input type="radio" name="lock_sending" value="N" <? if ( $setData['lock_sending']['value'] == 'N' ) echo 'checked'; ?> id="lock_sending_n" /><label for="lock_sending_n"><?php echo $langArr['settings_text5_label']; ?></label><br />
						<?php echo $langArr['settings_text5']; ?>
					</div>

					<!--
					<div class="form-group">
						<label for="conversion_unit_tp3"><?php echo $setData['conversion_unit_tp3']['show_name']; ?> </label>
						  <input type="text" name="conversion_unit_tp3" value="<?php echo setData['conversion_unit_tp3']['value']; ?>"  class="form-control" required="required" id="conversion_unit_tp3" >
					</div>-->
				</fieldset>
				



				<fieldset>
					<legend>Payment</legend>
			
					<div class="form-group">
						<label for="payment_service_available"><?php echo $setData['payment_service_available']['show_name']; ?></label><br />
						<input type="radio" name="payment_service_available" value="Y" <? if ( $setData['payment_service_available']['value'] == 'Y' ) echo 'checked'; ?> id="payment_service_available_y" /><label for="payment_service_available_y"><?php echo $langArr['settings_text6']; ?></label><br />
						<input type="radio" name="payment_service_available" value="N" <? if ( $setData['payment_service_available']['value'] == 'N' ) echo 'checked'; ?> id="payment_service_available_n" /><label for="payment_service_available_n"><?php echo $langArr['settings_text7']; ?></label>
					</div>
				</fieldset>


				<fieldset>
					<legend>E-Pay</legend>


					<table class="table table-bordered">
						<colgroup>
							<col width="25%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
						</colgroup>
						<thead><tr>
							<th>Explanation</th>
							<th>E-CTC</th>
							<th>E-TP3</th>
							<th>E-MC</th>
							<th>E-KRW</th>
							<th>E-USDT</th>
							<th>E-ETH</th>
						</tr></thead>
						<tbody>
							<tr>
								<td>Exchange Rate<br />1 Coin = ? E-Pay</td>
								<td><input type="text" name="exchange_ectc_per_ctc" value="<?php echo $setData['exchange_ectc_per_ctc']['value']; ?>"  class="form-control" required="required" id="exchange_ectc_per_ctc" readonly></td>
								<td><input type="text" name="exchange_etp3_per_tp3" value="<?php echo $setData['exchange_etp3_per_tp3']['value']; ?>"  class="form-control" required="required" id="exchange_etp3_per_tp3" readonly></td>
								<td><input type="text" name="exchange_emc_per_mc" value="<?php echo $setData['exchange_emc_per_mc']['value']; ?>"  class="form-control" required="required" id="exchange_emc_per_mc" readonly></td>
								<td><input type="text" name="exchange_ekrw_per_krw" value="<?php echo $setData['exchange_ekrw_per_krw']['value']; ?>"  class="form-control" required="required" id="exchange_ekrw_per_krw" readonly></td>
								<td><input type="text" name="exchange_eusdt_per_usdt" value="<?php echo $setData['exchange_eusdt_per_usdt']['value']; ?>"  class="form-control" required="required" id="exchange_eusdt_per_usdt" readonly></td>
								<td><input type="text" name="exchange_eeth_per_eth" value="<?php echo $setData['exchange_eeth_per_eth']['value']; ?>"  class="form-control" required="required" id="exchange_eeth_per_eth" readonly></td>
							</tr>
							<tr>
								<td>Coin -&gt; E-Pay<br /><?php echo !empty($langArr['settings_text2']) ? $langArr['settings_text2'] : ''; ?></td>
								<td><input type="text" name="min_transfer_ctc_to_ectc" value="<?php echo $setData['min_transfer_ctc_to_ectc']['value']; ?>"  class="form-control" required="required" id="min_transfer_ctc_to_ectc" ></td>
								<td><input type="text" name="min_transfer_tp3_to_etp3" value="<?php echo $setData['min_transfer_tp3_to_etp3']['value']; ?>"  class="form-control" required="required" id="min_transfer_tp3_to_etp3" ></td>
								<td><input type="text" name="min_transfer_mc_to_emc" value="<?php echo $setData['min_transfer_mc_to_emc']['value']; ?>"  class="form-control" required="required" id="min_transfer_mc_to_emc" ></td>
								<td><input type="text" name="min_transfer_krw_to_ekrw" value="<?php echo $setData['min_transfer_krw_to_ekrw']['value']; ?>"  class="form-control" required="required" id="min_transfer_krw_to_ekrw" ></td>
								<td><input type="text" name="min_transfer_usdt_to_eusdt" value="<?php echo $setData['min_transfer_usdt_to_eusdt']['value']; ?>"  class="form-control" required="required" id="min_transfer_usdt_to_eusdt" ></td>
								<td><input type="text" name="min_transfer_eth_to_eeth" value="<?php echo $setData['min_transfer_eth_to_eeth']['value']; ?>"  class="form-control" required="required" id="min_transfer_eth_to_eeth" ></td>
							</tr>
							<tr>
								<td>E-Pay -&gt; Coin<br /><?php echo !empty($langArr['settings_text2']) ? $langArr['settings_text2'] : ''; ?></td>
								<td><input type="text" name="min_transfer_ectc_to_ctc" value="<?php echo $setData['min_transfer_ectc_to_ctc']['value']; ?>"  class="form-control" required="required" id="min_transfer_ectc_to_ctc" ></td>
								<td><input type="text" name="min_transfer_etp3_to_tp3" value="<?php echo $setData['min_transfer_etp3_to_tp3']['value']; ?>"  class="form-control" required="required" id="min_transfer_etp3_to_tp3" ></td>
								<td><input type="text" name="min_transfer_emc_to_mc" value="<?php echo $setData['min_transfer_emc_to_mc']['value']; ?>"  class="form-control" required="required" id="min_transfer_emc_to_mc" ></td>
								<td><input type="text" name="min_transfer_ekrw_to_krw" value="<?php echo $setData['min_transfer_ekrw_to_krw']['value']; ?>"  class="form-control" required="required" id="min_transfer_ekrw_to_krw" ></td>
								<td><input type="text" name="min_transfer_eusdt_to_usdt" value="<?php echo $setData['min_transfer_eusdt_to_usdt']['value']; ?>"  class="form-control" required="required" id="min_transfer_eusdt_to_usdt" ></td>
								<td><input type="text" name="min_transfer_eeth_to_eth" value="<?php echo $setData['min_transfer_eeth_to_eth']['value']; ?>"  class="form-control" required="required" id="min_transfer_eeth_to_eth" ></td>
							</tr>
							<tr>
								<td>E-Pay -&gt; E-Pay<br /><?php echo !empty($langArr['settings_text2']) ? $langArr['settings_text2'] : ''; ?></td>
								<td><input type="text" name="min_send_amount_ectc" value="<?php echo $setData['min_send_amount_ectc']['value']; ?>"  class="form-control" required="required" id="min_send_amount_ectc" ></td>
								<td><input type="text" name="min_send_amount_etp3" value="<?php echo $setData['min_send_amount_etp3']['value']; ?>"  class="form-control" required="required" id="min_send_amount_etp3" ></td>
								<td><input type="text" name="min_send_amount_emc" value="<?php echo $setData['min_send_amount_emc']['value']; ?>"  class="form-control" required="required" id="min_send_amount_emc" ></td>
								<td><input type="text" name="min_send_amount_ekrw" value="<?php echo $setData['min_send_amount_ekrw']['value']; ?>"  class="form-control" required="required" id="min_send_amount_ekrw" ></td>
								<td><input type="text" name="min_send_amount_eusdt" value="<?php echo $setData['min_send_amount_eusdt']['value']; ?>"  class="form-control" required="required" id="min_send_amount_eusdt" ></td>
								<td><input type="text" name="min_send_amount_eeth" value="<?php echo $setData['min_send_amount_eeth']['value']; ?>"  class="form-control" required="required" id="min_send_amount_eeth" ></td>
							</tr>
							<tr>
								<td>airdrop</td>
								<td><input type="text" name="send_free_ectc" value="<?php echo $setData['send_free_ectc']['value']; ?>"  class="form-control" required="required" id="send_free_ectc" ></td>
								<td><input type="text" name="send_free_etp3" value="<?php echo $setData['send_free_etp3']['value']; ?>"  class="form-control" required="required" id="send_free_etp3" ></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>


					<table class="table table-bordered">
						<colgroup>
							<col width="25%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
							<col width="12.5%" />
						</colgroup>
						<thead><tr>
							<th>Explanation</th>
							<th>CTC</th>
							<th>TP3</th>
							<th>MC</th>
							<th>KRW</th>
							<th>USDT</th>
							<th>ETH</th>
						</tr></thead>
						<tbody>
							<tr>
								<td>*Coin당 원화 비율<br />1 Coin = ? 원<br />(30분마다 자동으로 설정됩니다.)</td>
								<td><input type="text" name="krw_per_ctc_kiosk" value="<?php echo $setData['krw_per_ctc_kiosk']['value']; ?>"  class="form-control" required="required" id="krw_per_ctc_kiosk" readonly><br />(미사용)</td>
								<td><input type="text" name="krw_per_tp3_kiosk" value="<?php echo $setData['krw_per_tp3_kiosk']['value']; ?>"  class="form-control" required="required" id="krw_per_tp3_kiosk" readonly><br />(kiosk)</td>
								<td><input type="text" name="krw_per_mc_kiosk" value="<?php echo $setData['krw_per_mc_kiosk']['value']; ?>"  class="form-control" required="required" id="krw_per_mc_kiosk" readonly><br />(kiosk)</td>
								<td></td>
								<td><input type="text" name="krw_per_usdt_kiosk" value="<?php echo $setData['krw_per_usdt_kiosk']['value']; ?>"  class="form-control" required="required" id="krw_per_usdt_kiosk" readonly><br />(미사용)</td>
								<td><input type="text" name="krw_per_eth_kiosk" value="<?php echo $setData['krw_per_eth_kiosk']['value']; ?>"  class="form-control" required="required" id="krw_per_eth_kiosk" readonly><br />(미사용)</td>
							</tr>
						</tbody>
					</table>


					
					
				</fieldset>

				<fieldset>
					<legend>월간 토큰 전송 한도 (Monthly Token Transfer Limit)</legend>
					<table class="table table-bordered">
						<colgroup>
							<col width="50%" />
							<col width="50%" />
						</colgroup>
						<thead><tr>
							<th>Token</th>
							<th>limit</th>
						</tr></thead>
						<tbody>
							<?php foreach($getTokenList as $getTokenSingle) { ?>
							<tr>
								<td><?php echo strtoupper($getTokenSingle['token_name']); ?></td>
								<td><input type="text" name="<?php echo $getTokenSingle['token_name'] ?>" value="<?php echo $getTokenSingle['monthly_transfer_limit'] ?>"  class="form-control" required="required" id="krw_per_ctc_kiosk" ></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</fieldset>

				<fieldset>
					<legend>Fees</legend>


					<table class="table table-bordered">
						<colgroup>
							<col width="25%" />
							<col width="25%" />
							<col width="25%" />
							<col width="25%" />
						</colgroup>
						<thead><tr>
							<th>Explanation</th>
							<th>CTC 수수료 회원</th>
							<th>ETH 수수료 회원</th>
							<th>HIGH(일부 회원)</th>
						</tr></thead>
						<tbody>
							<tr>
								<td>*E-Pay 사용시 E-CTC 수수료</td>
								<td>
									<!--₩<input type="text" name="won2_send_etoken_fee" value="<?php echo $setData2['send_etoken_fee']['value']; ?>"  class="form-control settings2" id="won2_send_etoken_fee" >-->
									<input type="text" name="send_etoken_fee" value="<?php echo $setData['send_etoken_fee']['value']; ?>"  class="form-control" required="required" id="send_etoken_fee">
								</td>
								<td>
									<!--₩<input type="text" name="won2_send_etoken_fee_eth" value="<?php echo $setData2['send_etoken_fee_eth']['value']; ?>"  class="form-control settings2" id="won2_send_etoken_fee_eth" >-->
									<input type="text" name="send_etoken_fee_eth" value="<?php echo $setData['send_etoken_fee_eth']['value']; ?>"  class="form-control" required="required" id="send_etoken_fee_eth">
								</td>
								<td>
									<!--₩<input type="text" name="won2_send_etoken_fee_h" value="<?php echo $setData2['send_etoken_fee_h']['value']; ?>"  class="form-control settings2" id="won2_send_etoken_fee_h" >-->
									<input type="text" name="send_etoken_fee_h" value="<?php echo $setData['send_etoken_fee_h']['value']; ?>"  class="form-control" required="required" id="send_etoken_fee_h" >
								</td>
							</tr>
							<tr>
								<td>*Coin ↔  E-Pay <?php echo !empty($langArr['settings_text1']) ? $langArr['settings_text1'] : ''; ?></td>
								<td colspan="2">
									<!--₩<input type="text" name="won2_send_etoken_fee2" value="<?php echo $setData2['send_etoken_fee2']['value']; ?>"  class="form-control settings2" id="won2_send_etoken_fee2" >-->
									<input type="text" name="send_etoken_fee2" value="<?php echo $setData['send_etoken_fee2']['value']; ?>"  class="form-control" required="required" id="send_etoken_fee2">
								</td>
								<td>
									<!--₩<input type="text" name="won2_send_etoken_fee2_h" value="<?php echo $setData2['send_etoken_fee2_h']['value']; ?>"  class="form-control settings2" id="won2_send_etoken_fee2_h">-->
									<input type="text" name="send_etoken_fee2_h" value="<?php echo $setData['send_etoken_fee2_h']['value']; ?>"  class="form-control" required="required" id="send_etoken_fee2_h">
								</td>
						</tbody>
					</table>
					
					<div class="form-group text-center">
						<label></label>
						<button type="submit" class="btn btn-warning" ><?php echo $langArr['submit']; ?> <span class="glyphicon glyphicon-send"></span></button>
					</div>            
				</fieldset>
			</form>
			
		 </div>
	</div>
</div>

<style>
.settings2 {
	background-color: #FDFCE3;
	width: 85%;
	display: inline-block;
	margin-left: 3%;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
   $("#customer_form").validate({
       rules: {
            exchange_rate: {
                required: true,
                minlength: 3
            },
            points_exchange: {
                required: true,
                minlength: 2
            },   
        }
    });

	$(".settings2").on('input', function() {
		var set_id = $(this).attr('id');
		fee_settings2_won_ctc(set_id);
	});
});

function fee_settings2_won_ctc(set_id) {
	var input_won_value = $("#"+set_id).val();
	
	$.ajax({
		url : 'multi.pro.php',
		type : 'POST',
		data : {mode: 'coin_won_change1', won: input_won_value},
		dataType : 'json',
		async: false,
		success : function(resp){
			if ( resp.result == 'y' ) {
				$("#"+set_id.replace('won2_', '')).val(resp.price);
			}
		},
		error : function(resp){
		}
	});
	

}
</script>

<?php include_once 'includes/footer.php'; ?>
