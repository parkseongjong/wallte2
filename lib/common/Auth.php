<?php

namespace wallet\common;

use wallet\common\Filter as walletFilter;

class Auth{

    const APP_PUBLIC_SECRET_KEY = '61:79:F7:45:6E:EA:32:FF:FD:9F:AE:03:F1:7A:6E:F6:94:33:34:A1:24:68:18:5F:6A:E2:73:15:2D:34:60:EE';  // APP AUTH 비밀키
    const APP_ALIAS = 'CTCWallet2025'; // APP STORE ID
    const APP_ALIAS_PW = '(T(Wallet2O25'; // APP STORE PW

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

    public function sessionAuth(){
        if(!isset($_SESSION['user_id'])){
            return false;
        }
        else{
            return true;
        }
    }
    public function getSessionId(){
        if(isset($_SESSION['user_id'])){
            return $_SESSION['user_id'];
        }
        else{
            return false;
        }
    }
    //휴면 계정 활성화에 쓰이는 temp session 입니다.
    public function sessionAuthTemp(){
        if(!isset($_SESSION['tempUserId'])){
            return false;
        }
        else{
            return true;
        }
    }
    public function getSessionIdTemp(){
        if(isset($_SESSION['tempUserId'])){
            return $_SESSION['tempUserId'];
        }
        else{
            return false;
        }
    }

    public function sessionAuthAdminCheck(){
        if(!isset($_SESSION['admin_type'])){
            return false;
        }
        else{
            if($_SESSION['admin_type'] != 'admin'){
                return false;
            }
            else{
                return true;
            }
        }
    }

    public function sessionAuthLoginCheck(){
        if(!isset($_SESSION['user_logged_in'])){
           return false;
        }
        else{
            if(!$_SESSION['user_logged_in']){
                return false;
            }
            else{
                return true;
            }
        }
    }

    //각종 인증 KEY 유효성 검사
    public function adminAccountsAuthCheck($sessionId, $ip, $dbTimestemp, $hash){
        //20분 초과 시 유효하지 않음
        if(time() >= strtotime ( '+20 minutes' , $dbTimestemp)){
            return false;
        }
        $signature = hash('sha256',trim($sessionId.$ip),false);
        if($hash != $signature){
            return false;
        }
        else{
            return true;
        }
    }

    /*
     *
     * 앱 단에서 탈옥 여부, 루팅 여부(앱변조)를 확인해서 SHA와 DEVICE ID를 던져준다. 해당 값 확인해서 valid 체크...
     *
     */
    public function appAuthenticate($data){
        $filter = walletFilter::singletonMethod();

        $targetPostData = array(
            'deviceId' => 'stringNotEmpty',
            'deviceValid' => 'stringNotEmpty'
        );
        $filterData = $filter->postDataFilter($data,$targetPostData);
        $signature = hash_hmac('sha256',self::APP_ALIAS.self::APP_ALIAS_PW.$filterData['deviceId'],self::APP_PUBLIC_SECRET_KEY);

        //error_logh('test:'.hash_hmac('sha256',self::APP_ALIAS.self::APP_ALIAS_PW.$filterData['deviceId'],self::APP_PUBLIC_SECRET_KEY));

        error_log('raw:'.print_r($filterData,true),0);
        error_log('origin:'.$filterData['deviceValid'],0);
        error_log('signature:'.$signature,0);
        if($filterData['deviceValid'] == $signature){
            return true;
        }
        else{
            return false;
        }
    }


}

?>
