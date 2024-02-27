<?php
// Page in use
// coupon_buy.php에 접속하면 kcp_order에 쌓임
// 지난 날짜는 주기적으로 삭제해 줄 필요가 있다.
// 하루에 한번 실행
// 매일 11시 59분 실행

// https://cybertronchain.com/wallet2/t_cron_payment_del.php


require_once './config/config.php';
require_once './config/new_config.php';

$beforeDay = date("Y-m-d H:i:s", strtotime(" -1 day"));
echo date('Y-m-d H:i:s')." ~ ";
//echo $beforeDay.'<br />';

$db = getDbInstance();
$db->where("order_status", '9');
$db->where("wdate", $beforeDay, '<=');


$stat = $db->delete('kcp_order');


//$kcpLogs = $db->get('kcp_order');
//if ( $db->count > 0 ) {
//	echo $db->count;
//}

echo date('Y-m-d H:i:s')."\n";

?>