<?php
/*
 *
 * 헤네시스 control
 *
 */

namespace wallet\common;

use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Util as walletUtil;
use \exception;


class Henesis{

    private $henesisDockerPort = '3010';
    private $henesisDockerHost = false;
    private $henesisSecretKey = "um1u3U/johqXAIMWyqlsNZ7Pep7uFZbLaa6IzXIwakc=";
    private $henesisAuthorizationKey = "eyJhbGciOiJIUzUxMiJ9.eyJlbWFpbCI6ImtpY2s4ODg4QG9uZWZhbWlseW1hbGwuY29tIiwiaWQiOiJkYTgwYjljY2UwOWI4MjE0NGQ5MzQ2ODc5NTBiZTJjZSIsInR5cGUiOiJMT05HIiwibG9uZ1R5cGUiOnRydWUsImlzcyI6ImhlbmVzaXMtd2FsbGV0LWlkZW50aXR5LXByb2QtdGVzdG5ldCIsImlhdCI6MTYyNDUyNjcwMiwiZXhwIjoxNjU2MDYyNzAyfQ.DRceICNCP-NWUq6LTSCQnzVtzKffMf1r2itHPzV1aSv6MUthgnCrY_de2OzkSaXESDZd6gWwteWh6RxpRofoRw";
    private $henesisSendHeader = false;

    private $masterId = '8a4a3cda65557bcf36b4de3caddd5c72';
    private $masterAddress = '0x6cbed28fd248ca1014bc5e300e8e952dffd99f6b';

    public  function __construct(){
        $this->henesisDockerHost = 'http://localhost:'.$this->henesisDockerPort;
        $this->henesisSendHeader = ["Accept: application/json","X-Henesis-Secret: ".$this->henesisSecretKey,"Authorization: Bearer ".$this->henesisAuthorizationKey,"Content-Type: application/json"];
    }

    private function curlBuild($path = false, $methodType, $bodyData){

        $curl = curl_init();
        $optionArray = array(
            CURLOPT_PORT => $this->henesisDockerPort,
            CURLOPT_URL => $this->henesisDockerHost.$path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $methodType,
            CURLOPT_HTTPHEADER => $this->henesisSendHeader
        );
        if($bodyData){
            $optionArray[CURLOPT_POSTFIELDS] = json_encode($bodyData);
           // $optionArray[CURLOPT_POSTFIELDS] = '{"name":"hihi"}';
        }
        else{
            $bodyData = false;
        }
        curl_setopt_array($curl, $optionArray);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if($err){
            return false;
        }

        return $response;
    }

    public function run($uriPath, $mehod = false, $bodyData = false){
        try {
            $curlReturn = self::curlBuild($uriPath,$mehod,$bodyData);
            if($curlReturn){
                return $curlReturn;
            }
            else{
                throw new Exception('실패!',9999);
            }
        }
        catch (Exception $e){
            return ($e->getMessage().'/'.$e->getCode());
        }
    }

    //입금 주소 생성
    public function createUserDepositWallet($walletName){
        $return = json_decode(self::run('/api/v3/ethereum/wallets/'.$this->getMasterWalletId().'/deposit-addresses','POST',['name'=>$walletName]),true);
        if(key_exists('code',$return)){
            if($return['code'] == 4006){
                return false;
            }
        }
        else{
            return $return;
        }
    }
    //사용자가 입금 지갑을 이미 만들었는지 조회, 존재하면 데이터를... 리턴 아니면 FALSE
    public function createUserDepositWalletCheck($memberId){
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->initTestStorage();

        $info = $walletDb->createQueryBuilder()
            ->select('henesis_child_wallet_id, henesis_child_wallet_address')
            ->from('admin_accounts')
            ->where('id = ?')
            ->andWhere('henesis_child_wallet_id is not null')
            ->andWhere('henesis_child_wallet_address is not null')
            ->setParameter(0,$memberId)
            ->execute()->fetch();
        if($info){
            return $info;
        }
        else{
            return false;
        }
    }

    public function createUserDepositWalletInsert($memberId,$henesis_child_wallet_id, $henesis_child_wallet_address){
        $walletDb = walletDb::singletonMethod();
        $walletUtil = walletUtil::singletonMethod();
        $walletDb = $walletDb->initTestStorage();


        $proc = $walletDb->createQueryBuilder()
            ->update('admin_accounts')
            ->set('henesis_child_wallet_id','?')
            ->set('henesis_child_wallet_address','?')
            ->set('henesis_child_wallet_datetime','?')
            ->where('id = ?')
            ->setParameter(0,$henesis_child_wallet_id)
            ->setParameter(1,$henesis_child_wallet_address)
            ->setParameter(2,$walletUtil->getDateSql())
            ->setParameter(3,$memberId)
            ->execute();
        if($proc){
            return true;
        }
        else{
            return false;
        }
    }
    
    //입금 주소 조회
    public function selectUserDepositWallet($depositAddressId){
        $return = json_decode(self::run('/api/v3/ethereum/wallets/'.$this->getMasterWalletId().'/deposit-addresses/'.$depositAddressId,'GET'),true);
        if(key_exists('code',$return)){
            if($return['code'] == 4000){
                return false;
            }
        }
        else{
            return $return;
        }
    }
    //입금 주소 잔여액 조회
    //ticker ETH
    public function selectUserDepositWalletBalance ($depositAddressId, $ticker = null){
        $return = json_decode(self::run('/api/v3/ethereum/wallets/'.$this->getMasterWalletId().'/deposit-addresses/'.$depositAddressId.'/balance?ticker='.$ticker,'GET'),true);
        var_dump(self::run('/api/v3/ethereum/wallets/'.$this->getMasterWalletId().'/deposit-addresses/'.$depositAddressId.'/balance?ticker='.$ticker,'GET'));
        var_dump('/api/v3/ethereum/wallets/'.$this->getMasterWalletId().'/deposit-addresses/'.$depositAddressId.'/balance?ticker='.$ticker);
        if(key_exists('code',$return)){
            if($return['code'] == 4000){
                return false;
            }
        }
        else{
            return $return;
        }
    }

    //입금 주소 잔액 마스터로 전송
    public function flush($gasPrice = 0, $gasLimit = null, $target){
        $return = json_decode(self::run('/api/v3/ethereum/wallets/'.$this->getMasterWalletId().'/flush','POST',['gasPrice'=>$gasPrice,'gasLimit'=>$gasLimit, 'target'=>$target]),true);
        if(key_exists('code',$return)){
            if($return['code'] == 4000){
                return false;
            }
        }
        else{
            return true;
        }
    }

    //헤네시스 지원 가상 자산 조회
    public function coins(){
        $return = json_decode(self::run('/api/v3/ethereum/coins','GET'),true);
        if(key_exists('code',$return)){
            if($return['code'] == 4000){
                return false;
            }
        }
        else{
            return $return;
        }
    }

    //헤네시스 전용 트랜잭션 ID 조회
    public function selectHenesisTransaction($id = false){
        $return = json_decode(self::run('/api/v3/ethereum/transactions/'.$id,'GET'),true);
        if(key_exists('code',$return)){
            if($return['code'] == 4000){
                return false;
            }
        }
        else{
            return $return;
        }
    }


    public function getMasterWalletId(){
        return $this->masterId;
    }
    public function getMasterWalletAddress(){
        return $this->masterAddress;
    }

}