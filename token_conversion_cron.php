<?php
// Page in use
require_once './config/config.php';
require_once './config/new_config.php';
require('includes/web3/vendor/autoload.php');



use Web3\Web3;
use Web3\Contract;
$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
$eth = $web3->eth;

echo "Start : ".date('Y-m-d H:i:s')."\n";

$db = getDbInstance();
$db->where("from_token_tx_status", 'pending');

$getData = $db->get('token_conversion');

$send_type = 'exchange_eth_to_bsc';

if(!empty($getData)){ 
	foreach($getData as $getDataSingle){
        $cuData =  date('Y-m-d H:i:s');
        $updateConversionData = [];
		$conversionId = $getDataSingle['id'];
		$bscToken = $getDataSingle['to_token'];
		$bscAmount = $getDataSingle['to_token_amount'];
		$to_address = $getDataSingle['to_token_to_wallet_address'];
		$userId = $getDataSingle['user_id'];
        $from_token_tx_id = $getDataSingle['from_token_tx_id'];
		
        //0xa1ba02cec78612d6c539def03855e3fd200b4c59229d8e93107e6d7b0ed70213  - not exist
        //0x4982e710afb0dd15b3d472fd175a3cdf275092bd7507f4c91ffc1034a10a7c3d - failed
        // 0x41ccdd01c263bea7336dacde798b3641e535a32bb491a9810d745a846d2024bf - success
        // 0xce6132afb3062f86fedfe5b25d0437fe9fcdbf8e1e3b3af5f6a90e509d8e5230  - pending
        $verifyTxCurl = curl_init();

        curl_setopt_array($verifyTxCurl, array(
        CURLOPT_URL => '195.201.168.34:3000/tx_status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{"tx_id":"'.$from_token_tx_id.'"}',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $getResponse = curl_exec($verifyTxCurl);

        curl_close($verifyTxCurl);
        $decodeResp = json_decode($getResponse,true);
        // if(empty($decodeResp['status'])){
        //     echo "empty";
        // }
        // if($decodeResp['status']===true){
        //     echo 'success';
        // }
        // if($decodeResp['status']===false) {
        //     echo 'failed';
        // }
        if($decodeResp['status']===true){
		    // send bscToken to user

         
            $tokenArrBsc = $contractAddressArr[$bscToken];
            $tokenAbiBsc = $tokenArrBsc['abi'];
            $tokenContractAddressBsc = $tokenArrBsc['contractAddress'];
            $decimalDigitBsc = $tokenArrBsc['decimal'];
            $amountToSendBsc = bcmul($bscAmount,$decimalDigitBsc); // 201112
    
            $curl = curl_init();
    
            curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://127.0.0.1:5000/api/v1/transfer_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"sender_pvt_key":"'.$bsc_master_pvt_key.'","to_address":"'.$to_address.'","amount":'.$amountToSendBsc.',"token":"'.$bscToken.'"}',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
            ));
    
            $response = curl_exec($curl);
    
            curl_close($curl);
            
            $decodeResp = json_decode($response,true);
            if($decodeResp['success']==true){
                $bscTxId = $decodeResp['data'];
                // Add log records
                $data_to_send_logs = [];
                $data_to_send_logs['send_type'] = $send_type;
                $data_to_send_logs['coin_type'] = $bscToken;
                //$data_to_send_logs['from_id'] = $_SESSION['user_id'];
                $data_to_send_logs['to_id'] = $userId;
                $data_to_send_logs['from_address'] = $bsc_master_wallet;
                $data_to_send_logs['to_address'] = $to_address;
                $data_to_send_logs['amount'] = $bscAmount;
                $data_to_send_logs['fee'] = 0;
                if ( !empty($bscTxId) ) {
                    $data_to_send_logs['transactionId'] = $bscTxId;
                }
                $data_to_send_logs['status'] = !empty($bscTxId) ? 'send' : 'fail';
                $data_to_send_logs['etoken_send'] = 'P';
                $data_to_send_logs['chain_type'] = 'BSC';
                $data_to_send_logs['created_at'] = $cuData;

                //$db = getDbInstance();
                $last_id_slBsc = $db->insert('user_transactions_all', $data_to_send_logs);

                if(!empty($bscTxId)){

                    $updateConversionData["to_token_tx_id"] = $bscTxId;
                    $updateConversionData["to_token_tx_status"] = 'pending';
                    $updateConversionData["to_created_at"] = $cuData;
                    

                    $data_to_store = [];
                    $data_to_store['created_at'] = $cuData;

                    //$data_to_store['sender_id'] = $_SESSION['user_id'];
                    $data_to_store['reciver_address'] = $to_address;
                    $data_to_store['amount'] = $bscAmount;
                    $data_to_store['fee_in_eth'] = 0;
                    $data_to_store['status'] = 'completed';
                    $data_to_store['fee_in_gcg'] = 0;
                    $data_to_store['blockchain_type'] = 'BSC';
                    $data_to_store['transactionId'] = $bscTxId;
                    $data_to_store['tx_type'] = 'eth_to_bsc_conversion';
                    $data_to_store['coin_type'] = $bscToken;
                    
                    //$db = getDbInstance();
                    $last_id = $db->insert('user_transactions', $data_to_store);
                }
                
            }

            $updateConversionData["from_token_tx_status"] = "completed";
            $updateConversionData["from_completed_at"] = $cuData;
        }
        else if($decodeResp['status']===false){
            $updateConversionData["from_token_tx_status"] = "failed";
        }
        if(!empty($updateConversionData)){
            $db = getDbInstance();
            $db->where("id", $conversionId);
            $db->update('token_conversion', $updateConversionData);

        }

	}
}


echo "Finish : ".date('Y-m-d H:i:s')."\n\n";

function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}
?>
