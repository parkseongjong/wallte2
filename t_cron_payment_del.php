<?php
// Page in use
// coupon_buy.php�� �����ϸ� kcp_order�� ����
// ���� ��¥�� �ֱ������� ������ �� �ʿ䰡 �ִ�.
// �Ϸ翡 �ѹ� ����
// ���� 11�� 59�� ����

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