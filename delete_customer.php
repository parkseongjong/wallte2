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

$del_id = filter_input(INPUT_POST, 'del_id');
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') 
{

	if($_SESSION['admin_type']!='super'){
		$_SESSION['failure'] = "You don't have permission to perform this action";
    	header('location: customers.php');
        exit;

	}
    $customer_id = $del_id;

    $db = getDbInstance();
    $db->where('id', $customer_id);
    $status = $db->delete('customers');
    
    if ($status) 
    {
        $_SESSION['info'] = "Customer deleted successfully!";
        $walletLogger->info('관리자 모드 > Customer deleted / 고유 ID:'.$del_id,['admin_id'=>$_SESSION['user_id'],'user_id'=>0,'url'=>$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'action'=>'D']);
        header('location: customers.php');
        exit;
    }
    else
    {
    	$_SESSION['failure'] = "Unable to delete customer";
        $walletLogger->error('관리자 모드 > Customer deleted / 고유 ID:'.$del_id,['admin_id'=>$_SESSION['user_id'],'user_id'=>0,'url'=>$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'action'=>'D']);
    	header('location: customers.php');
        exit;

    }
    
}