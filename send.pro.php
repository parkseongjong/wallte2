<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

use wallet\common\Log as walletLog;
use wallet\common\Filter as walletFilter;
use wallet\common\Info as walletInfo;

require __DIR__ .'/vendor/autoload.php';

$filter = walletFilter::getInstance();

//2021-11-09 XSS Filter by.ojt
$targetPostData = array(
    'mode' => 'string',
    'waddr' => 'string',
    'waddr2' => 'string',
    'token' => 'string',
    'mid' => 'string',
    'pas1' => 'string',
    'page' => 'string',
    'user_id' => 'string',
    'user_type' => 'string',
	'token_name' => 'string',
	'wallet_addr' => 'string',
);

$filterData = $filter->postDataFilter($_POST,$targetPostData);
unset($targetPostData);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //2021-07-31 LOG 기능 추가 By.OJT
    $log = new walletLog();

    $mode = $filterData['mode'];

	//휴면 계정 확인용 컬럼.
    $column = array(
        'A.account_type2','A.virtual_wallet_address','A.id_auth','A.wallet_address_change',
        'B.id','B.email','B.wallet_phone_email','B.register_with','B.passwd','B.passwd_new','B.passwd_salt','B.passwd_datetime',
        'B.name','B.lname','B.user_ip','B.phone','B.gender','B.dob','B.location','B.auth_phone','B.auth_name','B.auth_gender',
        'B.auth_dob','B.auth_local_code','B.n_country','B.n_phone','B.device','B.devId','B.devId2','B.devId3'
    );
	switch($mode) {
		
		case 'tokenBalance':
			 $tokenName = $filterData['token_name'];
			 $walletAddress = $filterData['wallet_addr'];
			
			 $wi_wallet_infos = new walletInfo();
			 $getNewCoinBalance = $wi_wallet_infos->wi_get_balance('2', $tokenName, $walletAddress, $contractAddressArr);
		
			$respArr = ["balance"=>$getNewCoinBalance];
			
			echo json_encode($respArr);
		break;

		// send_token, send_other
		case 'validateWithdrawalWalletAddress':
            $waddr = $filterData['waddr'];
			$r_name = '';

			$db = getDbInstance();
			$db->where("u_address", $waddr);
			$db->where("user_id", $_SESSION['user_id']);
			$checkValidAddr = $db->get('user_withdrawal_addresses');
			
			if(!empty($checkValidAddr)){
				$respArr = ["isValid"=>true];
			}
			else {
				$respArr = ["isValid"=>false];
			}
			echo json_encode($respArr);
		break;

        case 'wallet_check' :

            //CTC회원이 1순위 출금주소 2순위
            //최초 주소를 받기전 주소를 받아서 CTC 월렛인지 먼저 체크한다.

            $waddr = $filterData['waddr'];
            $r_name = '';

            $db = getDbInstance();
            $db->where ("A.wallet_address", $waddr);
            $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");

            $userData = $db->get('admin_accounts A',null,$column);

            if ($db->count < 1) {
                $db->where ("wallet_address", $waddr);
                $userData = $db->get('admin_accounts');
            }

            $r_name = get_user_real_name($userData[0]['auth_name'], $userData[0]['name'], $userData[0]['lname']);

            if($r_name != ''){
            echo json_encode($r_name);
            }else{
                $db = getDbInstance();
                $db->where("u_address", $waddr);
                $db->where("user_id", $_SESSION['user_id']);
                $checkValidAddr = $db->get('user_withdrawal_addresses');

                if(!empty($checkValidAddr)){
                    $respArr = ["isValid"=>true];
                    $r_name = $checkValidAddr[0]['u_name'];
                    echo json_encode($r_name);
                }
                else {
                    $respArr = ["isValid"=>false];
                }
                
            }

            break;

		// send_token, send_other
		case 'get_name':
            $waddr = $filterData['waddr'];
			$r_name = '';

			$db = getDbInstance();
			//2021.06.16 by.OJT 휴면 회원은 조회 되어야 함.
            //휴면 회원 쪽 조회 START
            $db->where ("A.wallet_address", $waddr);
            $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");

            $userData = $db->get('admin_accounts A',null,$column);

            if ($db->count < 1) {
                $db->where ("wallet_address", $waddr);
                $userData = $db->get('admin_accounts');
            }
            //휴면 회원 쪽 조회 END
			//$db->where ("wallet_address", $waddr);
			//$userData = $db->get('admin_accounts');
			if ($db->count >= 1) {
			    //리얼 지갑 주소 조회
                $log->info('get_name REAL 계좌조회',['target_id'=>$userData[0]['id'],'action'=>'S']);

				$r_name = get_user_real_name($userData[0]['auth_name'], $userData[0]['name'], $userData[0]['lname']);
				if ( !empty($userData[0]['account_type2']) && $userData[0]['account_type2'] != 'wallet' ) {
					$r_name = '('.$userData[0]['account_type2'].') '.$r_name;
				}
				// 지갑으로 전송하는 경우가 아닌 거래소로 전송하는 경우, 21.04.14
				if ( $userData[0]['account_type2'] == $con_exchange_type_value ) {
					if ( new_receive_getname_check($_SESSION['user_id'], $userData[0]['id']) == false ) {
						$r_name = 'coinibt_false';
					}
				}
			}
			else {
				$db = getDbInstance();
                //휴면 회원 쪽 조회 START
                $db->where ("virtual_wallet_address", $waddr);
                $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
                $storeData = $db->get('admin_accounts A',null,$column);
                if ($db->count < 1) {
                    $db->where ("virtual_wallet_address", $waddr);
                    $storeData = $db->get('admin_accounts');
                }
                //휴면 회원 쪽 조회 END
				//$db->where ("virtual_wallet_address", $waddr);
				//$storeData = $db->get('admin_accounts');
				if ($db->count >= 1) {
                    //가상 지갑 주소 조회
                    $log->info('get_name Virtual 계좌조회',['target_id'=>$storeData[0]['id'],'action'=>'S']);

					$r_name = get_user_real_name($storeData[0]['auth_name'], $storeData[0]['name'], $storeData[0]['lname']);
					$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
					$r_name = $r_name.$virtual_account_tx1;
				}
			}

			//$title = !empty($langArr['send_member_name']) ? $langArr['send_member_name'] : "Receiver";
			echo json_encode($r_name);
			//echo json_encode(array('result'=>$r_name));
			break;
		
		// send_etoken
        case 'get_name2':

            $waddr = $filterData['waddr'];

            //2021.06.16 by.OJT 휴면 회원은 조회 되어야 함.
		    //if($_SERVER['REMOTE_ADDR'] == '112.171.120.140'){
                $r_name = '';

                $db = getDbInstance();

                //휴면 회원 쪽 조회 START
                $db->where ("A.wallet_address", $waddr);
                $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");

                $userData = $db->get('admin_accounts A',null,$column);

                if ($db->count < 1) {
                    $db->where ("wallet_address", $waddr);
                    $userData = $db->get('admin_accounts');
                }
                //휴면 회원 쪽 조회 END

                if ($db->count >= 1) {
                    $log->info('get_name2 REAL 계좌조회',['target_id'=>$userData[0]['id'],'action'=>'S']);
                    $r_name = get_user_real_name($userData[0]['auth_name'], $userData[0]['name'], $userData[0]['lname']);
                    if ( !empty($userData[0]['account_type2']) && $userData[0]['account_type2'] != 'wallet' ) {
                        $r_name = '('.$userData[0]['account_type2'].') '.$r_name;
                    }
                    // 지갑으로 전송하는 경우가 아닌 거래소로 전송하는 경우, 21.04.14
                    if ( $userData[0]['account_type2'] == $con_exchange_type_value ) {
                        if ( new_receive_getname_check($_SESSION['user_id'], $userData[0]['id']) == false ) {
                            $r_name = 'coinibt_false';
                        }
                    }
                }
                else {
                    $db = getDbInstance();
                    //휴면 회원 쪽 조회 START
                    $db->where ("virtual_wallet_address", $waddr);
                    $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
                    $storeData = $db->get('admin_accounts A',null,$column);
                    if ($db->count < 1) {
                        $db->where ("virtual_wallet_address", $waddr);
                        $storeData = $db->get('admin_accounts');
                    }
                    //휴면 회원 쪽 조회 END

                    if ($db->count >= 1) {
                        $log->info('get_name2 Virtual 계좌조회',['target_id'=>$storeData[0]['id'],'action'=>'S']);
                        $r_name = get_user_real_name($storeData[0]['auth_name'], $storeData[0]['name'], $storeData[0]['lname']);
                        $virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
                        $r_name = $r_name.$virtual_account_tx1;
                    }
                }
           // }
		    //else{
        /*
                $r_name = '';
                $waddr = $filterData['waddr'];
                $db = getDbInstance();
                $db->where ("wallet_address", $waddr);
                $userData = $db->get('admin_accounts');
                if ($db->count >= 1) {
                    $r_name = get_user_real_name($userData[0]['auth_name'], $userData[0]['name'], $userData[0]['lname']);
                    if ( !empty($userData[0]['account_type2']) && $userData[0]['account_type2'] != 'wallet' ) {
                        $r_name = '('.$userData[0]['account_type2'].') '.$r_name;
                    }
                    // 지갑으로 전송하는 경우가 아닌 거래소로 전송하는 경우, 21.04.14
                    if ( $userData[0]['account_type2'] == $con_exchange_type_value ) {
                        if ( new_receive_getname_check($_SESSION['user_id'], $userData[0]['id']) == false ) {
                            $r_name = 'coinibt_false';
                        }
                    }
                } else {
                    $db = getDbInstance();
                    $db->where ("virtual_wallet_address", $waddr);
                    $storeData = $db->get('admin_accounts');
                    if ($db->count >= 1) {
                        $r_name = get_user_real_name($storeData[0]['auth_name'], $storeData[0]['name'], $storeData[0]['lname']);
                        $virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
                        $r_name = $r_name.$virtual_account_tx1;
                    }
                }
        */
           // }
			$store_name = '';
			$db = getDbInstance();
			$db->where('wallet_address', $waddr);
			$row_kiosk = $db->getOne('kiosk_config');
			if ( !empty($row_kiosk) ) {
				$store_name = 'kiosk';
			}

			echo json_encode(array('result'=>$r_name, 'store_name'=>$store_name));
			break;
		
	/*
		// send_etoken
		case 'get_name2_test':
			$r_name = '';
			$waddr = $filterData['waddr'];
			$db = getDbInstance();
			$db->where ("wallet_address", $waddr);
			$userData = $db->get('admin_accounts');
			if ($db->count >= 1) {
				$r_name = get_user_real_name($userData[0]['auth_name'], $userData[0]['name'], $userData[0]['lname']);
				if ( !empty($userData[0]['account_type2']) && $userData[0]['account_type2'] != 'wallet' ) {
					$r_name = '('.$userData[0]['account_type2'].') '.$r_name;
				}
				// 지갑으로 전송하는 경우가 아닌 거래소로 전송하는 경우, 21.04.14
				if ( $userData[0]['account_type2'] == $con_exchange_type_value ) {
					if ( new_receive_getname_check($_SESSION['user_id'], $userData[0]['id']) == false ) {
						$r_name = 'coinibt_false';
					}
				}
			} else {
				$db = getDbInstance();
				$db->where ("virtual_wallet_address", $waddr);
				$storeData = $db->get('admin_accounts');
				if ($db->count >= 1) {
					$r_name = get_user_real_name($storeData[0]['auth_name'], $storeData[0]['name'], $storeData[0]['lname']);
					$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
					$r_name = $r_name.$virtual_account_tx1;
				}
			}

			$store_name = '';
			$db = getDbInstance();
			$db->where('wallet_address', $waddr);
			$row_kiosk = $db->getOne('kiosk_config');
			if ( !empty($row_kiosk) ) {
				$store_name = 'kiosk';
			}

			echo json_encode(array('result'=>$r_name, 'store_name'=>$store_name));
			break;
		*/

		// token
		case 'get_token_history':
			$useragent=$_SERVER['HTTP_USER_AGENT'];
			$mobile=0;
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			{
				$mobile=1;
			}

			$walletAddress = $filterData['waddr'];
			$walletAddress_old = $filterData['waddr2']; // wallet_address_change
			$tokenName = $filterData['token'];

			$curl = curl_init();
			$setContractAddr = $contractAddressArr[$tokenName]['contractAddress'];
			$decimalDivide = $contractAddressArr[$tokenName]['decimal'];
			if($tokenName!='eth') {
				$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$walletAddress."&page=1&offset=10000&sort=desc&apikey=".$ethApiKey;
				$ethUrl2 = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$walletAddress_old."&page=1&offset=10000&sort=desc&apikey=".$ethApiKey;
			}
			else {
				$ethUrl = "http://api.etherscan.io/api?module=account&action=txlist&address=".$walletAddress."&sort=desc&apikey=".$ethApiKey;
				$ethUrl2 = "http://api.etherscan.io/api?module=account&action=txlist&address=".$walletAddress_old."&sort=desc&apikey=".$ethApiKey;
			}
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $ethUrl,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 3000,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"postman-token: 89d13eeb-278c-730c-b720-b521c178b500"
			  ),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			$getResultDecode = json_decode($response,true);
			$getRecords = $getResultDecode['result']; 

			$getRecords2 = array();
			if ( !empty($walletAddress_old) ) {
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $ethUrl2,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 3000,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"postman-token: 89d13eeb-278c-730c-b720-b521c178b500"
				  ),
				));
				
				$response2 = curl_exec($curl);
				$err2 = curl_error($curl);
				curl_close($curl);
				$getResultDecode2 = json_decode($response2,true);
				$getRecords2 = $getResultDecode2['result']; 
			}

			//$old_wallet_master = array('0xcea66e2f92e8511765bc1e2a247c352a7c84e895', '0x5aBa21b0B7f00Cc2223D4636b703b9D5708f861e', '0xB3f53ba0Fb6FC59a46299B4A8Bafe6d7a12B85e6', '0x1da4a1759ed3e2d59d4ae4303eaf5d408fbb24c6', '0x233a562005ff31c1999253ff28048f4bb01d1887');
			?>
				
			<div class="history">
				<div class="subject">
					<span>HISTORY</span>
					<span><?php echo !empty($langArr['send_sms_message5'])  ? $langArr['send_sms_message5'] : 'It takes up to 24 hours to complete the transaction.'; ?></span>
				</div>
				<?php


				if(!empty($getRecords)) {
					$getTime = '';
					foreach($getRecords as $getRecordSingle) {
						if($getRecordSingle['value'] <= 0 ){ continue; }
						//$txId = $getRecordSingle['hash'];
						$getDate = date("Y-m-d H:i:s",$getRecordSingle['timeStamp']);
						$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,4);

						$send_msg = !empty($langArr['token_history_text1']) ? $langArr['token_history_text1'] : 'Send';
						$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
						$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
						$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
						$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';

						$type = ($getRecordSingle['from']==$walletAddress) ? $send_msg.$complete_msg : $receive_msg.$complete_msg;
						$sign = ($getRecordSingle['from']==$walletAddress) ? "-" : "+";
						$name_text = ($getRecordSingle['from']==$walletAddress) ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다.


						if ($sign == '+') { // 받은거라면
							$txId = $getRecordSingle['from']; // 보낸사람 표시
						} else {
							$txId = $getRecordSingle['to'];
						}
						
						// 이름 표시, Show names (2020.05.12, YMJ)
						$name = '';
						$db = getDbInstance();
						$db->orwhere ("wallet_address", $txId);
						$db->orwhere ("wallet_address_change", $txId);
						$rowm = $db->get('admin_accounts');
						if ($db->count >= 1) {
                            $log->info('get_token_history REAL 계좌 조회',['target_id'=>$rowm[0]['id'],'action'=>'S']);
                            $name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
						} else {
							$db = getDbInstance();
							$db->where ("virtual_wallet_address", $txId);
							$rowm2 = $db->get('admin_accounts');
							if ($db->count >= 1) {
                                $log->info('get_token_history2 Virtual 계좌 조회',['target_id'=>$rowm[0]['id'],'action'=>'S']);
								$name = get_user_real_name($rowm2[0]['auth_name'], $rowm2[0]['name'], $rowm2[0]['lname']);
								if ( !empty($rowm2[0]['virtual_wallet_address']) && $rowm2[0]['virtual_wallet_address'] == $txId ) {
									$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
									$name = $name.$virtual_account_tx1;
								}
							}
						}

						$textLength = strlen($txId);
						$maxChars = 14;
						$txIdresult = substr_replace($txId, '...', $maxChars/2, $textLength-$maxChars);
						$txId = ($mobile==1) ? $txIdresult : $txId;

						
						$name = $name != '' ? $name : $txId; // 이름이 없을 경우 지갑주소 표시
						?>

						 <ul class="contents">
							<li>
								<span class="icon">
									<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
									<img class="big" src="images/icons/history_circles.png" alt="circle" />
								</span>
								<span class="amount"><?php echo $sign.$amount." ".strtoupper($tokenName); ?></span>
								<span class="type"><?php echo $type; ?></span>

								<p class="date"><?php echo $getDate; ?></p>
								<p class="name"><?php echo $name_text.' | '.$name; ?></p>
								<!--<p class="address"><?php echo $txId; ?></p>-->
							</li>
						</ul>
						<?php
					} // foreach
				}


				if(!empty($getRecords2)) {
					$getTime = '';
					foreach($getRecords2 as $getRecordSingle) {
						if($getRecordSingle['value'] <= 0 ){ continue; }
						//$txId = $getRecordSingle['hash'];
						$getDate = date("Y-m-d H:i:s",$getRecordSingle['timeStamp']);
						$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,4);

						$send_msg = !empty($langArr['token_history_text1']) ? $langArr['token_history_text1'] : 'Send';
						$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
						$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
						$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
						$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';

						$type = ($getRecordSingle['from']==$walletAddress_old) ? $send_msg.$complete_msg : $receive_msg.$complete_msg;
						$sign = ($getRecordSingle['from']==$walletAddress_old) ? "-" : "+";
						$name_text = ($getRecordSingle['from']==$walletAddress_old) ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다.


						if ($sign == '+') { // 받은거라면
							$txId = $getRecordSingle['from']; // 보낸사람 표시
						} else {
							$txId = $getRecordSingle['to'];
						}
						
						// 이름 표시, Show names (2020.05.12, YMJ)
						$name = '';
						$db = getDbInstance();
						$db->orwhere ("wallet_address", $txId);
						$db->orwhere ("wallet_address_change", $txId);
						$rowm = $db->get('admin_accounts');
						if ($db->count >= 1) {
							$name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
						} else {
							$db = getDbInstance();
							$db->where ("virtual_wallet_address", $txId);
							$rowm2 = $db->get('admin_accounts');
							if ($db->count >= 1) {
								$name = get_user_real_name($rowm2[0]['auth_name'], $rowm2[0]['name'], $rowm2[0]['lname']);
								if ( !empty($rowm2[0]['virtual_wallet_address']) && $rowm2[0]['virtual_wallet_address'] == $txId ) {
									$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
									$name = $name.$virtual_account_tx1;
								}
							}
						}

						$textLength = strlen($txId);
						$maxChars = 14;
						$txIdresult = substr_replace($txId, '...', $maxChars/2, $textLength-$maxChars);
						$txId = ($mobile==1) ? $txIdresult : $txId;
						
						$name = $name != '' ? $name : $txId; // 이름이 없을 경우 지갑주소 표시
						?>

						 <ul class="contents">
							<li>
								<span class="icon">
									<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
									<img class="big" src="images/icons/history_circles.png" alt="circle" />
								</span>
								<span class="amount"><?php echo $sign.$amount." ".strtoupper($tokenName); ?></span>
								<span class="type"><?php echo $type; ?></span>

								<p class="date"><?php echo $getDate; ?></p>
								<p class="name"><?php echo $name_text.' | '.$name; ?></p>
								<!--<p class="address"><?php echo $txId; ?></p>-->
							</li>
						</ul>
						<?php
					} // foreach
				} // getRecords2



				?>
			</div>
			<?php




			break;

		
		// set_transferpw_frm
		case 'get_transfer_pw':

			$result = '';
	
			if ( empty($filterData['mid']) || empty($filterData['pas1']) ) {
				$result = 'none';
			} else {
				$db = getDbInstance();
				$db->where ("id", $filterData['mid']);
				$userData = $db->getOne('admin_accounts');

				if ( !empty($userData['id']) ) {
                    $log->info('get_transfer_pw 조회',['target_id'=>$userData['id'],'action'=>'S']);
					if( !empty($userData['transfer_passwd']) ) {
						if ( password_verify($filterData['pas1'], $userData['transfer_passwd'])) { // 입력문자열, 해쉬
							$result = 'success';
						} else {
							$result = 'fail';
						}
					} else { // 비밀번호 셋팅이 필요함
						$result = 'set';
					}
				}
				else { // 사용자정보 없음. 잘못된 접근입니다..
					$result = 'none';
				}
			}
			
			//echo json_encode($result);
			echo json_encode(array('result'=>$result));

			break;

		// set_transferpw_frm_send
		case 'get_transfer_pw2':
			$result = '';
	
			if ( empty($filterData['mid']) || empty($filterData['pas1']) ) {
				$result = 'none';
			} else {
				$db = getDbInstance();
				$db->where ("id", $filterData['mid']);
				$userData = $db->getOne('admin_accounts');

                $log->info('get_transfer_pw2 조회',['target_id'=>$userData['id'],'action'=>'S']);

				$stf_count = !empty($userData['transfer_pw_count']) ? $userData['transfer_pw_count'] : '0';
				$stf_date = $userData['transfer_pw_date'];
				if ( !empty($stf_date) && $stf_date != date("Y-m-d") ) { // 날짜 다르면 초기화
					$stf_count = 0;
					$db = getDbInstance();
					$db->where ("id", $filterData['mid']);
					$updateArr = [] ;
					$updateArr['transfer_pw_count'] =  NULL;
					$updateArr['transfer_pw_date'] =  NULL;
					$last_id = $db->update('admin_accounts', $updateArr);
				}
				if ($stf_count >= $n_transfer_pw_count ) {
					// 횟수 초과시
					$result = 'over';
				}
				
				if ( $result == '' ) {
					if ( !empty($userData['id']) ) {
						if( !empty($userData['transfer_passwd']) ) {
							if ( password_verify($filterData['pas1'], $userData['transfer_passwd'])) { // 입력문자열, 해쉬
								$result = 'success';
							} else {
								$result = 'fail';

								// 실패시 횟수 변경
								$stf_count = $stf_count + 1;
								$db = getDbInstance();
								$db->where ("id", $filterData['mid']);
								$updateArr = [] ;
								$updateArr['transfer_pw_count'] =  $stf_count;
								$updateArr['transfer_pw_date'] =  date("Y-m-d");
								$last_id2 = $db->update('admin_accounts', $updateArr);

							}
						} else {
							$result = 'set';
						}
					} else {
						$result = 'none';
					}
				}
			}
			
			//echo json_encode($result);
			echo json_encode(array('result'=>$result, 'count'=>$stf_count));

			break;
		



		// token.php : More
		case 'get_token_history2':
			$useragent=$_SERVER['HTTP_USER_AGENT'];
			$mobile=0;
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			{
				$mobile=1;
			}

			$walletAddress = $filterData['waddr'];
			$walletAddress_old = $filterData['waddr2']; // wallet_address_change
			$tokenName = $filterData['token'];
			$page = !empty($filterData['page']) ? $filterData['page'] : 1;
			$offset = 10;

			$getRecords = array();
			$curl = curl_init();
			$setContractAddr = $contractAddressArr[$tokenName]['contractAddress'];
			$decimalDivide = $contractAddressArr[$tokenName]['decimal'];
			if($tokenName!='eth') {
				$ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$walletAddress."&page=".$page."&offset=".$offset."&sort=desc&apikey=".$ethApiKey;
			}
			else {
				$ethUrl = "http://api.etherscan.io/api?module=account&action=txlist&address=".$walletAddress."&page=".$page."&offset=".$offset."&sort=desc&apikey=".$ethApiKey;
			}
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $ethUrl,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 3000,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"postman-token: 89d13eeb-278c-730c-b720-b521c178b500"
			  ),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			$getResultDecode = json_decode($response,true);
			$getRecords = $getResultDecode['result']; 
			
			$url1_count = 0;
			if ( !empty($getRecords) ) {
				$url1_count = count($getRecords);
			}

			?>
			<div class="history">
				<?php if ( $page == 1 ) { ?>
					<div class="subject">
						<span>HISTORY</span>
						<span><?php echo !empty($langArr['send_sms_message5'])  ? $langArr['send_sms_message5'] : 'It takes up to 24 hours to complete the transaction.'; ?></span>
					</div>
				<?php
				}
				if(!empty($getRecords)) {
					$getTime = '';
					foreach($getRecords as $getRecordSingle) {
						//if($getRecordSingle['value'] <= 0 ){ continue; }
						//$txId = $getRecordSingle['hash'];
						$getDate = date("Y-m-d H:i:s",$getRecordSingle['timeStamp']);
						$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,8);
						$amount = rtrim($amount, 0);
						$amount = rtrim($amount, '.');

						$send_msg = !empty($langArr['token_history_text1']) ? $langArr['token_history_text1'] : 'Send';
						$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
						$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
						$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
						$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';

						$type = ($getRecordSingle['from']==$walletAddress) ? $send_msg.$complete_msg : $receive_msg.$complete_msg;
						$sign = ($getRecordSingle['from']==$walletAddress) ? "-" : "+";
						$name_text = ($getRecordSingle['from']==$walletAddress) ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다.


						if ($sign == '+') { // 받은거라면
							$txId = $getRecordSingle['from']; // 보낸사람 표시
						} else {
							$txId = $getRecordSingle['to'];
						}
						
						// 이름 표시, Show names (2020.05.12, YMJ)
						$name = '';
						$db = getDbInstance();
                        //2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
                        //휴면 회원 쪽 조회 START
                        //if($_SERVER['REMOTE_ADDR'] == '112.171.120.140'){
                            $db->where("A.wallet_address", $txId);
                            $db->orwhere("A.wallet_address_change", $txId);
                            $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
                            $rowm = $db->get('admin_accounts A',null,$column);
                            if(!$rowm){
                                $db->where("wallet_address", $txId);
                                $db->orwhere("wallet_address_change", $txId);
                                $rowm = $db->get('admin_accounts');
                            }
                        //휴면 회원 쪽 조회 END
                        //}
						//else{
//                            $db->orwhere ("wallet_address", $txId);
//                            $db->orwhere ("wallet_address_change", $txId);
//                            $rowm = $db->get('admin_accounts');
                        //}

						if ($db->count >= 1) {
                            $log->info('get_token_history2 REAL 계좌 조회',['target_id'=>$rowm[0]['id'],'action'=>'S']);
							$name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
							if ( !empty($rowm[0]['account_type2']) && $rowm[0]['account_type2'] != 'wallet' ) {
								$name = $name.' ('.$rowm[0]['account_type2'].')';
							}
						}
						else {
							$db = getDbInstance();
							$db->where ("virtual_wallet_address", $txId);
							$rowm2 = $db->get('admin_accounts');
							if ($db->count >= 1) {
                                $log->info('get_token_history2 Virtual 계좌 조회',['target_id'=>$rowm2[0]['id'],'action'=>'S']);
								$name = get_user_real_name($rowm2[0]['auth_name'], $rowm2[0]['name'], $rowm2[0]['lname']);
								if ( !empty($rowm2[0]['virtual_wallet_address']) ) { //  && $rowm2[0]['virtual_wallet_address'] == $txId
									$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
									$name = $name.$virtual_account_tx1;
								}
							}
						}
						
						if ( empty($name) && ($txId == strtolower($contractAddress) || $txId == strtolower($tokenPayContractAddress) || $txId == strtolower($marketCoinContractAddress) || $txId == strtolower($koreanWonContractAddress) || $txId == strtolower($usdtContractAddress) || $txId == strtolower('address') ) ) {
							$name = 'Smart Contract';
						} else if ( empty($name) && ($txId == '0x5aba21b0b7f00cc2223d4636b703b9d5708f861e' || $txId == '0xb3f53ba0fb6fc59a46299b4a8bafe6d7a12b85e6' || $txId == '0xcea66e2f92e8511765bc1e2a247c352a7c84e895') ) {
							$name = 'ServerCyberTronChain';
						}
						$name = $name != '' ? $name : $txId; // 이름이 없을 경우 지갑주소 표시

						$textLength = strlen($txId);
						$maxChars = 14;
						$txIdresult = substr_replace($txId, '...', $maxChars/2, $textLength-$maxChars);
						$txId = ($mobile==1) ? $txIdresult : $txId;
						
						?>

						 <ul class="contents">
							<li>
								<span class="icon">
									<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
									<img class="big" src="images/icons/history_circles.png" alt="circle" />
								</span>
								<span class="amount"><?php echo $sign.$amount." ".strtoupper($tokenName); ?></span>
								<span class="type"><?php echo $type; ?></span>

								<p class="date"><?php echo $getDate; ?></p>
								<p class="name"><?php echo $name_text.' | '.$name; ?></p>
								<!--<p class="address"><?php echo $txId; ?></p>-->
							</li>
						</ul>
						<?php
					} // foreach
				}
				?>

				<div class="token_history_page">
					<?php

					$exploerLink = ($tokenName=="ctc7" || $tokenName=="ctctm") ? "https://bscscan.com" : "https://etherscan.io";
					if ( $tokenName!='eth' && !empty($setContractAddr) ) {
						$ethscan_url = $exploerLink.'/token/'.$setContractAddr.'?a='.$walletAddress;
					} else {
						$ethscan_url = $exploerLink.'/address/'.$walletAddress;
					}
					if ( $page == 5 || $url1_count < $offset ) { ?>
						<a href="<?php echo $ethscan_url; ?>" title="etherscan"><?php echo !empty($langArr['more_on_etherscan']) ? $langArr['more_on_etherscan'] : "More On Explorer"; ?></a><?php
						if ( !empty($walletAddress_old) ) {
							if ( $tokenName!='eth' && !empty($setContractAddr) ) {
								$ethscan_url2 = $exploerLink.'/token/'.$setContractAddr.'?a='.$walletAddress_old;
							} else {
								$ethscan_url2 = $exploerLink.'/address/'.$walletAddress_old;
							}
							?><a href="<?php echo $ethscan_url2; ?>" title="etherscan" class="btn2"><?php echo !empty($langArr['view_on_etherscan_old_data']) ? $langArr['view_on_etherscan_old_data'] : "View previous transactions on Explorer"; ?></a><?php
						}
					} else { ?>
						<a href="javascript:;" onclick="get_token_history('<?php echo $page + 1; ?>', 'add');"><?php echo !empty($langArr['token_history_more']) ? $langArr['token_history_more'] : 'More'; ?></a>
					<?php } ?>
				</div>
				
			</div>
			<?php
			break;
		

		// etoken.php : More
		// 20.10.26 사용중지 -> get_etoken_history2로 대체
		case 'get_etoken_history':

			$page = !empty($filterData['page']) ? $filterData['page'] : 1;
			$token = $filterData['token'];
			$pagelimit = 10;
			
			$db = getDbInstance();
			$db->where("user_id", $_SESSION['user_id']);
			$db->where("coin_type", $token);
			 $db->orderBy('id', 'desc');
						
			$db->pageLimit = $pagelimit;
			$resultData = $db->arraybuilder()->paginate("etoken_logs", $page);
			$total_pages = $db->totalPages;
			
			?>
			<div class="history">
				<?php if ( $page == 1 ) { ?>
					<div class="subject">
						<span>HISTORY</span>
					</div>
				<?php
				}
				if(!empty($resultData)) {
					$getTime = '';
					foreach($resultData as $row) {
						$sign = $row['in_out'] == 'in' ? '+' : '-';
						$amount = $row['points'];
						$getDate = $row['created_at'];
						$tokenName = $row['coin_type'];
						
						$send_msg = !empty($langArr['token_history_text7']) ? $langArr['token_history_text7'] : 'Use';
						$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
						$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
						$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
						$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';

						$type = ($row['in_out']=='out') ? $send_msg.$complete_msg : $receive_msg.$complete_msg;
						
						$name = '';
						//if ( $row['in_out'] == 'in' ) {
							$name = 'ServerCyberTronChain';
						//} else {
							
							$db = getDbInstance();
							if ( !empty($row['send_user_id']) ) {
								$db->where("id", $row['send_user_id']);
								$rowm = $db->get('admin_accounts');
							} else if ( !empty($row['send_wallet_address']) ) {
								$db->orwhere ("wallet_address", $row['send_wallet_address']);
								$db->orwhere ("wallet_address_change", $row['send_wallet_address']);
								$db->orwhere ("virtual_wallet_address", $row['send_wallet_address']);
								$rowm = $db->get('admin_accounts');
							}
							if ($db->count >= 1) {
								$name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
								if ( !empty($rowm[0]['virtual_wallet_address']) && $rowm[0]['virtual_wallet_address'] == $row['send_wallet_address'] ) {
									$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
									$name = $name.$virtual_account_tx1;
								}
							}
						//}
						$name_text = ($row['in_out'] == 'out') ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다.
						
						?>

						 <ul class="contents">
							<li>
								<span class="icon">
									<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
									<img class="big" src="images/icons/history_circles.png" alt="circle" />
								</span>
								<span class="amount"><?php echo new_number_format($amount, $n_decimal_point_array2[$tokenName])." ".lcfirst(strtoupper($tokenName)); ?></span>
								<span class="type"><?php echo $type; ?></span>

								<p class="date"><?php echo $getDate; ?></p>
								<p class="name"><?php echo $name_text.' | '.$name; ?></p>
							</li>
						</ul>
						<?php
					} // foreach
				}
				?>

				<div class="token_history_page">
					<?php
					$lastPage = $total_pages;
					$nextPage = $page + 1;
					if ( $lastPage >= $nextPage ) {
						?><a href="javascript:;" onclick="get_token_history('<?php echo $nextPage; ?>', 'add');"><?php echo !empty($langArr['token_history_more']) ? $langArr['token_history_more'] : 'More'; ?></a><?php
					}
					?>
				</div>
				
			</div>
			<?php
			break;


		// etoken.php : More
		case 'get_etoken_history2':
			$page = !empty($filterData['page']) ? $filterData['page'] : 1;
			$token = $filterData['token'];
			$pagelimit = 10;
			
			$wallet_address = '';
			$db = getDbInstance();

            $db->where("id", $_SESSION['user_id']);
            $row_member = $db->getOne('admin_accounts');

			if ( !empty($row_member['wallet_address']) ) {
				$wallet_address = $row_member['wallet_address'];
			}
						

			$db = getDbInstance();
			$db->where("send_wallet_address", $wallet_address);
			$db->where("coin_type", $token);
			 $db->orderBy('id', 'desc');
						
			$db->pageLimit = $pagelimit;
			$resultData = $db->arraybuilder()->paginate("etoken_logs", $page);
			$total_pages = $db->totalPages;
			
			?>
			<div class="history">
				<?php if ( $page == 1 ) { ?>
					<div class="subject">
						<span>HISTORY</span>
					</div>
				<?php
				}
				if(!empty($resultData)) {
					$getTime = '';
					foreach($resultData as $row) {
						$sign = $row['in_out'] == 'out' ? '+' : '-'; // in, out 반대로
						$amount = $row['points'] != 0 ? ($row['points'] * -1) : $row['points'];
						$getDate = $row['created_at'];
						$tokenName = $row['coin_type'];
						
						$send_msg = !empty($langArr['token_history_text7']) ? $langArr['token_history_text7'] : 'Use';
						$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
						$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
						$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
						$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';

						$type = ($row['in_out']=='in') ? $send_msg.$complete_msg : $receive_msg.$complete_msg; // in, out 반대로
						
						$name = '';
						//if ( $row['in_out'] == 'in' ) {
							$name = 'ServerCyberTronChain';
						//} else {
							$db = getDbInstance();
							if ( !empty($row['user_id']) ) {

                                //2021.06.16 by.OJT 휴면 회원은 조회 되어야 함.
                                //휴면 회원 쪽 조회 START
                                $db->where("A.id", $row['user_id']);
                                $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");

                                $rowm = $db->get('admin_accounts A',null,$column);
                                if ($db->count < 1) {
                                    $db->where("id", $row['user_id']);
                                    $rowm = $db->get('admin_accounts');
                                }
                                //휴면 회원 쪽 조회 END
//                                    $db->where("id", $row['user_id']);
//                                    $rowm = $db->get('admin_accounts');
							}
							if ($db->count >= 1) {
                                $log->info('get_etoken_history2 조회',['target_id'=>$rowm[0]['id'],'action'=>'S']);
                                $name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
								if ( !empty($rowm[0]['account_type2']) && $rowm[0]['account_type2'] != 'wallet' ) {
									$name = $name.' ('.$rowm[0]['account_type2'].')';
								}

								if ( !empty($row['wallet_address']) && $rowm[0]['virtual_wallet_address'] == $row['wallet_address'] ) {
									$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
									$name = $name.$virtual_account_tx1;
								}
								
							}
						//}
						$name_text = ($row['in_out'] == 'in') ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다. // in, out 반대로
						
						?>

						 <ul class="contents">
							<li>
								<span class="icon">
									<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
									<img class="big" src="images/icons/history_circles.png" alt="circle" />
								</span>
								<span class="amount"><?php echo new_number_format($amount, $n_decimal_point_array2[$tokenName])." ".$n_epay_name_array[$tokenName]; ?></span>
								<span class="type"><?php echo $type; ?></span>

								<p class="date"><?php echo $getDate; ?></p>
								<p class="name"><?php echo $name_text.' | '.$name; ?></p>
							</li>
						</ul>
						<?php
					} // foreach
				}
				?>

				<div class="token_history_page">
					<?php
					$lastPage = $total_pages;
					$nextPage = $page + 1;
					if ( $lastPage >= $nextPage ) {
						?><a href="javascript:;" onclick="get_token_history('<?php echo $nextPage; ?>', 'add');"><?php echo !empty($langArr['token_history_more']) ? $langArr['token_history_more'] : 'More'; ?></a><?php
					}
					?>
				</div>
				
			</div>
			<?php
			break;








		// etoken_adm.php : More
		case 'get_etoken_history3':
			$page = !empty($filterData['page']) ? $filterData['page'] : 1;
			$token = $filterData['token'];
			$pagelimit = 10;
			$userId = $filterData['user_id'];
			$user_type = $filterData['user_type'];
			
			$wallet_address = '';
			$db = getDbInstance();
			$db->where("id", $userId);
			$row_member = $db->getOne('admin_accounts');

			if ( $user_type == 'virtual' ) {
				if ( !empty($row_member['virtual_wallet_address']) ) {
					$wallet_address = $row_member['virtual_wallet_address'];
				}
			} else {
				if ( !empty($row_member['wallet_address']) ) {
					$wallet_address = $row_member['wallet_address'];
				}
			}
						

			$db = getDbInstance();
			$db->where("send_wallet_address", $wallet_address);
			$db->where("coin_type", $token);
			 $db->orderBy('id', 'desc');
						
			$db->pageLimit = $pagelimit;
			$resultData = $db->arraybuilder()->paginate("etoken_logs", $page);
			$total_pages = $db->totalPages;
			
			?>
			<div class="history">
				<?php if ( $page == 1 ) { ?>
					<div class="subject">
						<span>HISTORY</span>
					</div>
				<?php
				}
				if(!empty($resultData)) {
					$getTime = '';
					foreach($resultData as $row) {
						$sign = $row['in_out'] == 'out' ? '+' : '-'; // in, out 반대로
						$amount = $row['points'] != 0 ? ($row['points'] * -1) : $row['points'];
						$getDate = $row['created_at'];
						$tokenName = $row['coin_type'];
						
						$send_msg = !empty($langArr['token_history_text7']) ? $langArr['token_history_text7'] : 'Use';
						$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
						$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
						$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
						$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';

						$type = ($row['in_out']=='in') ? $send_msg.$complete_msg : $receive_msg.$complete_msg; // in, out 반대로
						
						$name = '';
						//if ( $row['in_out'] == 'in' ) {
							$name = 'ServerCyberTronChain';
						//} else {
							$db = getDbInstance();
							if ( !empty($row['user_id']) ) {
                                //2021.06.16 by.OJT 휴면 회원은 조회 되어야 함.
                                //휴면 회원 쪽 조회 START
                                $db->where("A.id", $row['user_id']);
                                $db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");

                                $rowm = $db->get('admin_accounts A',null,$column);
                                if ($db->count < 1) {
                                    $db->where("id", $row['user_id']);
                                    $rowm = $db->get('admin_accounts');
                                }
                                //휴면 회원 쪽 조회 END
//								$db->where("id", $row['user_id']);
//								$rowm = $db->get('admin_accounts');
							}
							if ($db->count >= 1) {
                                $log->info('get_etoken_history3 조회',['target_id'=>$rowm[0]['id'],'action'=>'S']);
								$name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
								if ( !empty($rowm[0]['account_type2']) && $rowm[0]['account_type2'] != 'wallet' ) {
									$name = $name.' ('.$rowm[0]['account_type2'].')';
								}
								if ( !empty($row['wallet_address']) && $rowm[0]['virtual_wallet_address'] == $row['wallet_address'] ) {
									$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
									$name = $name.$virtual_account_tx1;
								}
								
							}
						//}
						$name_text = ($row['in_out'] == 'in') ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다. // in, out 반대로
						
						?>

						 <ul class="contents">
							<li>
								<span class="icon">
									<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
									<img class="big" src="images/icons/history_circles.png" alt="circle" />
								</span>
								<span class="amount"><?php echo new_number_format($amount, $n_decimal_point_array2[$tokenName])." ".$n_epay_name_array[$tokenName]; ?></span>
								<span class="type"><?php echo $type; ?></span>

								<p class="date"><?php echo $getDate; ?></p>
								<p class="name"><?php echo $name_text.' | '.$name; ?></p>
							</li>
						</ul>
						<?php
					} // foreach
				}
				?>

				<div class="token_history_page">
					<?php
					$lastPage = $total_pages;
					$nextPage = $page + 1;
					if ( $lastPage >= $nextPage ) {
						?><a href="javascript:;" onclick="get_token_history('<?php echo $nextPage; ?>', 'add');"><?php echo !empty($langArr['token_history_more']) ? $langArr['token_history_more'] : 'More'; ?></a><?php
					}
					?>
				</div>
				
			</div>
			<?php
			break;


			case 'get_bsc_history':
				$useragent=$_SERVER['HTTP_USER_AGENT'];
				$mobile=0;
				if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
				{
					$mobile=1;
				}
	
				$walletAddress = $filterData['waddr'];
				$walletAddress_old = $filterData['waddr2']; // wallet_address_change
				$tokenName = $filterData['token'];
				$page = !empty($filterData['page']) ? $filterData['page'] : 1;
				$offset = 10;
	
				$getRecords = array();
				$curl = curl_init();
				$setContractAddr = $contractAddressArr[$tokenName]['contractAddress'];
				$decimalDivide = $contractAddressArr[$tokenName]['decimal'];
				if($tokenName!='eth') {
					$ethUrl = "http://api.bscscan.com/api?module=account&action=tokentx&contractaddress=".$setContractAddr."&address=".$walletAddress."&page=".$page."&offset=".$offset."&sort=desc&apikey=".$bscApiKey;
				}
				else {
					$ethUrl = "http://api.bscscan.com/api?module=account&action=txlist&address=".$walletAddress."&page=".$page."&offset=".$offset."&sort=desc&apikey=".$bscApiKey;
				}
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $ethUrl,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 3000,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"postman-token: 89d13eeb-278c-730c-b720-b521c178b500"
				  ),
				));
				
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				$getResultDecode = json_decode($response,true);
				$getRecords = $getResultDecode['result']; 
				
				$url1_count = 0;
				if ( !empty($getRecords) ) {
					$url1_count = count($getRecords);
				}
	
				?>
				<div class="history">
					<?php if ( $page == 1 ) { ?>
						<div class="subject">
							<span>HISTORY</span>
							<span><?php echo !empty($langArr['send_sms_message5'])  ? $langArr['send_sms_message5'] : 'It takes up to 24 hours to complete the transaction.'; ?></span>
						</div>
					<?php
					}
					if(!empty($getRecords)) {
						$getTime = '';
						foreach($getRecords as $getRecordSingle) {
							//if($getRecordSingle['value'] <= 0 ){ continue; }
							//$txId = $getRecordSingle['hash'];
							$getDate = date("Y-m-d H:i:s",$getRecordSingle['timeStamp']);
							$amount = number_format((float)$getRecordSingle['value']/$decimalDivide,8);
							$amount = rtrim($amount, 0);
							$amount = rtrim($amount, '.');
	
							$send_msg = !empty($langArr['token_history_text1']) ? $langArr['token_history_text1'] : 'Send';
							$receive_msg = !empty($langArr['token_history_text2']) ? $langArr['token_history_text2'] : 'Receive';
							$complete_msg  = !empty($langArr['token_history_text3']) ? $langArr['token_history_text3'] : 'complete';
							$sender = !empty($langArr['token_history_text4']) ? $langArr['token_history_text4'] : 'Sender';
							$recipient = !empty($langArr['token_history_text5']) ? $langArr['token_history_text5'] : 'Recipient';
	
							$type = ($getRecordSingle['from']==$walletAddress) ? $send_msg.$complete_msg : $receive_msg.$complete_msg;
							$sign = ($getRecordSingle['from']==$walletAddress) ? "-" : "+";
							$name_text = ($getRecordSingle['from']==$walletAddress) ? $recipient : $sender; // 내가 보낸거라면 받는사람이 표시되어야 한다.
	
	
							if ($sign == '+') { // 받은거라면
								$txId = $getRecordSingle['from']; // 보낸사람 표시
							} else {
								$txId = $getRecordSingle['to'];
							}
							
							// 이름 표시, Show names (2020.05.12, YMJ)
							$name = '';
							$db = getDbInstance();
							//2021.06.17 by.OJT 휴면 회원은 조회 되어야 함.
							//휴면 회원 쪽 조회 START
							//if($_SERVER['REMOTE_ADDR'] == '112.171.120.140'){
								$db->where("A.wallet_address", $txId);
								$db->orwhere("A.wallet_address_change", $txId);
								$db->join("admin_accounts_sleep B", "A.id = B.id", "INNER");
								$rowm = $db->get('admin_accounts A',null,$column);
								if(!$rowm){
									$db->where("wallet_address", $txId);
									$db->orwhere("wallet_address_change", $txId);
									$rowm = $db->get('admin_accounts');
								}
							//휴면 회원 쪽 조회 END
							//}
							//else{
	//                            $db->orwhere ("wallet_address", $txId);
	//                            $db->orwhere ("wallet_address_change", $txId);
	//                            $rowm = $db->get('admin_accounts');
							//}
	
							if ($db->count >= 1) {
								$log->info('get_token_history2 REAL 계좌 조회',['target_id'=>$rowm[0]['id'],'action'=>'S']);
								$name = get_user_real_name($rowm[0]['auth_name'], $rowm[0]['name'], $rowm[0]['lname']);
								if ( !empty($rowm[0]['account_type2']) && $rowm[0]['account_type2'] != 'wallet' ) {
									$name = $name.' ('.$rowm[0]['account_type2'].')';
								}
							}
							else {
								$db = getDbInstance();
								$db->where ("virtual_wallet_address", $txId);
								$rowm2 = $db->get('admin_accounts');
								if ($db->count >= 1) {
									$log->info('get_token_history2 Virtual 계좌 조회',['target_id'=>$rowm2[0]['id'],'action'=>'S']);
									$name = get_user_real_name($rowm2[0]['auth_name'], $rowm2[0]['name'], $rowm2[0]['lname']);
									if ( !empty($rowm2[0]['virtual_wallet_address']) ) { //  && $rowm2[0]['virtual_wallet_address'] == $txId
										$virtual_account_tx1 = !empty($langArr['virtual_account_tx1']) ? $langArr['virtual_account_tx1'] : ' (Virtual Account)';
										$name = $name.$virtual_account_tx1;
									}
								}
							}
							
							if ( empty($name) && ($txId == strtolower($contractAddress) || $txId == strtolower($tokenPayContractAddress) || $txId == strtolower($marketCoinContractAddress) || $txId == strtolower($koreanWonContractAddress) || $txId == strtolower($usdtContractAddress) || $txId == strtolower('address') ) ) {
								$name = 'Smart Contract';
							} else if ( empty($name) && ($txId == '0x5aba21b0b7f00cc2223d4636b703b9d5708f861e' || $txId == '0xb3f53ba0fb6fc59a46299b4a8bafe6d7a12b85e6' || $txId == '0xcea66e2f92e8511765bc1e2a247c352a7c84e895') ) {
								$name = 'ServerCyberTronChain';
							}
							$name = $name != '' ? $name : $txId; // 이름이 없을 경우 지갑주소 표시
	
							$textLength = strlen($txId);
							$maxChars = 14;
							$txIdresult = substr_replace($txId, '...', $maxChars/2, $textLength-$maxChars);
							$txId = ($mobile==1) ? $txIdresult : $txId;
							
							?>
	
							 <ul class="contents">
								<li>
									<span class="icon">
										<img class="main" src="images/icons/history_circle<?php echo $sign; ?>.png" alt="circle" /><br />
										<img class="big" src="images/icons/history_circles.png" alt="circle" />
									</span>
									<span class="amount"><?php echo $sign.$amount." ".strtoupper($tokenName); ?></span>
									<span class="type"><?php echo $type; ?></span>
	
									<p class="date"><?php echo $getDate; ?></p>
									<p class="name"><?php echo $name_text.' | '.$name; ?></p>
									<!--<p class="address"><?php echo $txId; ?></p>-->
								</li>
							</ul>
							<?php
						} // foreach
					}
					?>
	
					<div class="token_history_page">
						<?php
	
						$exploerLink = ($tokenName=="ctc7" || $tokenName=="ctctm") ? "https://bscscan.com" : "https://etherscan.io";
						if ( $tokenName!='eth' && !empty($setContractAddr) ) {
							$ethscan_url = $exploerLink.'/token/'.$setContractAddr.'?a='.$walletAddress;
						} else {
							$ethscan_url = $exploerLink.'/address/'.$walletAddress;
						}
						if ( $page == 5 || $url1_count < $offset ) { ?>
							<a href="<?php echo $ethscan_url; ?>" title="etherscan"><?php echo !empty($langArr['more_on_etherscan']) ? $langArr['more_on_etherscan'] : "More On Explorer"; ?></a><?php
							if ( !empty($walletAddress_old) ) {
								if ( $tokenName!='eth' && !empty($setContractAddr) ) {
									$ethscan_url2 = $exploerLink.'/token/'.$setContractAddr.'?a='.$walletAddress_old;
								} else {
									$ethscan_url2 = $exploerLink.'/address/'.$walletAddress_old;
								}
								?><a href="<?php echo $ethscan_url2; ?>" title="etherscan" class="btn2"><?php echo !empty($langArr['view_on_etherscan_old_data']) ? $langArr['view_on_etherscan_old_data'] : "View previous transactions on Explorer"; ?></a><?php
							}
						} else { ?>
							<a href="javascript:;" onclick="get_token_history('<?php echo $page + 1; ?>', 'add');"><?php echo !empty($langArr['token_history_more']) ? $langArr['token_history_more'] : 'More'; ?></a>
						<?php } ?>
					</div>
					
				</div>
				<?php
				break;
				


	} // switch
}
?>