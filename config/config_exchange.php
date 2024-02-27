<?php

include_once (BASE_PATH.'/lib/WalletUtil.php');
use wallet\oldCommon\Util as walletUtil;

define('DB_HOST_EX', "3.35.154.9");
define('DB_USER_EX', "smbit_ctc_exch");
define('DB_PASSWORD_EX', "1234");
define('DB_NAME_EX', "exchange_db");

function getDbInstance_exchange()
{
	return new MysqliDb(DB_HOST_EX,DB_USER_EX,DB_PASSWORD_EX,DB_NAME_EX);
}

define('DB_HOST_EX2', "125.141.133.23");
define('DB_USER_EX2', "testcoinibt_wallet");
define('DB_PASSWORD_EX2', "ptestcoinibtsOrw@rd2");
define('DB_NAME_EX2', "testexchange");

function getDbInstance_testexchange()
{
	return new MysqliDb(DB_HOST_EX2,DB_USER_EX2,DB_PASSWORD_EX2,DB_NAME_EX2);
}

/**
 * array return 값 [$max_value -> coin ibt 고유 id 최대 값, $last_users_id -> 불러온 coin ibt 마지막 고유 id 값 $total_count -> 불러온 coin ibt 계정 count 값]
 * @return array
 */
function ex_get_member_list() {

	$last_users_id = '';
	$total_count = 0;

	$con_exchange_type_value = 'CoinIBT'; // config/new_config.php와 같아야 함

	// db에 저장된 마지막 값 이후의 값만 불러올 수 있도록
	$db = getDbInstance();
	$db->where('account_type2', $con_exchange_type_value);
	$max_value = $db->getValue('admin_accounts', 'max(external_id)');

	if ( !empty($max_value) ) {
		$loadPostData = array(
			'auth_key' => 'E7146GHKUP13',
            'max_id' => $max_value,
//            'max_id' => 'asdasdasd'
//			'max_id' => 3690
		);
		$util = walletUtil::singletonMethod();
		//$resultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/userinfo',$loadPostData));
		$resultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/userinfo',$loadPostData));
		if($resultData['code'] == '806' || $resultData['error'] === true){
			echo($resultData['msg']);
			exit();
		}
		if ( $resultData['count'] > 0 ) {
			foreach($resultData['data'] as $row) {
				// 지갑에 이미 있는 회원인지 확인
				$db = getDbInstance();
				$db->where('external_id', $row['id']);
				$db->where('wallet_address', $row['eth_address']);
				$count = $db->getValue('admin_accounts', 'count(*)');
                if ( $count == 0 ) {

				// 없으면 추가
				// email, phone, n_phone, auth_phone 필드에 값을 넣으면 지갑에서 중복체크 문제가 발생할 수 있기 때문에 넣지 않음
				$insertArr = [];
				$insertArr['name'] = $row['name'];
				//$insertArr['lname'] = '(CoinIBT)';
				//$insertArr['email'] = $row['email'];
				//$insertArr['phone'] = $row['phone_number'];
				$insertArr['account_type2'] = $con_exchange_type_value;
				$insertArr['external_id'] = $row['id'];
				$insertArr['external_phone'] = $row['phone_number'];
				$insertArr['wallet_address'] = $row['eth_address'];
				//if ( !empty($row['country_dialcode']) ) {
				//	$insertArr['n_country'] = $row['country_dialcode'];
				//}
				//$insertArr['n_phone'] = $row['phone_number'];
				$insertArr['auth_name'] = $row['name'];
				$insertArr['login_or_not'] = 'N';
				$insertArr['created_at'] = date("Y-m-d H:i:s");

				$db = getDbInstance();
				$last_id = $db->insert('admin_accounts', $insertArr);

				$last_users_id = $row['id'];
				$total_count = $total_count + 1;
                }

			} // foreach
		} // if

	} // if
	return array($max_value, $last_users_id, $total_count);
}

// send_etoken
/**
 * @param coin $coin e-pay 대문자
 * @param amount $amount
 * @param to_id $to_id 상대 CTC 고유 ID (거래소 고유 ID가 있는 coinIBT 계정)
 * @param from_id $from_id 내 CTC 고유 ID
 */
function ex_set_epay_logs($coin, $amount, $to_id, $from_id) {
	// 거래소에서 epay.id 확인
	$util = walletUtil::singletonMethod();
	$loadPostData = array(
		'auth_key' => 'E7146GHKUP13',
		'coin' => $coin,
	);

	//e-pay info response
	//$ecoinInfoResultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/epayinfo',$loadPostData));
	$ecoinInfoResultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/epayinfo',$loadPostData));

//    var_dump($ecoinInfoResultData);

	if ( !empty($ecoinInfoResultData) && $ecoinInfoResultData['error'] === false ) {
		//real coin info response
		$loadPostData = array(
			'auth_key' => 'E7146GHKUP13',
		);
		//$coinListResultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/coinList',$loadPostData));
		$coinListResultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/coinList',$loadPostData));
//        var_dump($coinListResultData);

		if($coinListResultData['code'] != 200 || $coinListResultData['error'] === true){
			echo('오류 발생');
			exit();
		}
		else{
			$eCoinName = str_replace('E-', '', $coin);
			foreach ($coinListResultData['data'] as $value){
				if($value['short_name'] == $eCoinName){
					$realCoinInfo = $value;
					break;
				}
			}
			unset($coinListResultData,$eCoinName);
//            var_dump($realCoinInfo);
		}


		// 지갑회원에서 거래소고유번호 확인
		$db = getDbInstance();
		$db->where('id', $to_id);
		$touserData = $db->getOne('admin_accounts');

		if ( !empty($touserData) && $touserData['external_id'] > 0) {
			$loadPostData = array(
				'auth_key' => 'E7146GHKUP13',
				'user_id' => $touserData['external_id'], //거래소 고유 user ID
				//'user_id' => '15474AA', //거래소 고유 user ID
				'cryptocoin_id' => $realCoinInfo['id'],
				//'cryptocoin_id' => '15ADDASD',
				'amount' => $amount,
				//'amount' => '5001A',
				'wallet_address' => $touserData['wallet_address'],
				'type' => 'purchase',
				'remark' => 'airdrop reward',
				'status' => 'completed',
				'epay_id' => $ecoinInfoResultData['epay_id'],
				//'epay_id' => 9999,
				'target' => 'CTC Wallet',
				'target_id' => $from_id,
			);
//            var_dump($loadPostData);
			/*
             *
             * REAL 적용 시 real url 적용 해야 함.
             *
             *
             */
			//$ecoinInfoResultData = $util->jsonDecode($util->getCurl('http://testexchange.hansbiotech.kr/api/wallet/deposit',$loadPostData));
			//$ecoinInfoResultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/deposit',$loadPostData));
			$ecoinInfoResultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/deposit',$loadPostData));

			//var_dump($ecoinInfoResultData);
			/*
            if($ecoinInfoResultData['code'] != 200 || $ecoinInfoResultData['error'] === true){
                echo('오류 발생');
                exit();
            }
            */

		}
		else {
			// 회원정보를 찾을 수 없음
		}
	}
	else {
		// 코인 정보 없음
	}
} //

// t_cron_coinprice
function ex_get_coin_price() {
	$result = array();

	$second_id = '';

	$util = walletUtil::singletonMethod();
	$loadPostData = array(
		'auth_key' => 'E7146GHKUP13',
	);
	//$coinListResultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/coinList',$loadPostData));
	$coinListResultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/coinList',$loadPostData));

	//coin 고유 id를 20으로 고정 할 수 있지만, 변동 될 수 있으니, 한번 조회해서 찾는다.
	foreach ($coinListResultData['data'] as $value){
		if($value['short_name'] == 'KRW'){
//            if($value['status'] == '0'){
//                echo('사용 불가 coin 입니다.');
//                exit();
//            }
			$second_id = $value['id'];
			$secondShortName = $value['short_name'];
			break;
		}
	}

	//KRW 기준 각 코인 시세 build
	$krwConvertDataArray = array();
	foreach ($coinListResultData['data'] as $value){
		if($value['id'] != $second_id){
			//KRW가 아닐때 코인 가격 요청
			$loadPostData = array(
				'auth_key' => 'E7146GHKUP13',
				'seccond_coin' => $secondShortName,
				'first_coin' => $value['short_name'],
			);
			//array_push($krwConvertDataArray,['convertData'=>$util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/price',$loadPostData)),'coinInfo'=>$value]);
			array_push($krwConvertDataArray,['convertData'=>$util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/price',$loadPostData)),'coinInfo'=>$value]);
		}
	}
	unset($coinListResultData,$coinListResultData);
	//var_dump($krwConvertDataArray);
	/*
        krwConvertDataArray return
        array(......) {
          [0]=>
          array(2) {
            ["convertData"]=>
            array(4) {
              ["code"]=>
              string(3) "200"
              ["error"]=>
              bool(false)
              ["msg"]=>
              string(7) "Success"
              ["data"]=>
              array(3) {
                ["price"]=>
                string(9) "44,687.00"
                ["first_coin_status"]=>
                string(1) "1"
                ["seccond_coin_status"]=>
                string(1) "1"
              }
            }
            ["coinInfo"]=>
            array(3) {
              ["id"]=>
              int(1)
              ["short_name"]=>
              string(3) "BTC"
              ["status"]=>
              string(1) "1"
            }
          }
        }
     */
	foreach($krwConvertDataArray as $value) {
		$price = $util->priceFilter($value['convertData']['data']['price']);

		$db = getDbInstance();
		$db->where('module_name', 'krw_per_coin');
		$db->where('coin_type', $value['coinInfo']['short_name']);
		$setData = $db->getOne('settings2', 'id');

		if (!empty($setData)) {
			$updateArr = [];
			$updateArr['value'] = $price;
			$updateArr['updated'] = date("Y-m-d H:i:s");
			$db = getDbInstance();
			$db->where('id', $setData['id']);
			$last_id = $db->update('settings2', $updateArr);
		}
		else {
			$insertArr = [];
			$insertArr['module_name'] = 'krw_per_coin';
			$insertArr['coin_type'] = $value['coinInfo']['short_name'];
			$insertArr['value'] = $price;
			$insertArr['updated'] = date("Y-m-d H:i:s");
			$db = getDbInstance();
			$last_id = $db->insert('settings2', $insertArr);
		}

		$result[] = $value['coinInfo']['short_name'].':'.$price;

	} // foreach

	//var_dump(implode(',', $result));
	return implode(',', $result); // 로그파일 기록용 / Used only for recording in log file
} //

// 실시간 거래소 코인가격 리턴 (KRW)
// $coin1 : KRW (20)
// $coin_type : cryptocoin.short_name : BTC, USDT, TP3, ETH, MC, KRW, CTC, XRP, BNB
function ex_get_coin_price_one($coin1, $coin_type) {
	$second_id = '';
	$price = 0;
	$coin1 = strtoupper($coin1);
	$coin_type = strtoupper($coin_type);

	$util = walletUtil::singletonMethod();
	$loadPostData = array(
		'auth_key' => 'E7146GHKUP13',
	);
	//$coinListResultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/coinList',$loadPostData));
	$coinListResultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/coinList',$loadPostData));

	//coin 고유 id를 20으로 고정 할 수 있지만, 변동 될 수 있으니, 한번 조회해서 찾는다.
	foreach ($coinListResultData['data'] as $value){
		if($value['short_name'] == $coin1){
			$second_id = $value['id'];
			$secondShortName = $value['short_name'];
			break;
		}
	}

	//조회 할 코인 정보 build
	foreach ($coinListResultData['data'] as $value){
		if($value['short_name'] == $coin_type){
			$selectTargetCoinInfo = $value;
			break;
		}
	}

	//KRW 기준 코인 시세 build
	$loadPostData = array(
		'auth_key' => 'E7146GHKUP13',
		'seccond_coin' => $secondShortName,
		'first_coin' => $selectTargetCoinInfo['short_name'],
	);

	//$coinInfoResultData = $util->jsonDecode($util->getCurl('https://www.coinibt.io/api/wallet/price',$loadPostData));
	$coinInfoResultData = $util->jsonDecode($util->getCurl('https://www.bitsomon.com/api/wallet/price',$loadPostData));

	return $util->priceFilter($coinInfoResultData['data']['price']);
}

?>