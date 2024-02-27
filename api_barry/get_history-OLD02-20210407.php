<?php

exit();

//사용안함 2021-04-07 by ojt 
@require_once '../config/config.php';
@require_once '../config/new_config.php';

$returnArr = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  
    if (empty($_GET['token']) || empty($_GET['waddr'])) {
        $returnArr['success'] = false;
        $returnArr['message'] = "Not found Rapameters";
        $returnArr['data'] = [];
    } else {
        $name = "";
 
        // $mode = $_GET['mode'];
        $tokenName = $_GET['token'];
        $walletAddress = $_GET['waddr'];
		
		if(isset($_GET['page']) && $_GET['page'] > 0){
			$page = $_GET['page'];
		}
		else{
			$page = 1;
		}
			
        // aa
        switch ($tokenName) {
            case ('tp3'):
            case ('mc'):
                $curl = curl_init();
                $setContractAddr = $contractAddressArr[$tokenName]['contractAddress'];
                $decimalDivide = $contractAddressArr[$tokenName]['decimal'];

                $ethUrl = "http://api.etherscan.io/api?module=account&action=tokentx&contractaddress=" . $setContractAddr . "&address=" . $walletAddress . "&page=" . $page . "&offset=20&sort=desc&apikey=" . $ethApiKey;

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
                    )
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);

                $getResultDecode = json_decode($response, true);
                $getRecords = $getResultDecode['result'];

                $result = array();
                $i = 0;

                $decimalDivide = $contractAddressArr[$tokenName]['decimal'];

                $db = getDbInstance();

                foreach ($getRecords as $getRecordSingle) {
                    if ($getRecordSingle['value'] <= 0)
                        continue;
                    $getDate = date("Y-m-d H:i:s", $getRecordSingle['timeStamp']);
                    $amount = number_format((float) $getRecordSingle['value'] / $decimalDivide, 4);

                    $name = '';

                    if ($getRecordSingle['from'] == $walletAddress) {
                        $type = '보내기완료';
                        $sign = "-";
                        $name_text = '받는사람';
                    } else {
                        $type = '받기완료';
                        $sign = "+";
                        $name_text = '보내는사람';
                    }

                    $sign = ($getRecordSingle['from'] == $walletAddress) ? "-" : "+";

                    if ($sign == '+') { // 받은거라면
                        $txId = $getRecordSingle['from']; // 보낸사람 표시
                    } else {
                        $txId = $getRecordSingle['to'];
                    }

                    // $db->where("wallet_address", $txId);
                    $db->where("wallet_address='" . $txId . "' or wallet_address_change='" . $txId . "'");
                    $rowm = $db->get('admin_accounts');

                    if (! empty($rowm[0]['auth_name'])) { // 본인인증 완료한 경우 실명 표시
                        $name = $rowm[0]['auth_name'];
                    } else if (! empty($rowm[0]['name'])) { // 사용자 입력한 이름
                        $name = $rowm[0]['name'];
                        if (! empty($rowm[0]['lname'])) {
                            $name = $rowm[0]['lname'] . $name;
                        }
                    }
                    $textLength = strlen($txId);
                    $maxChars = 14;
                    $txIdresult = substr_replace($txId, '...', $maxChars / 2, $textLength - $maxChars);
                    $txId = $txIdresult;
                    $name = $name != '' ? $name : $txIdresult; // 이름이 없을 경우 지갑주소 표시

                    $result[$i] = array(

                        'datetime' => $getDate,
                        'amount' => $amount,
                        'name' => $name,
                        'type' => $type,
                        'sign' => $sign,
                        'name_text' => $name_text,
                        'page' => $page
                    );
                    $i ++;
                }
                break;

            case ('etp3'):
            case ('emc'):

                // $kind = $_GET['kind'];
                // $waddr = $_GET['waddr'];

                $result = array();
                $i = 0;

                // $walletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';

                if (! empty($walletAddress)) {

                    /*
                     * $db3 = getDbInstance();
                     *
                     * $db3->where("virtual_wallet_address='".$walletAddress."'");
                     * $user = $db3->get('admin_accounts');
                     *
                     * $owner_id = '';
                     *
                     * if ( !empty($user[0]['id']) ) {
                     * $owner_id = $user[0]['id'];
                     * }
                     *
                     *
                     * $db->where("user_id", $owner_id);
                     */

                    $db = getDbInstance();

                    $db->where("send_wallet_address", $walletAddress);
                    $db->where("coin_type", $tokenName);
                    
                    $db->orderBy('id', 'desc');

                    $resultData = $db->arraybuilder()->paginate("etoken_logs", $page);
                    $db2 = getDbInstance();

                    foreach ($resultData as $row) {

                        if ($row['in_out'] == 'out') {
                            $type = '받기완료';
                            $sign = "+";
                            $name_text = '보내는사람';
                            $amount = (int) ($row['points']) * - 1;
                        } else {
                            $type = '보내기완료';
                            $sign = "-";
                            $name_text = '받는사람';
                            $amount = (int) ($row['points']);
                        }

                        // $db2->where("wallet_address='".$row['send_wallet_address']."' or wallet_address_change='".$row['send_wallet_address']."'");
                        $db2->where("id='" . $row['user_id'] . "'");
                        $rowm = $db2->get('admin_accounts');

                        if (! empty($rowm[0]['auth_name'])) { // 본인인증 완료한 경우 실명 표시
                            $name = $rowm[0]['auth_name'];
                        } else if (! empty($rowm[0]['name'])) { // 사용자 입력한 이름
                            $name = $rowm[0]['name'];
                            if (! empty($rowm[0]['lname'])) {
                                $name = $rowm[0]['lname'] . $name;
                            }
                        }
                        $name = $name != '' ? $name : $row['user_id']; // 이름이 없을 경우 유저ID 표시

                        $result[$i] = array(
                            'datetime' => $row['created_at'],
                            'amount' => number_format($amount),
                            'name' => $name,
                            'type' => $type,
                            'sign' => $sign,
                            'name_text' => $name_text,
                            'page' => $page
                        );
                        $i++;
                    }
                }

                break; 
        }
        $returnArr['success'] = true;
        $returnArr['message'] = "get balance";
        // $returnArr['data'] = $getResultDecode;
        $returnArr['data'] = $result;
        $returnArr['page'] = $page;
    }
    
} else {

    $returnArr['success'] = false;
    $returnArr['message'] = "Invalid Request";
    $returnArr['data'] = [];
}

$jsonData = json_encode($returnArr);

echo $jsonData;

