<?php
//신규 API 테스트... 2021.08.18
//WEB3 버전.
namespace wallet\common;

use wallet\common\Filter as walletFilter;
use wallet\ctcDbDriver\Driver as walletDb;

use \Web3;
use \Web3\Contract;
use \Web3\Providers\HttpProvider;
use \Web3\RequestManagers\HttpRequestManager;
require('includes/web3/vendor/autoload.php');

class InfoWeb3{

    /*
     * 노드 조회는 외부 API 이용.
     * 그 외는... 내부 geth 사용.
     *
     */
    protected $new_server_ip = 'https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e';
    protected $new_server_ip_notProtocol = 'mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e';
    protected $new_server_port = 80;

    protected $innerServerIp = 'http://195.201.168.34';
    protected $innerTempServerIp = 'http://172.31.132.111';
    protected $innerServerPort = 8545;

    //바이낸스 체인
    protected $bnbChainInnerServerIp = 'http://52.78.213.161';
    protected $bnbChainInnerServerPort = 8545;

    protected $bscLiveUrl = 'https://bsc-dataseed1.binance.org';

    protected $ethApiKey = 'ehtkey';

    //외부 API 연결...
    public function outterInit(){
        return new Web3\Web3(new HttpProvider(new HttpRequestManager($this->new_server_ip)));
    }

    // 내부 geth 연결...
    public function innerInit(){
        return new Web3\Web3($this->innerServerIp.':'.$this->innerServerPort.'/');
        //블록 다운로드 전 까지...
        //return new Web3\Web3(new HttpProvider(new HttpRequestManager($this->new_server_ip)));
    }

    public function bscChainInnerInit(){
        return new Web3\Web3($this->bscLiveUrl);
    }

    // 내부 TEMP geth 연결...
    public function binanceChainInnerInit(){
        return new Web3\Web3($this->bnbChainInnerServerIp.':'.$this->bnbChainInnerServerPort.'/');
    }

    // 내부 TEMP geth 연결...
    public function innerTempInit(){
        return new Web3\Web3($this->innerTempServerIp.':'.$this->innerServerPort.'/');
    }

    public function innerContract($provider,$abi){
        return new Contract($provider,$abi);
    }

    // Token balance check
    // $token : eth, ctc, tp3, usdt, krw, mc
    // $contractArr : config/config.php : $contractAddressArr
    public function wi_get_balance($token, $walletAddress, $contractArr) {
        if($walletAddress=="s" || empty($walletAddress) ){
            return 0;
        }

        if ( $token == 'all' ) {

        } else {
            $get_balance = 0;

            $contractAddress = $contractArr[$token]['contractAddress'];
            $decimal = $contractArr[$token]['decimal'];
            $abi = $contractArr[$token]['abi'];

            $functionName = "balanceOf";
            //$web3 = new Web3\Web3(new HttpProvider(new HttpRequestManager($this->new_server_ip)));
            $web3 = new Web3\Web3($this->innerServerIp.':'.$this->innerServerPort.'/');
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
                $get_balance = -1;
                $this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            }

        }

        return $get_balance;
    } //



    public function wi_get_bsc_balance($token, $walletAddress, $contractArr) {
        if($walletAddress=="s" || empty($walletAddress) ){
            return 0;
        }

        if ( $token == 'all' ) {

        } else {
            $get_balance = 0;

            $contractAddress = $contractArr[$token]['contractAddress'];
            $decimal = $contractArr[$token]['decimal'];
            $abi = $contractArr[$token]['abi'];

            $functionName = "balanceOf";
            //$web3 = new Web3\Web3(new HttpProvider(new HttpRequestManager($this->new_server_ip)));
            $web3 = new Web3\Web3($this->bscLiveUrl);
            $eth = $web3->eth;
            try {
                switch ($token) {
                    case 'bnb':
                        $eth->getBalance($walletAddress, function ($err, $balance) use (&$get_balance, &$decimal) {
                            if ( !empty($err) ) {
                                throw new Exception($err->getMessage(), 92);
                            }
                           $get_balance = $balance->toString();
                            $get_balance = $get_balance/(10**18);
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
                $get_balance = -1;
                $this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
            }

        }

        return $get_balance;
    } //
    // Log
    protected function wi_fn_logSave($log)
    {
        $logPathDir = "/var/www/html/wallet2/_log";  //로그위치 지정

        $filePath = $logPathDir."/".date("Y")."/".date("n");
        $folderName1 = date("Y"); //폴더 1 년도 생성
        $folderName2 = date("n"); //폴더 2 월 생성

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

}

?>
