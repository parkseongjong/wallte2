<?php
// Page in use
// 
ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);  

require_once './config/config.php';
require_once './config/new_config.php';

/*
https://cybertronchain.com/wallet2/upload1.pro7.php?use_type=
*/

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;
use Web3\Utils;


$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;


echo '테스트 전입니다. 실행할 수 없습니다.';
return;
exit;

$use_type = filter_input(INPUT_GET, 'use_type');


if ( $use_type == '' ) {
	echo 'use_type을 설정해주세요.';
	exit();
}


if ( $use_type == 'USED3' ) {
	$delete_coin_arr = array('mc');
} else if ( $use_type == 'USED4' ) {
	$delete_coin_arr = array('mc');
} else if ( $use_type == 'USED6' ) {
	$delete_coin_arr = array('tp', 'mc');
}

$db = getDbInstance();
$db->where("use1", $use_type);

$resultData = $db->get('z_user_address_list2');



if ( !empty($resultData) ) {
	foreach($resultData as $k=>$row) {

		$result ='';
		echo '<br />'.$row['id'].' : <a href="https://cybertronchain.com/wallet2/admin_user_approval.php?user_id='.$row['admin_accounts_id'].'" target="_blank">'.$row['admin_accounts_id'].'</a><br />';
		//echo $row['id']. ' : '.$row['admin_accounts_id'].'<br />';
		$db = getDbInstance();
		$db->where("id", $row['admin_accounts_id']);
		$user_row = $db->getOne('admin_accounts');
		
		if ( !empty($user_row) ) {
			$user_id = $user_row['id'];
			$wallet_address = $user_row['wallet_address'];
			
			$admin_type = $user_row['admin_type'];
			$registerWith = $user_row['register_with'];
			$transfer_approved = $user_row['transfer_approved'];

			if ( $user_row['id'] >= 10900 ) {
				$walletAddress = $user_row['wallet_address'];
			} else {
				if ( $user_row['wallet_change_apply'] == 'Y' ) {
					$walletAddress = $user_row['wallet_address'];
				} else {
					$walletAddress = $user_row['wallet_address_change']; //--------- (New Wallet Address)
				}
			}

			if ( $admin_type != 'admin' && $registerWith != "email") { //  && $transfer_approved == 'C' // 
				

				foreach ($delete_coin_arr as $val ) {
					$db = getDbInstance();
					$db->where ("user_id", $user_id);
					$db->where ("coin_type", $val);
					$db->where('ethmethod', 'approve');
					$db->where('del', 'use');

					$updateArr = [];
					$updateArr['del'] = 'del';
					$updateArr['deleted_at'] = date("Y-m-d H:i:s");
					$last_id = $db->update('ethsend', $updateArr);
					

				} // foreach

			}


		}
		
	} // foreach
} else { // if
	echo '데이터가 없습니다.';
}

?>
