<?php
//신규 API 테스트... 2021.08.18
//EthereumRPC RPC 버전.
namespace wallet\common;

use wallet\common\Filter as walletFilter;
use wallet\ctcDbDriver\Driver as walletDb;

use \EthereumRPC;
use \ERC20;
use \Web3;
require('includes/web3/vendor/autoload.php');

class InfoTest{

    protected $apiServerIp = 'https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e';
    protected $apiServerIpNotProtocol = 'mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e';
    protected $apiServerPort = 80;

    protected $new_server_ip = 'http://172.31.132.111';
    protected $new_server_ip_notProtocol = '172.31.132.111';
    protected $new_server_port = 8545;

    protected $ethApiKey = 'ehtkey';

    // Token balance check
    // $getbalance_read_type : 1 - EthereumRPC/ERC20, 2-Web3/Contract
    // $token : eth, ctc, tp3, usdt, krw, mc
    // $contractArr : config/config.php : $contractAddressArr
    public function wi_get_balance($getbalance_read_type, $token, $walletAddress, $contractArr) {
        if($walletAddress=="s" || empty($walletAddress) ){
            return 0;
        }
        if ( empty($getbalance_read_type) ) {
            $getbalance_read_type = '1';
        }

        if ( $token == 'all' ) {
            $get_balance = array();
            //require('/var/www/html/wallet2/vendor/autoload.php');

            //$geth = new EthereumRPC\EthereumRPC($this->apiServerIpNotProtocol);
            $geth = new EthereumRPC\EthereumRPC($this->new_server_ip_notProtocol,$this->new_server_port);
            $erc20 = new ERC20\ERC20($geth);
            // 테스트
//            var_dump($walletAddress);
//            var_dump($geth);
//           var_dump($erc20);
//           var_dump('aaa');
//           $testtesttes = $erc20->token($contractArr['tp3']['contractAddress']);
//            var_dump($contractArr['tp3']['contractAddress']);
//           var_dump($testtesttes->balanceOf('0xe13896f1fca58db37bde5ac5f39c0b602a99ab31',false));
//            var_dump($contractArr['ctc']['contractAddress']);
//            var_dump($contractArr);

            // 테스트 END
            try {
                $getVal = 0;
                $coinBalance = 0;
                $tokenPayBalance = 0;
                $usdtBalance = 0;
                $mcBalance = 0;
                $krwBalance = 0;

                $getVal = $geth->eth()->getBalance($walletAddress);
                //$getVal = ($getVal>0.0045 && $checkApproved=='N') ? $getVal-0.0045 :$getVal;
                $ethObj = $erc20->token($contractArr['ctc']['contractAddress']);
                $coinBalance = $ethObj->balanceOf($walletAddress,false);
                $scale = 18;
                $coinBalance = bcdiv($coinBalance, bcpow("10", strval($scale), 0), $scale);

                // tp3 balance
                $tokenPay = $erc20->token($contractArr['tp3']['contractAddress']);
                $tokenPayBalance = $tokenPay->balanceOf($walletAddress,false);
                $scale = 18;
                $tokenPayBalance = bcdiv($tokenPayBalance, bcpow("10", strval($scale), 0), $scale);

                // usdt balance
                $usdtObj = $erc20->token($contractArr['usdt']['contractAddress']);
                $usdtBalance = $usdtObj->balanceOf($walletAddress,false);
                $scale = 6;
                $usdtBalance = bcdiv($usdtBalance, bcpow("10", strval($scale), 0), $scale);

                // mc balance
                $mcObj = $erc20->token($contractArr['mc']['contractAddress']);
                $mcBalance = $mcObj->balanceOf($walletAddress,false);
                $scale = 6;
                $mcBalance = bcdiv($mcBalance, bcpow("10", strval($scale), 0), $scale);

                // krw balance
                $krwObj = $erc20->token($contractArr['krw']['contractAddress']);
                $krwBalance = $krwObj->balanceOf($walletAddress,false);
                $scale = 6;
                $krwBalance = bcdiv($krwBalance, bcpow("10", strval($scale), 0), $scale);

                $get_balance['eth'] = $getVal;
                $get_balance['ctc'] = $coinBalance;
                $get_balance['tp3'] = $tokenPayBalance;
                $get_balance['usdt'] = $usdtBalance;
                $get_balance['mc'] = $mcBalance;
                $get_balance['krw'] = $krwBalance;
            } catch(Exception $e) {
                $this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : 94, File : ' . $e->getFile() . ' on line ' . $e->getLine());
            }



        } else {
            $get_balance = 0;

            $contractAddress = $contractArr[$token]['contractAddress'];
            $decimal = $contractArr[$token]['decimal'];
            $abi = $contractArr[$token]['abi'];

            if ( $getbalance_read_type == '1') {
                if ( $token == 'ctc' || $token == 'tp3' ) {
                    $scale = 18;
                } else {
                    $scale = 6;
                }
                try {
                    $geth = new EthereumRPC\EthereumRPC($this->new_server_ip_notProtocol,$this->new_server_port);
                    switch($token) {
                        case 'eth':
                            $get_balance = $geth->eth()->getBalance($walletAddress);
                            break;
                        default:
                            $erc20 = new ERC20\ERC20($geth);
                            $ethObj = $erc20->token($contractAddress);
                            $get_balance = $ethObj->balanceOf($walletAddress,false);
                            $get_balance = bcdiv($get_balance, bcpow("10", strval($scale), 0), $scale);
                            break;
                    } // switch

                } catch(Exception $e) {
                    //$get_balance = -1;
                    $this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : 91, File : ' . $e->getFile() . ' on line ' . $e->getLine());
                }

            } else {
                $functionName = "balanceOf";
                //$web3 = new Web3\Web3($this->new_server_ip);
                $web3 = new Web3\Web3($this->new_server_ip.':'.$this->new_server_port.'/');
                $eth = $web3->eth;
                try {
                    switch ($token) {
                        case 'eth':
                            $eth->getBalance($walletAddress, function ($err, $balance) use (&$get_balance, &$decimal) {
                                if ( !empty($err) ) {
                                    throw new Exception($err->getMessage(), 92);
                                }
                                $get_balance = $balance->toString();
                                $get_balance = $get_balance/$decimal;
                            });
                            break;
                        default:
                            $contract = new Web3\Contract($web3->provider, $abi);
                            $contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$get_balance, &$decimal){
                                if ( !empty($err) ) {
                                    throw new Exception($err->getMessage(), 93);
                                }
                                if ( !empty( $result )) {
                                    $get_balance = reset($result)->toString();
                                    $get_balance = $get_balance/$decimal;
                                }
                            });
                            break;
                    } // switch
                } catch(Exception $e) {
                    //$get_balance = -1;
                    $this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
                }
            } // if
        }

        return $get_balance;
    } //

    // Status check
    public function wi_get_status($transactionId)
    {
        $status = 'Pending';
        $web3 = new Web3\Web3($this->new_server_ip.':'.$this->new_server_port.'/');
        $eth = $web3->eth;
        $eth->getTransactionReceipt($transactionId, function ($err, $transaction) use (&$status) {
            if ($err !== null) {
                $status = 'Pending';
            }
            else {
                if(!empty($transaction) && !empty($transaction->status)){
                    if(hexdec($transaction->status)==1){
                        $status = 'Completed';
                    }
                    else if(hexdec($transaction->status)==0){
                        $status = 'Failed';
                    }
                    else {
                        $status = 'Pending';
                    }
                }

            }
        });
        return $status;
    } //

    public function get_gas_price($type)
    {
        $gasPriceInWei = 40000000000;

        if ( empty($type) ) {
            $type = 'fast';
        }

        if ( $type == 'average1' ) {

            //require('includes/web3/vendor/autoload.php');

            //$web3 = new Web3\Web3($this->new_server_ip);
            $web3 = new Web3\Web3($this->new_server_ip.':'.$this->new_server_port.'/');
            $eth = $web3->eth;
            $eth->gasPrice(function($err,$result)use(&$gasPriceInWei){
                if ( !empty($result) ) {
                    $gasPriceInWei = $result->toString();
                }
            });
            //$gasPriceInGwei =$gasPriceInWei/1000000000;

        } else {
            // $type : safeLow < average < fast  < fastest

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://ethgasstation.info/api/ethgasAPI.json?api-key=".$this->ethApiKey,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "postman-token: bf5e409c-28bf-4abb-2670-d47bdf8f690e"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            $decodeData = json_decode($response,true);
            if (isset($decodeData[$type])) {
                $gasPriceInWei = $decodeData[$type] * 100000000;
            }
            // echo 'gasPriceInWei : '.$gasPriceInWei.' ( ' . ($gasPriceInWei/1000000000). ' Gwei)<br />';
            // $gasPriceInWei = "0x".dechex($gasPriceInWei);
        }

        return $gasPriceInWei;

    } //


    // Log
    protected function wi_fn_logSave($log)
    {
        $logPathDir = "/var/www/html/wallet2/_log";

        $filePath = $logPathDir."/".date("Y")."/".date("n");
        $folderName1 = date("Y");
        $folderName2 = date("n");

        if(!is_dir($logPathDir."/".$folderName1)){
            mkdir($logPathDir."/".$folderName1, 0777);
        }

        if(!is_dir($logPathDir."/".$folderName1."/".$folderName2)){
            mkdir(($logPathDir."/".$folderName1."/".$folderName2), 0777);
        }

        $log_file = fopen($logPathDir."/".$folderName1."/".$folderName2."/".date("Ymd").".txt", "a");
        fwrite($log_file, date("Y-m-d H:i:s ").$log."\r\n");
        fclose($log_file);
    }


    // exchange_etp3, exchange_ectc
    public function get_txId_result($txId)
    {
        $result = '';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.etherscan.io/api?module=transaction&action=gettxreceiptstatus&txhash=".$txId."&apikey=".$this->ethApiKey,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "postman-token: bf5e409c-28bf-4abb-2670-d47bdf8f690e"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $decodeData = json_decode($response,true);
        if (isset($decodeData['result']['status'])) {
            $result = $decodeData['result']['status'];
        }
        return $result;

    } //


}

?>
