<?php 
// Test Page
//session_start();
/*
header('Content-Type: application/json');

define('WALLET_BASE_PATH', '/var/www/html/wallet2');
// https://cybertronchain.com/wallet2/test_send_other_latoken_api.php
require_once WALLET_BASE_PATH.'/config/config.php';
require_once WALLET_BASE_PATH.'/config/new_config.php';

//require_once './includes/auth_validate.php';


//$ok_json = array('code'=>200,'error'=>false, 'msg'=>'Success');

$db = getDbInstance();
$aa = [];
$aa['user_id'] = '5137';
$aa['wallet_address']= 'test';
$aa['coin_type'] = 'ectc';
$aa['amount'] = '10';
$aa['epay_send'] = 'Y';
$aa['created'] = date("Y-m-d H:i:s");
$db->insert('z_user_epay_process', $aa);


//jsonReturn($ok_json);

function jsonReturn($arr='') {
    if (empty($arr)) {
        $arr = array('code'=>'999', 'error'=>true, 'msg'=>'Error');
        echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    } else {
        if (is_array($arr)) {
            echo json_encode($arr, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('code'=>'999', 'error'=>true, 'msg'=>$arr), JSON_UNESCAPED_UNICODE);
        }
    }
    //logWrite($arr);
    exit();
}
*/


?>