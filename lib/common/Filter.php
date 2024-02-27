<?php
/*
 *
 *  by. OJT 2021.05.27 사용 중인 페이지 입니다.
 *  lib/WalletFilter.php 와 동일한 파일 입니다 autoload 적용을 위해 lib/WalletFilter.php은 레거시로 남겨두었습니다.
 *
 */
namespace wallet\common;

use wallet\common\Util as walletUtil;

use \Webmozart\Assert\Assert;
use \ezyang\htmlpurifier;

use \InvalidArgumentException;
use \Exception;

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


    //API 용
    public function apiPostDataFilter($data, $targetData){
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

    //variableNotUnset 확인.
    private function variableNotUnset($data,$targetData){
        foreach ($targetData as $key => $value) {
            if(preg_match('/NotEmpty/',$value)){
                if(!isset($data[$key])){
                    throw new InvalidArgumentException('vaild error not found variable:'.$key,406);
                }
            }
        }
        return true;
    }

    public function postDataFilter($data,$targetData){
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);

        $filterData = array();
        //foreach($_POST as $key => $value){

        //variableNotUnset 확인.  // 이거 key exists 아닐 때 한번 체크를 ?
        self::variableNotUnset($data, $targetData);

        //POST 데이터가 길어질 수 있어서, POST를 기준으로 순회 돌리며 확인
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $targetData)) {
                if ($targetData[$key] == 'integer') {
                    Assert::{$targetData[$key]}((int)$value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    $filterData[$purifier->purify($key)] = (int)$purifier->purify($value);
                } else if ($targetData[$key] == 'integerNotEmpty') {
                    Assert::integer((int)$value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    if ((int)$value > 0) {
                        Assert::notEmpty((int)$value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    }
                    $filterData[$purifier->purify($key)] = (int)$purifier->purify($value);
                } else if ($targetData[$key] == 'singleChr30NotEmpty') {
                    Assert::stringNotEmpty($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    $tempValue = explode(chr(30), $value);
                    $tempValueResult = false;
                    foreach ($tempValue as $chrKey => $chrValue) {
                        if ($chrKey != 0) {
                            $tempValueResult .= chr(30) . $purifier->purify($chrValue);
                        } else {
                            $tempValueResult = $purifier->purify($chrValue);
                        }
                    }
                    $filterData[$purifier->purify($key)] = $tempValueResult;
                    unset($tempValue, $chrKey, $chrValue, $tempValueResult);
                } else if ($targetData[$key] == 'singleChr30') {
                    Assert::string($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    $tempValue = explode(chr(30), $value);
                    $tempValueResult = false;
                    foreach ($tempValue as $chrKey => $chrValue) {
                        if ($chrKey != 0) {
                            $tempValueResult .= chr(30) . $purifier->purify($chrValue);
                        } else {
                            $tempValueResult = $purifier->purify($chrValue);
                        }
                    }
                    $filterData[$purifier->purify($key)] = $tempValueResult;
                    unset($tempValue, $chrKey, $chrValue, $tempValueResult);
                } else if ($targetData[$key] == 'chr30') {
                    Assert::isArray($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    foreach ($value as $optIdKey => $optIdValue) {
                        $tempValue = explode(chr(30), $optIdValue);
                        $tempValueResult = false;
                        foreach ($tempValue as $chrKey => $chrValue) {
                            if ($chrKey != 0) {
                                $tempValueResult .= chr(30) . $purifier->purify($chrValue);
                            } else {
                                $tempValueResult = $purifier->purify($chrValue);
                            }
                        }
                        $filterData[$purifier->purify($key)][$optIdKey] = $tempValueResult;
                    }
                    unset($tempValue, $chrKey, $chrValue, $tempValueResult);
                } else if ($targetData[$key] == 'isArray') {
                    Assert::{$targetData[$key]}($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    foreach ($value as $key2 => $value2) {
                        Assert::string($value2, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                        $filterData[$purifier->purify($key)][$key2] = $purifier->purify($value2);
                    }
                } else if ($targetData[$key] == 'isArrayNotEmpty') {
                    Assert::isArray($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    foreach ($value as $key2 => $value2) {
                        Assert::stringNotEmpty($value2, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                        $filterData[$purifier->purify($key)][$key2] = $purifier->purify($value2);
                    }
                } else if ($targetData[$key] == 'selectOption') {
                    //선택 옵션 필터는 int로!
                    Assert::isArray($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    foreach ($value as $key2 => $value2) {
                        $filterData[$purifier->purify($key)][$key2] = (int)$purifier->purify($value2);
                    }
                } else if ($targetData[$key] == 'middlewareUploadFile') {
                    /*
                     *  ["files"]=>
                          array(1) {
                            ["imageFixSourceList"]=>
                            array(2) {
                              [0]=>
                              object(Slim\Psr7\UploadedFile)#179 (8) {
                                ["file":protected]=>
                                string(27) "C:\Windows\Temp\phpBB96.tmp"
                                ["name":protected]=>
                                string(5) "1.jpg"
                                ["type":protected]=>
                                string(10) "image/jpeg"
                                ["size":protected]=>
                                int(146154)
                                ["error":protected]=>
                                int(0)
                                ["sapi":protected]=>
                                bool(true)
                                ["stream":protected]=>
                                NULL
                                ["moved":protected]=>
                                bool(false)
                              }
                              [1]=>
                              object(Slim\Psr7\UploadedFile)#181 (8) {
                                ["file":protected]=>
                                string(27) "C:\Windows\Temp\phpBB97.tmp"
                                ["name":protected]=>
                                string(5) "2.jpg"
                                ["type":protected]=>
                                string(10) "image/jpeg"
                                ["size":protected]=>
                                int(119030)
                                ["error":protected]=>
                                int(0)
                                ["sapi":protected]=>
                                bool(true)
                                ["stream":protected]=>
                                NULL
                                ["moved":protected]=>
                                bool(false)
                              }
                            }
                          }
                        }
                     */
                    Assert::isArray($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    foreach ($value as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            //object 인지만 검사를.. 합니다, 실제 파일 내용 검사는 file 관련 유틸에서 수행.
                            Assert::object($value3, 'valid(not file object) error: ' . $key . ' valid type: ' . $targetData[$key]);
                            $filterData[$purifier->purify($key)][$key2][$key3] = $value3;
                        }
                    }
                } else {
                    Assert::{$targetData[$key]}($value, 'valid error: ' . $key . ' valid type: ' . $targetData[$key]);
                    $filterData[$purifier->purify($key)] = $purifier->purify($value);
                }
            }
        }

        unset($_POST, $targetData);// $targetData unset 합니다.

        return $filterData;
    }

    //application/json
    public function apiHeaderCheck($authKeyPlain){
            $util = walletUtil::singletonMethod();

            $checkData = array(
                'headerAuthKey' => ['code'=>'88','msg'=>'알수없는 2 요청 입니다.'],
                'headerType' => ['code'=>'88','msg'=>'올바른 요청 컨텐츠 타입이 아닙니다.'],
            );

            $headers = apache_request_headers();
            //$authKey,$contentType 는 가변 변수로 선언 됩니다.

            //AWS서버로 옮기며 header또는 변수 대소문자 처리 소문자로..
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

    //multipart/form-data;
    public function apiHeaderCheckMultipart($authKeyPlain){
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
                    if($contentType[0] != 'multipart/form-data;'){
                        throw new Exception($value['msg'],$value['code']);
                    }
                }
            }
            unset($headers);
            return true;
    }

}

?>