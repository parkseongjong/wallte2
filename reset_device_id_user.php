<?php 
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;
$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);
$reset_id = filter_input(INPUT_POST, 'reset_id');
$db = getDbInstance();

if($_SESSION['admin_type']!='admin'){
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}


// Update a user using user_id
if ($reset_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $db->where('id', $reset_id);
    $updateArr = [];
    $updateArr['device'] = NULL;
    $updateArr['devId'] = NULL;
    $updateArr['devId2'] = NULL;
    $updateArr['devId3'] = NULL;

    $stat = $db->update('admin_accounts', $updateArr);
    if ($stat) {
        $_SESSION['info'] = "Update successfully!";

        $walletLogger->info('XdevId을 제거(수정) 하였습니다.',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$reset_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
    } else {
        $_SESSION['info'] = "Update fail";
        $walletLogger->error('XdevId을 제거(수정)에 실패 하였습니다.',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>$reset_id,'url'=>$walletLoggerUtil->getUrl(),'action'=>'E']);
    }

} else {
    $_SESSION['info'] = "Invalid use";
}
if ( isset($_POST['queries']) && !empty($_POST['queries']) ) {
	header('location: admin_users.php?'.$_POST['queries']);
} else {
	header('location: admin_users.php');
}
exit;
