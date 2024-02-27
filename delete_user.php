<?php 
session_start();
require_once 'includes/auth_validate.php';
require_once './config/config.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;
$del_id = filter_input(INPUT_POST, 'del_id');
$db = getDbInstance();
$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

if($_SESSION['admin_type']!='admin'){
    header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized");
}


// Delete a user using user_id
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $db->where('id', $del_id);
    $stat = $db->delete('admin_accounts');
	$walletLogger->info('관리자 모드 > 등록 된 사용자 목록 > 사용자 삭제 처리',['admin_id'=>$_SESSION['user_id'],'user_id'=>$del_id,'url'=>$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'action'=>'D']);
	if ($stat) {
        $_SESSION['info'] = "User deleted successfully!";
        header('location: admin_users.php');
        exit;
    }
}