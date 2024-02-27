<?php
/*
 *
 *  by. OJT 2021.05.27 사용 중인 페이지 입니다.
 *
 *
 */
namespace wallet\oldCommon;
use wallet\oldCommon\Util as walletUtil;

use \Exception;

include_once (dirname(__FILE__).'/WalletUtil.php');

class Filter {

    public static function getInstance(){
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }
    public static function singletonMethod(){
        return self::getInstance();// static 멤버 함수 호출
    }
    protected function __construct() {

    }
    private function __clone(){

    }
    private function __wakeup(){

    }

    public function postDataFilter($data, $targetData){
            $util = walletUtil::singletonMethod();

            $filterData = array();

            foreach ($targetData as $key => $value) {
                $postValue = (isset($data[$key]) ? filter_var($data[$key], FILTER_SANITIZE_SPECIAL_CHARS) : '');
                if (empty($postValue)) {
                    throw new Exception($value['msg'],$value['code']);
                }
                $filterData[$key] = $postValue;
            }
            unset($targetData,$data);
            return $filterData;
    }

    public function apiHeaderCheck($authKeyPlain){
            $util = walletUtil::singletonMethod();

            $checkData = array(
                'headerAuthKey' => ['code'=>'88','msg'=>'알수없는 2 요청 입니다.'],
                'headerType' => ['code'=>'88','msg'=>'올바른 요청 컨텐츠 타입이 아닙니다.'],
            );

            $headers = apache_request_headers();
            //$authKey,$contentType 는 가변 변수로 선언 됩니다.
            foreach (['authorization' => 'authKey','content-type' => 'contentType'] as $key => $value){
                if(array_key_exists($key,$headers)){
                    $$value = explode(' ',$headers[$key]);
                }
                else{
                    $$value = false;
                }
            }

            foreach ($checkData as $key => $value){
                if($key == 'headerAuthKey'){
                    if($authKey[0] != 'walletKey' || $authKey[1] != $authKeyPlain){
                        throw new Exception($value['msg'],$value['code']);
                    }
                }
                else if($key == 'headerType'){
                    if($contentType[0] != 'application/json;'){
                        throw new Exception($value['msg'],$value['code']);
                    }
                }
            }
            unset($headers);
            return true;
    }

}

?>