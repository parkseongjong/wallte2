<?php
/*
*   /var/www/html/wallet2/lib/ctcEncrypt/Rsa.php
*   
*
*   wallet 암호화 관련 메소드가 호출 됩니다.
*   
*/
namespace wallet\encrypt;

use \Exception;

class Rsa {

    protected $privateKey, $publicKey;
    
    public $version = '1.0.0';
    const VERSION = '1.0.0';

	public function __construct($pubKey='', $privKey='') {
        //개인키와 공개키는 ./rsaData에 저장 됩니다.
		if($pubKey){
            $this->publicKey = $pubKey;
        }
        else{
            $this->publicKey = file_get_contents('/var/www/ctc/wallet/encrypt/public.pem',false);
        }
		if($privKey){
            $this->privateKey = $privKey;
        }
        else{
            //$this->privateKey = file_get_contents('/var/www/ctc/wallet/encrypt/private.pem',false);
            $this->privateKey = file_get_contents('/var/www/ctc/wallet/encrypt/private.pem',false);
        }
	}
    public function makeKey() {
        //makeKey를 사이버트론에서도 사용 하려면 openssl 경로를 설정해주세요.
        //기본 값으로 설정된 config는 local 환경 입니다.
        $openSSLCnofig = (array(
                'config' => 'C:/server/conf/openssl.cnf',
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            ));
        $res = openssl_pkey_new($openSSLCnofig);
        openssl_pkey_export($res, $this->privateKey, NULL,$openSSLCnofig);
        $this->publicKey = openssl_pkey_get_details($res);
        $this->publicKey = $this->publicKey['key']; // key값만 재저장
	}
    public function setKey($public,$private){
        $this->publicKey = $public;
        $this->privateKey = $private;
    }
	public function viewKey() {
		return array('publicKey'=>$this->publicKey, 'privateKey'=>$this->privateKey);
	}
	public function encrypt($data) {
        try{
            $pubKey = openssl_pkey_get_public($this->publicKey);
            if(!$pubKey){
                throw new Exception('유효하지 않은 공개키 입니다.');
            }
            openssl_public_encrypt($data, $encrypted, $pubKey);
            return base64_encode($encrypted);
        }
        catch (Exception $e){
            echo $e->getMessage();
            exit();
        }
	}
	public function decrypt($data) {
        try{
            $privKey = openssl_pkey_get_private($this->privateKey);
            if(!$privKey){
                throw new Exception('유효하지 않는 개인키 입니다.');
            }
            $data = base64_decode($data);
            openssl_private_decrypt($data, $decrypted, $privKey);
            return $decrypted;
        }
        catch (Exception $e){
            echo $e->getMessage();
            exit();
        }
	}
}
?>