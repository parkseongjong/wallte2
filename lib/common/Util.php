<?php
/*
 *
 * lib/WalletUtil.php 와 동일한 파일 입니다 autoload 적용을 위해 lib/WalletUtil.php은 레거시로 남겨두었습니다.
 *
 */
namespace wallet\common;

use \Psr\Http\Message\UploadedFileInterface as Files;
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

    public function notAcceptable($data = false){
        if($data == false){
            return self::jsonEncode(array('code'=>'406','msg'=>'notAcceptable'), JSON_UNESCAPED_UNICODE);
        }
        else{
            $data['code'] = '406';
            $data['msg'] = 'notAcceptable';
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

    /*
    //barry api에서 쓰던 것
    function barryapiCoinTypeChange($coin) {
        $result = '';
        $type = '';
        if( stristr($coin, 'E-') == TRUE || stristr($coin, 'e-') == TRUE ) {
            $result = str_replace('-', '', $coin);
            $result = strtolower($result);
            $type = 'epay';
        } else {
            $result = strtolower($coin);
            $type = 'coin';
        }
        return array($type, $result);
    }

    // ??? => E-TP3
    // barry api에서 쓰던 것
    function barryapiCoinTypeChange2($coin) {
        $result = '';
        if( stristr($coin, 'E-') == TRUE || stristr($coin, 'e-') == TRUE ) {
            $result = strtoupper($coin);
        } else {
            $result = 'E-'.substr($coin, 1);
        }

        return $result;
    }
    */

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
	
	public function logFileWrite($requestArray='', $responseArray='', $name = false ,$path = false) {
        if(!$name ||!$path){
            return false;
        }
        $fname = $path."/".$name.'-'.date('Y-m-d').".txt";
        $f = fopen($fname, "a");
        fwrite($f, "[".date('Y-m-d H:i:s')."] : ".$_SERVER['REMOTE_ADDR']."\n");
        fwrite($f, "[REQUEST] ---------------\n");
        foreach($requestArray as $k => $v) {
            fwrite($f, '    '.$k.'='.$v."\n");
        }
        fwrite($f, "[RESPONSE] ---------------\n");
        if (is_array($responseArray)) {
            foreach($responseArray as $k => $v) {
                if (is_array($v)) {
                    fwrite($f, '    '.$k.'='.print_r($v, true)."\n");
                } else {
                    fwrite($f, '    '.$k.'='.$v."\n");
                }
            }
        }
        else {
            fwrite($f, '    '.$responseArray."\n");
        }
        fwrite($f, "\n");
        fwrite($f, "========================================\n\n");
        fclose($f);

        return true;
    }

    /**
     * @param string $directory
     * @param Files $uploadedFile
     * @param string $option image 일때는 이미지 처리 file 때는 그 외 파일 처리
     * @return false|string
     * @throws Exception
     */
    public function slimApiMoveUploadedFile(string $directory, Files $uploadedFile, string $option){
        /*
            getStream() = object(Slim\Psr7\Stream)#
            moveTo($targetPath)
            getSize() = int(275877)
            getError()
            getClientFilename() = 4cf8b9d6129ac528adae9c1f42a76d60.jpg
            getClientMediaType() = image/jpeg
         */

        $extentsionList = array(
            'image' => 'jpg|jpeg|gif|png|swf',
            'media' => 'asx|asf|wmv|wma|mpg|mpeg|mov|avi|mp3',
            'other' => 'php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc'
        );
        //file name은 특수문자 넘어올 수 있으니 제거
        $fullFileName = preg_replace('/["\'<>=#&!%\\\\(\)\*\+\?]/', '',$uploadedFile->getClientFilename());
        $mediaType = $uploadedFile->getClientMediaType();
        $metaData = $uploadedFile->getStream()->getMetadata();
        $fileSize = $uploadedFile->getStream()->getSize();
        $extension = pathinfo($fullFileName, PATHINFO_EXTENSION);
        $singleFileName = pathinfo($fullFileName, PATHINFO_FILENAME);

        foreach ($extentsionList as $key => $value){
            //이미지 처리 이미지 확장자가 아닌경우 false 처리
            if($option == 'image'){
                if($key == 'image'){
                    if(!preg_match('/\.('.$value.')$/i', $fullFileName)){
                        return false;
                    }
                    //파일 스트림을 직접 검사, 이미지 아닌 경우 false 처리
                    //참고 사항 https://www.php.net/manual/en/function.exif-imagetype
                    $temp = exif_imagetype($metaData['uri'] );
                    if(!$temp || $temp > 16){
                        return false;
                    }
                }
            }//그 외 확장자 경우 false 처리, 아직 동영상은 처리 해야 할 이슈가 없음.
            else if($option == 'file'){
                if(!preg_match('/\.('.$value.')$/i', $fullFileName)){
                    return false;
                }//그 외 확장자 처리, 악성 파일 실행 못하게 확장자 변경,
                else{
                    $extension = $extension.'----x';
                }
            }
            else{
                return false;
            }
        }

        //참고 사항 http://php.net/manual/en/function.random-bytes.php
        //$convertSingleFileName = bin2hex(random_bytes(8));
        //유니크 함 때문에... 혹시 몰라서 바이트 수를 늘립니다.
        $realFilePath = $directory;
        while(1){
            //만약 파일명이 중복 된다면, (난수 테스트 5000개 중복 없음) 참고 사항 : http://192.168.0.10:9011/admin/barrybarries/issue/25
            $convertSingleFileName = bin2hex(random_bytes(16));
            $convertFullName = sprintf('%s.%0.5s', $convertSingleFileName, $extension);
            if(!file_exists($realFilePath.'/'.$convertFullName)) {
                break;
            }
        }
        $realFilePathLocation = $realFilePath.'/'.$convertFullName;
        $uploadedFile->moveTo($realFilePathLocation);
        //파일 퍼미션 변경
        chmod($realFilePathLocation, 0644);

        $return = array();

        if($option == 'image'){
            //위에서 한꺼번에 exif_imagetype 말고 getimagesize 처리를 해도 되지만, 비정상적인 파일이 있을 수 있기에... 이렇게 처리..
            //3번째 인자는 GD 타입이 리턴 됩니다 GB에서 파일 타입을 int 형으로 저장 하기 때문에 추가 합니다. 참고 : https://www.php.net/manual/en/image.constants.php
            list($imageWidth, $imageHeight, $predefinedImageType) = getimagesize($realFilePathLocation);
            $return['type'] = 'image';
            $return['width'] = $imageWidth;
            $return['height'] = $imageHeight;
            $return['predefinedImageType'] = $predefinedImageType;
            $return['size'] = $fileSize;
            $return['name'] = $singleFileName;
            $return['convertName'] = $convertSingleFileName;
            $return['extension'] = $extension;
            $return['path'] = $realFilePath;
            $return['pathLocation'] = $realFilePathLocation;
        }//일반 파일 처리는 아직... 추후 구현, db에 저장 해야함.
        elseif($option == 'file'){

        }
        else{
            return false;
        }


        return $return;
    }
}
?>