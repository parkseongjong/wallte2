<?php
// Page in use
// CoinIBT 회원 목록 지갑으로 업데이트
// 하루 2번 실행
// cron 등록 : 21.02.05 14:34
require_once './config/config.php';
require_once './config/new_config.php';
require_once './config/config_exchange.php';

echo date('Y-m-d H:i:s')." ~ ";
list($max_value, $last_users_id, $total_count) = ex_get_member_list();
echo date('Y-m-d H:i:s')." ([Total ".$total_count."] ".$max_value." ~ ".$last_users_id.")\n";

?>
