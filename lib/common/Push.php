<?php
/*
 *  문자, 이메일 발송 통합 class 입니다.
 *
 */
namespace wallet\common;

use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;
//use League\Plates\Engine as plateTemplate; 랜더링은... 밖에서 .?
use Nurigo\Api\Message as walletSms;
use SendGrid\Mail\Mail as walletMail;
use SendGrid as walletGrid;
use \Exception;

include_once ('/var/www/html/wallet2/sendgrid-php/vendor/autoload.php');
include_once ('/var/www/html/wallet2/sms/bootstrap.php');

class Push{

    private $smsDriver;
    private $emailDriver;

    //private const SMS_API_KEY = '1234';
    //private const SMS_API_SECRET = '1234';
	private const SMS_API_KEY = 'NCSWM2DJ81J4J5V9';
    private const SMS_API_SECRET = 'WATOR3FYBG4MOJONQYTXDY5TPYLBPE4C';
    private const EMAIL_API_KEY = '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';

    private $smsFromTel = '0234893237';
    private $emailApiFrom = 'michael@cybertronchain.com';
    private $emailApiFromName = 'CyberTron Coin';

    public $filterData = array(
        'pushSendTitle' => 'CTC Wallet 회원님께 안내드립니다.',
    );

    public $mailData = array(
        'senderTitle' => 'CYBERTRON',
        'font' => "'Malgun Gothic',Apple SD Gothic Neo,sans-serif,'맑은고딕',Malgun Gothic,'굴림',gulim",
        'logoImgUrl' => 'https://cybertronchain.com/beta/images/logo.png',
    );

    public function __construct(){
        $this->emailDriver = new walletGrid(self::EMAIL_API_KEY);
        $this->smsDriver = new walletSms(self::SMS_API_KEY,self::SMS_API_SECRET);
    }

    public function sendMail($pushSendTitle = false, $pushTarget = false, $html = false){
        $emailObj = new walletMail();
        $emailObj->setFrom($this->emailApiFrom, $this->emailApiFromName);

        if($pushSendTitle === false){
            $emailObj->setSubject($this->filterData['pushSendTitle']);
        }
        else{
            $emailObj->setSubject($pushSendTitle);
        }
        if($pushTarget === false){
            throw new Exception('이메일 수신자가 설정 되지 않았습니다.');
        }

        $emailObj->addTo($pushTarget);
        $emailObj->addContent("text/html", $html);
        $response = $this->emailDriver->send($emailObj);

        if ($response->statusCode() != '202') {
            throw new Exception('메일 발송 오류!!!', 9998);;
        }

    }

    public function sendMessage($pushTarget = false, $countryCode = 82 ,$html = false, $type = 'SMS'){
        $options = new \stdClass();
        if($pushTarget === false){
            throw new Exception('메시지 수신자가 설정 되지 않았습니다.');
        }
        $options->to = $pushTarget;
        $options->from = $this->smsFromTel; // 발신번호
        $options->country = $countryCode;
        $options->type = $type; // Message type ( SMS, LMS, MMS, ATA )
        $options->text = $html; // 문자내용

        $proc = $this->smsDriver->send($options);
         if ($proc->success_count < 1) {
             throw new Exception('문자 발송 오류!!!',9999);
         }
    }

}

?>
