<?php

namespace wallet;

use \OTPHP\TOTP;
use \Exception;

require __DIR__ .'/../vendor/autoload.php';

/**
 * Class WalletOtp
 * 구글 TOTP 이용, composer 패키지 확인 필요.
 * 해당 클래스를 불러올 때 autoload 사용? 아니면 include ...??
 * @package wallet\otp
 */
class Otp{

    /**
     * 3ISGR7EPN6OPQ6TJ7YOKTWGFBT7MZ33JLCCSJ37X7HL7AUYEGP4Y4A7A4KTSUCXH6VGILG6YZ4BW3DALLNFRHHE4AXSBMNX25ZBL73Q
     * google totp 기준 환경 설정.
     */
    public const OTP_LABEL = '(none TITLE)';

    /**
     *
     */
    public const OTP_ISSUER = 'CTC WALLET';

    /**
     *
     */
    public const OTP_DIGEST = 'sha1';

    /**
     *
     */
    public const OTP_DIGITS = 6;

    /**
     *
     */
    public const OTP_TIMESTEMP = 30;

    /**
     *
     */
    public const GOOGLE_QR_URL = 'https://chart.googleapis.com/chart?&cht=qr&choe=UTF-8&chl=';

    /**
     *
     */
    public const GOOGLE_QR_SIZE_WIDTH = 300;

    /**
     *
     */
    public const GOOGLE_QR_SIZE_HEIGHT = 400;

    /**
     * @var bool|\OTPHP\TOTPInterface
     */
    /**
     * @var bool|\OTPHP\TOTPInterface|null
     */
    /**
     * @var bool|mixed|\OTPHP\TOTPInterface|null
     */
    /**
     * @var bool|mixed|\OTPHP\TOTPInterface|Otp|null
     */
    protected $otp = false, $secretKey = false, $dbDriver = false, $label = false;

    /**
     * Opt constructor.
     * @param $secretKey false 일 때는 비밀키 없이..
     * @param $dbDriver
     * @param $label OTP 사용자 명
     */
    public function __construct($secretKey = false, $dbDriver = false, $label = false){
        try{
            if($secretKey === false && !empty($dbDriver) && !empty($label)){
                $key = NULL;
            }
            else if(empty($secretKey) || empty($dbDriver) || empty($label)){
                throw new Exception('생성자에 필요한 인자값이 누락 되었습니다.',9998);
            }
            else{
                $key = $secretKey;
            }

            $this->secretKey = $key;
            unset($key);
            $this->otp = TOTP::create(
                $this->secretKey, // 비밀키 init
                self::OTP_TIMESTEMP,
                self::OTP_DIGEST,
                self::OTP_DIGITS
            );

            $this->dbDriver = self::getClass($dbDriver)?$dbDriver:false;
            $this->label = $label;
            $this->otp->setLabel((!$this->label)?self::OTP_ISSUER:$this->label);
            $this->otp->setIssuer(self::OTP_ISSUER);

        }
        catch (Exception $e){
            echo self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * otp_auth_use_status , otp_auth_secret 항목이 둘다 있는지 확인.
     * @return false
     */
    public function otpAuthCheck(){
        try{
            $this->dbDriver->where('otp_auth_use_status','Y');
            $userInfo = $this->dbDriver->getOne('admin_accounts', 'id');
            if(!$userInfo){
                return false;
            }
            else{
                return true;
            }
        }
        catch (Exception $e){
            return self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public function otpCodeVerify($code){
        try{
            if(self::initCheck() === false){
                throw new Exception('생성자에 필요한 인자값이 누락 되었습니다.',9999);
            }

            return $this->otp->verify($code);
        }
        catch (Exception $e){
            return self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @return false|string
     */
    public function getSecret(){
        try{
            //중복 된 코드가 있는지 확인하고 없음 리턴 있다면 다시 한번 돌리기.
            while (1) {
                $secretKey = $this->otp->getSecret();
                $this->dbDriver->where('otp_auth_secret',$secretKey);

                if(!$this->dbDriver->getOne('admin_accounts', 'id')){
                    break;
                }
                usleep(10000);
            }
            return $secretKey;
        }
        catch (Exception $e){
            return self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @return false|string
     */
    public function getOtpCode(){
        try{
            if(self::initCheck() === false){
                throw new Exception('생성자에 필요한 인자값이 누락 되었습니다.',9999);
            }

            return $this->otp->now();
        }
        catch (Exception $e){
            return self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @return false|string
     */
    public function getQrUrl(){
        try{
            if(self::initCheck() === false){
                throw new Exception('생성자에 필요한 인자값이 누락 되었습니다.',9999);
            }

            return $this->otp->getQrCodeUri(
                self::GOOGLE_QR_URL.'[DATA]&chs='.self::GOOGLE_QR_SIZE_WIDTH.'x'.self::GOOGLE_QR_SIZE_HEIGHT,
                '[DATA]'
            );
        }
        catch (Exception $e){
            return self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @return false
     */
    private function initCheck(){
        foreach ([$this->secretKey,$this->otp,$this->dbDriver,$this->label] as $value){
            if($value == NULL || $value === false){
                return false;
            }
        }
    }

    /**
     * @param $msg
     * @param $code
     * @return false
     */
    private function getException($msg, $code){
        echo $code.':'.$msg;
        return false;
    }

    /**
     * @param object $object
     * @return string
     */
    private function getClass(object $object){
        $class = \get_class($object);
        return 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).'@anonymous' : $class;
    }

}