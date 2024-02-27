<?php
/*
 *
 * lib/WalletUtil.php 와 동일한 파일 입니다 autoload 적용을 위해 lib/WalletUtil.php은 레거시로 남겨두었습니다.
 *
 */
namespace wallet\oldCommon;

use \Exception;

class Util {

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

    //array 로 decode
    public function jsonDecode($data){
        return json_decode($data,true);
    }

    public function jsonEncode($data){
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function success($data = false){
        if($data == false){
            return self::jsonEncode(array('code'=>'00','msg'=>'ok'), JSON_UNESCAPED_UNICODE);
        }
        else{
            $data['code'] = '00';
            $data['msg'] = 'ok';
            return self::jsonEncode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    public function authFail($data = false){
        if($data == false){
            return self::jsonEncode(array('code'=>'403','msg'=>'authFail'), JSON_UNESCAPED_UNICODE);
        }
        else{
            $data['code'] = '403';
            $data['msg'] = 'authFail';
            return self::jsonEncode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    public function fail($data = false){
        if($data == false){
            return self::jsonEncode(array('code'=>'404','msg'=>'fail'), JSON_UNESCAPED_UNICODE);
        }
        else{
            $data['code'] = '404';
            $data['msg'] = 'fail';
            return self::jsonEncode($data, JSON_UNESCAPED_UNICODE);
        }
    }

    //now date
    public function getDateTime(){
        $key = (string) date('YmdHis', time());
        return $key;
    }

    //now date
    public function getDateSql(){
        $key = (string) date('Y-m-d H:i:s', time());
        return $key;
    }

    public function getDateSqlDefault(){
        $key = '0000-00-00 00:00:00';
        return $key;
    }

    //not iso unixDate(YmdHis ex.20210121163029) -> sqlDate(Y-m-d H:i:s ex.2021-01-21 16:30:29)
    public function getSqlDateInNotIsoUnixDateTimeConvert($date){
        $key = (string) date('Y-m-d H:i:s', strtotime($date));
        return $key;
    }

    //iso unixDate(time stamp ex.1611219583) -> sqlDate(Y-m-d H:i:s ex.2021-01-21 16:30:29)
    public function getSqlDateInIsoUnixDateTimeConvert($date){
        $key = (string) date('Y-m-d H:i:s', $date);
        return $key;
    }

    public function priceFilter($price){
        $price = rtrim($price,0);
        $price = rtrim($price,'.');
        $price = str_replace(',','',$price);

        return $price;
    }

    public function getCurl($url = false, $data = false){
        try{
            $curl = curl_init();

            $data = http_build_query($data);

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "gzip",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 3000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'Content-Length:'.strlen($data),
                    "cache-control: no-cache"
                ),
                CURLOPT_VERBOSE => false
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if($err){
                throw new Exception('curl error');
            }

            return $response;

        }
        catch (Exception $e){
            return $e->getMessage();
        }
    }

    //서버끼리 API 통신 시 위변조 체크
    public function serverCommunicationAuth($protocol,$token,$timestemp,$signature){
        //5분 초과 시 유효하지 않음
        if(time() >= strtotime ( '+5 minutes' , $timestemp)){
            return false;
        }
        $tokenHash = md5($token);
        $buildSignature = hash('sha256',trim($protocol.'|'.$tokenHash.'|'.$timestemp),false);
        if($buildSignature != $signature){
            return false;
        }
        else{
            return true;
        }
    }

    //CTC WALLET : walletadmin
    //BARRY : barryadmin
    public function serverCommunicationBuild($protocol = false,$token){
        if($protocol === false){
            $protocol = 'barryadmin';
        }
        $timestemp = time();
        $tokenHash = md5($token);
        return [
            'signature' => hash('sha256',trim($protocol.'|'.$tokenHash.'|'.$timestemp),false),
            'timestamp' => $timestemp,
            'value' => $token
        ];
    }
}
?>