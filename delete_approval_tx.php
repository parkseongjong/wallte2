<?php 
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

$del_id = filter_input(INPUT_POST, 'del_id');
$user_id = filter_input(INPUT_POST, 'user_id');
$db = getDbInstance();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

if($_SESSION['admin_type']!='admin'){
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}

/* ======================================================= */
/*
2020-07-07 16:30 (KST)		$db->where ("del", 'use');
user approval ���������� ������ �������� �ʰ� use �׸��� del�θ� �����Ѵ�.
In the user approval page, change the use item to del only without actually deleting it.

ethsend ���̺��� ��ȸ�� ������ del ���� use �� ���� ��ȸ�ϸ� �˴ϴ�.
When querying on the ethsend table, you can query that the'del' value is'use'.
*/
/* ======================================================= */

// Delete a user using user_id
if ($del_id && $user_id && $_SERVER['REQUEST_METHOD'] == 'POST') {


	// Add (2020-07-07 15:50 (KST)
	$db = getDbInstance();
	$db->where('id', $del_id);
	$db->where('user_id', $user_id);
	$row = $db->getOne("ethsend");

	$db = getDbInstance();
	$db->where('id', $user_id);
	$rowm = $db->getOne("admin_accounts");

	$updateArr = [] ;
	if ( !empty($row['id']) && $row['ethmethod'] == 'approve' && !empty($rowm['id'])) {
		switch($row['coin_type']) {
			case 'ctc':
				if ( $rowm['sendapproved'] == 'Y' ) {
					$updateArr['sendapproved'] = 'N';
				}
				if ( $rowm['sendapproved_completed'] == 'Y' ) {
					$updateArr['sendapproved_completed'] = 'N';
				}
				break;
			case 'tp':
			case 'mc':
			case 'krw':
			case 'usdt':
				if ( $rowm[$row['coin_type'].'_approved'] == 'Y') {
					$updateArr[$row['coin_type'].'_approved'] = 'N';
				}
				if ( $rowm[$row['coin_type'].'_approved_completed'] == 'Y') {
					$updateArr[$row['coin_type'].'_approved_completed'] = 'N';
				}
				break;
		}

		if ( !empty($updateArr) ) {
			$db = getDbInstance();
			$db->where("id", $user_id);
			$last_id = $db->update('admin_accounts', $updateArr);
		}

	}





	$updateArr2 = [] ;
	$db = getDbInstance();
    $db->where('id', $del_id);
    $db->where('user_id', $user_id);
	$updateArr2['del'] = 'del';
	$updateArr2['deleted_at'] = date("Y-m-d H:i:s");
	$stat = $db->update('ethsend', $updateArr2);

	/*$db = getDbInstance();
    $db->where('id', $del_id);
    $db->where('user_id', $user_id);
    $stat = $db->delete('ethsend');*/
    if ($stat) {
        $_SESSION['info'] = "Transaction deleted successfully!";
        $walletLogger->info('관리자 모드 > User Approval 삭제 처리 / 고유 ID :'.$del_id,['admin_id'=>$_SESSION['user_id'],'user_id'=>0,'url'=>$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'action'=>'D']);
        header('location: admin_user_approval.php?user_id='.$user_id);
        exit;
    }
}