<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once WALLET_PATH.'/config/config_admin.php';
if (!defined('WALLET_ADMIN')) exit;

$del_id = filter_input(INPUT_POST, 'del_id');
$db = getDbInstance();

// Delete a user using user_id
if ($del_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $db->where('id', $del_id);
    $stat = $db->delete('blocked_admin_ips');
    $walletLogger->info('관리자 모드 > 관리자 페이지 접근 제어 > IP 삭제 처리 / 고유 ID :'.$del_id,['admin_id'=>$_SESSION['user_id'],'user_id'=>0,'url'=>$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'action'=>'D']);
    if ($stat) {
        $_SESSION['info'] = "IP deleted successfully!";
        header('location: blocked_admin_ip.php');
        exit;
    }
}