<?php

namespace wallet\common;

use wallet\ctcDbDriver\Driver as walletDb;
use \OTPHP\TOTP;
use \Exception;

/**
 * Class WalletOtp
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
    public function __construct($secretKey = false, $label = false){
        try{
            if($secretKey === false && !empty($label)){
                $key = NULL;
            }
            else if(empty($secretKey) || empty($label)){
                throw new Exception('생성자에 필요한 인자값이 누락 되었습니다.(0)',403);
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
            $walletDb = walletDb::singletonMethod();
            $this->dbDriver = $walletDb->init();
            $this->label = $label;
            $this->otp->setLabel((!$this->label)?self::OTP_ISSUER:$this->label);
            $this->otp->setIssuer(self::OTP_ISSUER);

        }
        catch (Exception $e){
            echo self::getException($e->getMessage(),$e->getCode());
        }
    }

    /**
     * @param $code
     * @return bool
     */
    public function otpCodeVerify($code){
        try{
            if(self::initCheck() === false){
                throw new Exception('OTP 생성에 필요한 정보가 누락 되었습니다.(1)',403);
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
            $secretKey = $this->otp->getSecret();
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
                throw new Exception('OTP 생성에 필요한 정보가 누락 되었습니다.(2)',403);
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
                throw new Exception('OTP 생성에 필요한 정보가 누락 되었습니다.(3)',403);
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
//        echo $code.':'.$msg;
        throw new Exception($msg,$code);
//        return false;
    }

}