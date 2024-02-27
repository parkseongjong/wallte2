<?php
// Page in use
// CoinIBT : 코인 가격(KRW)을 지갑으로 업데이트
// 30분마다 실행
// cron 등록 : 21.02.17 10:44
require_once './config/config.php';
require_once './config/new_config.php';
require_once './config/config_exchange.php';

echo date('Y-m-d H:i:s')." ~ ";
$result = ex_get_coin_price();

// CoinIBT로부터 가져온 값 업데이트 : settings2 => settings
new_ex_set_coin_price();


/*
// settings2에 입력된 'krw_per_coin'이 아닌 나머지 module_name의 value(단위: 원)를 CTC로 변환해서 settings에 업데이트해야 한다.
// settings2.module_name='send_token_fee'의 value가 1000이고
// settings2.module_name='krw_per_coin'&&coin_type='ctc'의 value가 500이라면 (1CTC = 500원)
// settings.module_name='krw_per_coin'의 value에 업데이트되어야 하는 값 : 1000원이 몇 CTC인지 
$db = getDbInstance();
$setDatas2 = $db->get('settings2');
if ( !empty($setDatas2) ) {
	foreach($setDatas2 as $row) {
		if ( $row['module_name'] == 'krw_per_coin' ) {
			$db = getDbInstance();
			$db->where('module_name','krw_per_'.$row['coin_type'].'_kiosk');
			$r1 = $db->getOne('settings');
			if ( empty($r1) ) {
				$insertArr = [];
				$insertArr['module_name'] = 'krw_per_'.$row['coin_type'].'_kiosk';
				$insertArr['show_name'] = 'KRW per '.strtoupper($row['coin_type']);
				$insertArr['type'] = 'ex_rate';
				$insertArr['value'] = $row['value'];
				$insertArr['created'] = date("Y-m-d H:i:s");
				$insertArr['exp'] = '1 '.strtoupper($row['coin_type']).' = ? 원';
				$last_id = $db->insert('settings', $insertArr);
			} else {
				$db = getDbInstance();
				$db->where('id', $r1['id']);
				$updateArr = [];
				$updateArr['value'] = $row['value'];
				$updateArr['modified'] = date("Y-m-d H:i:s");
				$last_id = $db->update('settings', $updateArr);
			}
		} else {
			$new_price = new_coin_price_change_1won('CTC', $row['value'], '');
			if ( is_numeric($new_price) && $new_price > 0 ) {
				$db = getDbInstance();
				$db->where('module_name', $row['module_name']);
				
				$updateArr = [];
				$updateArr['value'] = $new_price;
				$updateArr['modified'] = date("Y-m-d H:i:s");
				$last_id = $db->update('settings', $updateArr);
			}

		}
	}
}
*/
echo date('Y-m-d H:i:s')." (".$result.")\n";

?>
