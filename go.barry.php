<?php
// Page in use
session_start();

require_once './config/config.php';
require_once 'includes/auth_validate.php';

$ret = array('err'=>'fail');

if(empty( $_SESSION['user_id'] )) {
	echo json_encode($ret);
	exit();
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $db = getDbInstance();
    $db->where("id", $_SESSION['user_id']);
    $row = $db->get('admin_accounts');

    $email = $row[0]['email'];
    $registerWith = $row[0]['register_with'];
    //2021.10.27 By.OJT ISO27001로 DB내 개인정보 저장 안함.
    //$showHeader = ($registerWith=="phone") ? $email : $row[0]['phone'];

    if ($row[0]['n_phone']) $showHeader = $row[0]['n_phone'];
    if ($row[0]['auth_phone']) $showHeader = $row[0]['auth_phone'];

    $key = '3456789012345678901234';
    $key_128 = substr($key, 0, 128/8);
    $key_256 = substr($key, 0, 256/8);
    $now_millis = time();

    //$enc = openssl_encrypt($showHeader, 'AES-128-CBC', $key_128, 0, $key_128);
    $enc = openssl_encrypt($now_millis, 'AES-256-CBC', $key_256, 0, $key_128);

    $enc = str_replace('+', '', $enc);

    $data_to_barry = [];
    $data_to_barry['ctc_key'] = $enc;
    $data_to_barry['mb_id'] = $_SESSION['user_id'];
    //2021.10.27 By.OJT ISO27001로 DB내 개인정보 저장 안함.
    //$data_to_barry['email'] = $showHeader;
    $data_to_barry['regdate'] = date('Y-m-d H:i:s');
    $data_to_barry['reg_mtime'] = $now_millis;

    $db = getDbInstance();
    if ($db->insert('barry_auth', $data_to_barry)) {
        $ret = array('msg' => $enc);
    }

    //barry 에 제3 개인정보 제공 동의를 이미 했다면 Agree 값도 같이 넘겨주기.
    if($row[0]['barry_personal_information'] >= 1){
        $ret['firstVisitAgree'] = 'N';
    }
    else{
        $ret['firstVisitAgree'] = 'Y';
    }

	// 201130
	$data_to_wallet = [];
	$data_to_wallet['ckey'] = $enc;
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$last_id = $db->update('admin_accounts', $data_to_wallet);


}

echo json_encode($ret);


?>
