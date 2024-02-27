<?php
// Test Page
session_start();
ini_set('max_execution_time', 0); 
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

// https://cybertronchain.com/wallet2/admin_tmp_list1_export.php
//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}
$filename = time().'export.csv';
 header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="'.$filename.'";');

$db = getDbInstance();
$query = '';
if ( isset($_GET['admin_type']) && !empty($_GET['admin_type']) ) {
	$db->where('admin_type', $_GET['admin_type']);
}

	//$query = " WHERE a.id='709' and e.user_id='709'";

//$db->where('email', 'ajay@mailinator.com', '!=');
//$db->where('admin_type', 'admin');
//$db->orderBy('id', 'DESC');
//$result = $db->get('admin_accounts'); 

//$query = " where a.register_with='phone' and a.email like '%+82%'";
//$query = " where a.register_with='phone' and a.email like '%+82%' and a.tp_approved='N' and e.ethmethod='approve' and e.coin_type='tp'";
//$query = " where a.register_with='phone' and a.email like '%+82%' and e.ethmethod='sendTransaction' and e.coin_type='all'";
//$result = $db->rawQuery("SELECT a.id as a_id, a.register_with, a.lname, a.name, a.email, a.wallet_address, a.phone, a.created_at, a.auth_phone, a.auth_name, e.* FROM `admin_accounts` as a LEFT JOIN `ethsend` as e on a.id=e.user_id" .$query." order by a.id asc"); //

//$result = $db->rawQuery("select * from admin_accounts where register_with = 'phone' and admin_type = 'user' and account_type = 'real' and wallet_address IS NOT NULL  and phone like '+82%' and id_auth='N' and last_login_at is NULL and transfer_passwd is NULL order by id asc");
//$result = $db->rawQuery("select id, wallet_address from admin_accounts where id=5885 or id=5137 order by id asc");

//$result = $db->rawQuery("select * from admin_accounts where (register_with = 'phone' and admin_type = 'user' and account_type = 'real' and wallet_address IS NOT NULL  and phone like '+82%' ) or (id=5309) order by id asc");
//$result = $db->rawQuery("select * from admin_accounts where id=5309 order by id asc");
//$result = $db->rawQuery("select * from admin_accounts where login_or_not='N'");

//$result = $db->rawQuery("SELECT * FROM `admin_accounts` where id >= 10900 and admin_type != 'admin' and transfer_approved != 'C' ");



//$result = $db->rawQuery("SELECT * FROM `admin_accounts` where id < 10900 and admin_type != 'admin' and transfer_approved != 'C' ");

$result = $db->rawQuery("SELECT * FROM `admin_accounts` where id < 10900 ");


$file = fopen('php://output', 'w');


//$headers = array('#','Register with','Name','Email','Wallet Address','ETH Balance','Phone','Date', 'auth_phone', 'e.tx_id', 'e.ethmethod', 'e.amount', 'e.coin_type', 'e.to_address', 'e.from_address', 'e.created', 'status', 'del');

//$headers = array('#','wallet_address','eth','ctc','tp3','krw','usdt', 'mc', 'last_login');

$headers = array('#','email','register_with','name','admin_type','email_verify','login_or_not', 'wallet_address', 'pvt_key', 'created_at', 'last_login_at', 'auth_phone', 'wallet_address_change', 'wallet_change_apply', 'eth', 'ctc', 'tp3', 'mc', 'krw', 'usdt', 'bee', 'ectc', 'etp3', 'emc');

//$result = $db->get('admin_accounts'); 


require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

fputcsv($file,$headers);
$k=1;
$before_id = '';
foreach ($result as $row) {

	$old_wallet_address = '';
	if ( $row['wallet_change_apply'] == 'Y' ) {
		$old_wallet_address = $row['wallet_address_change'];
	} else {
		$old_wallet_address = $row['wallet_address'];
	}

	// W, N, Y, 
	/*
	// Get Balance
	if ( empty($row['last_login_at']) && empty($row['transfer_passwd']) && $row['id_auth'] == 'N' ) {
		$getbalances = $wi_wallet_infos->wi_get_balance('', 'all', $row['wallet_address'], $contractAddressArr);
	} else {
		$getbalances = array();
	}
	*/

	if ( $old_wallet_address != '' ) {

		$getbalances = $wi_wallet_infos->wi_get_balance('', 'all', $old_wallet_address, $contractAddressArr);

		$name = '';
		$name = get_user_real_name(mb_convert_encoding( htmlspecialchars($row['auth_name']), "EUC-KR", "UTF-8" ), mb_convert_encoding( htmlspecialchars($row['name']), "EUC-KR", "UTF-8" ), mb_convert_encoding( htmlspecialchars($row['lname']), "EUC-KR", "UTF-8" ));
		
		
		/*$userEthAmt = 0;
		if ($row['wallet_address'] != '' ) {
			$userEthAmt = getMyETHBalance($row['wallet_address'], $n_connect_ip, $n_connect_port);
		}

		$status = '';
		if ( !empty($row['tx_id']) ) {
			$eth->getTransactionReceipt($row['tx_id'], function ($err, $transaction) use (&$status) {
				if ($err !== null) {
					 //echo $err->getMessage();
					$status = 'Pending';
				}
				else {
					
					if(!empty($transaction) && !empty($transaction->status)){
						if(hexdec($transaction->status)==1){
							$status = 'Completed';
						}
						else if(hexdec($transaction->status)==0){
							$status = 'Failed';
						}
						else {
							$status = 'Pending';
						}
					}
					
				}
			});
		}
		*/
		
		$arr = [];
		/*
		$arr['#'] = $row['id'];
		$arr['wallet_address'] = $row['wallet_address'];
		
		if ( empty($row['last_login_at']) && empty($row['transfer_passwd']) && $row['id_auth'] == 'N' ) {
			$arr['eth'] = new_number_format($getbalances['eth'], $n_decimal_point_array['eth']);
			$arr['ctc'] = new_number_format($getbalances['ctc'], $n_decimal_point_array['ctc']);
			$arr['tp3'] = new_number_format($getbalances['tp3'], $n_decimal_point_array['tp3']);
			$arr['krw'] = new_number_format($getbalances['krw'], $n_decimal_point_array['krw']);
			$arr['usdt'] = new_number_format($getbalances['usdt'], $n_decimal_point_array['usdt']);
			$arr['mc'] = new_number_format($getbalances['mc'], $n_decimal_point_array['mc']);
		} else {
			$arr['eth'] = '-';
			$arr['ctc'] = '-';
			$arr['tp3'] = '-';
			$arr['krw'] = '-';
			$arr['usdt'] = '-';
			$arr['mc'] = '-';
		}
		*/
		
		$arr['#'] = $row['id'];
		$arr['email'] = '="'.$row['email'].'"';
		$arr['register_with'] = $row['register_with'];
		$arr['name'] = $name;
		$arr['admin_type'] = $row['admin_type'];
		$arr['email_verify'] = $row['email_verify'];
		$arr['login_or_not'] = $row['login_or_not'];
		$arr['wallet_address'] = $old_wallet_address;
		$arr['pvt_key'] = $row['pvt_key'];
		$arr['created_at'] = $row['created_at'];
		$arr['last_login_at'] = $row['last_login_at'];
		$arr['auth_phone'] = '="'.$row['auth_phone'].'"';
		$arr['wallet_address_change'] = $row['wallet_address_change'];
		$arr['wallet_change_apply'] = $row['wallet_change_apply'];
		
		$arr['eth'] = new_number_format($getbalances['eth'], $n_decimal_point_array['eth']);
		$arr['ctc'] = new_number_format($getbalances['ctc'], $n_decimal_point_array['ctc']);
		$arr['tp3'] = new_number_format($getbalances['tp3'], $n_decimal_point_array['tp3']);
		$arr['mc'] = new_number_format($getbalances['mc'], $n_decimal_point_array['mc']);
		$arr['krw'] = new_number_format($getbalances['krw'], $n_decimal_point_array['krw']);
		$arr['usdt'] = new_number_format($getbalances['usdt'], $n_decimal_point_array['usdt']);

		$db = getDbInstance();
		$db->where("user_id", $row['id']);
		$pointSum = $db->getValue("store_transactions", "sum(points)");
		$arr['bee'] = number_format($pointSum);
		$arr['ectc'] = $row['etoken_ectc'];
		$arr['etp3'] = $row['etoken_etp3'];
		$arr['emc'] = $row['etoken_emc'];
		


		/*$arr = [];
		$arr['#'] = $row['a_id'];
		$arr['Register with'] = $row['register_with'];
		$arr['Name'] = $name;
		$arr['Email'] = '="'.htmlspecialchars($row['email']).'"';
		$arr['Wallet Address'] = htmlspecialchars($row['wallet_address']);
		$arr['ETH Balance'] =  new_number_format($userEthAmt, $n_decimal_point_array['eth']);
		$arr['Phone'] = $row['phone'] != '' ? '="'.htmlspecialchars($row['phone']).'"' : '';
		$arr['Date'] = htmlspecialchars($row['created_at']);
		$arr['auth_phone'] = '="'.$row['auth_phone'].'"';
		$k++;

		$arr['e.tx_id'] = $row['tx_id'];
		$arr['e.ethmethod'] = $row['ethmethod'];
		$arr['e.amount'] = $row['amount'];
		$arr['e.coin_type'] = $row['coin_type'];
		$arr['e.to_address'] = $row['to_address'];
		$arr['e.from_address'] = $row['from_address'];
		$arr['e.created'] = $row['created'];
		$arr['status'] = $status;
		$arr['del'] = $row['del'].' ( '.$row['deleted_at'].')';
		*/
		fputcsv($file,$arr);

	} // if
	//$before_id = $row['a_id'];
}
fclose($file);
die;




?>	
