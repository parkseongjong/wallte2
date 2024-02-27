<?php

namespace control\controller\auth;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpSpecializedException;
use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

use wallet\common\Otp as walletOtp;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class Otp
{
    private $container;
    private $templatesOption = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $bufferData = $this->container->get('bufferData');
        $this->templatesOption = array(
            'info' => [
                'htmlHeader' => $bufferData['header'],
                'htmlFooter' => $bufferData['footer'],
                'lang' => $this->container->get('langArray'),
                'asstsUrl' => WALLET_URL . '/skin/common/assets',
            ]
        ) ;
    }

    public function view(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        $loginKey = (empty($args['loginKey'])?false:$args['loginKey']);
        if(!$loginKey){
            throw new Exception($this->container->get('langArray')['commonErrorString01'].'(1)', 403);
        }

        $loginKeyInfo = $loginKeyInfo = self::loginKeyCheck($loginKey);

        $templates = new plateTemplate(WALLET_PATH . '/skin/auth', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

        $this->templatesOption['info']['loginKey'] = $loginKeyInfo['aaa_key'];

        $randerData = $templates->render('otpView', $this->templatesOption);
        $response->getBody()->write($randerData);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

//    public function memberComplete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
//        $auth = walletAuth::singletonMethod();
//        $walletDb = walletDb::singletonMethod();
//        $walletDb = $walletDb->init();
//
//        if(!$auth->sessionAuthLoginCheck()){
//            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
//        }
//
//        $memberId = $auth->getSessionId();
//        $memberInfo = $walletDb->createQueryBuilder()
//            ->select('id, name, lname, id_auth, auth_name, otp_auth_secret, otp_auth_use_status, otp_auth_type')
//            ->from('admin_accounts')
//            ->where('otp_auth_secret = ?')
//            ->setParameter(0,$memberId)
//            ->execute()->fetch();
//        if(!$memberInfo){
//            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
//        }
//
//        if($memberInfo['id_auth'] == 'Y'){
//            $name = $memberInfo['auth_name'];
//        }
//        else{
//            $name = $memberInfo['lname'].$memberInfo['name'];
//        }
//
//        $otpDriver = new walletOtp(false,'hi');
//        $this->templatesOption['otpSecretKey'] = $otpDriver->getSecret();
//        ;
//        $templates = new plateTemplate(WALLET_PATH . '/skin/auth', 'html');
//        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));
//
//        $randerData = $templates->render('otpTutorialComplete', $this->templatesOption);
//        $response->getBody()->write($randerData);
//        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
//    }

    //OTP 등록 안내
    public function tutorial(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $loginKey = (empty($args['loginKey'])?false:$args['loginKey']);
        if(!$loginKey){
            throw new Exception($this->container->get('langArray')['commonErrorString01'].'(1)', 403);
        }

        $loginKeyInfo = $loginKeyInfo = self::loginKeyCheck($loginKey);
        $this->templatesOption['info']['loginKey'] = $loginKeyInfo['aaa_key'];

        $templates = new plateTemplate(WALLET_PATH . '/skin/auth', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

        $randerData = $templates->render('otpTutorial', $this->templatesOption);
        $response->getBody()->write($randerData);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    //OTP 최초 등록
    public function tutorialComplete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        $loginKey = (empty($args['loginKey'])?false:$args['loginKey']);
        if(!$loginKey){
            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
        }

        $loginKeyInfo = self::loginKeyCheck($loginKey);
        $this->templatesOption['info']['loginKey'] = $loginKeyInfo['aaa_key'];

        $memberInfo = $walletDb->createQueryBuilder()
            ->select('id, name, lname, id_auth, auth_name, otp_auth_secret, otp_auth_use_status, otp_auth_type')
            ->from('admin_accounts')
            ->where('id = ?')
            ->setParameter(0,$loginKeyInfo['aaa_accounts_id'])
            ->execute()->fetch();
        if(!$memberInfo){
            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
        }

        if($memberInfo['otp_auth_type'] == 'Y'){
            throw new Exception('이미 OTP를 발급 하였습니다. 재발급은 내 정보를 이용해주세요.', 403);
        }

        if($memberInfo['id_auth'] == 'Y'){
            $name = $memberInfo['auth_name'];
        }
        else{
            $name = $memberInfo['lname'].$memberInfo['name'];
        }

        $otpDriver = new walletOtp(false,$name);
        $this->templatesOption['info']['otpSecretKey'] = $otpDriver->getSecret();
        //발급된 비밀키로 다시 정의
        $otpDriver = new walletOtp($this->templatesOption['info']['otpSecretKey'],$name);
        $this->templatesOption['info']['otpSecretKeyQr'] = $otpDriver->getQrUrl();

        $updateProc = $walletDb->createQueryBuilder()
            ->update('admin_accounts')
            ->set('otp_auth_secret','?')
            ->set('otp_auth_use_status','?')
            ->set('otp_auth_type','?')
            ->where('id = ?')
            ->setParameter(0,$this->templatesOption['info']['otpSecretKey'])
            ->setParameter(1,'W')
            ->setParameter(2,'GOOGLE')
            ->setParameter(3,$memberInfo['id'])
            ->execute();
        if(!$updateProc){
            throw new Exception('OTP 등록 중 오류가 발생 하였습니다.',403);
        }

        $templates = new plateTemplate(WALLET_PATH . '/skin/auth', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

        $randerData = $templates->render('otpTutorialComplete', $this->templatesOption);
        $response->getBody()->write($randerData);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    //OTP 번호 인증 처리...
    public function process(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $filter = walletFilter::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();
        $util = walletUtil::singletonMethod();

        //header, filter 확인
        $filter->apiHeaderCheck($this->container->get('walletApiUserKey'));

        $targetPostData = array(
            'password' => 'stringNotEmpty'
        );
        $filterData = $filter->postDataFilter($request->getParsedBody(),$targetPostData);
        unset($targetPostData);

        $loginKey = (empty($args['loginKey'])?false:$args['loginKey']);
        if(!$loginKey){
            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
        }

        $loginKeyInfo = self::loginKeyCheck($loginKey);
        $this->templatesOption['info']['loginKey'] = $loginKeyInfo['aaa_key'];

        $memberInfo = $walletDb->createQueryBuilder()
            ->select('id, otp_auth_secret, otp_auth_use_status, otp_auth_type')
            ->from('admin_accounts')
            ->where('id = ?')
            ->setParameter(0,$loginKeyInfo['aaa_accounts_id'])
            ->execute()->fetch();
        if(!$memberInfo){
            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
        }

        $returnArray = array(
            'data' => false
        );

        if(empty($memberInfo['otp_auth_secret'])){
            throw new Exception('OTP 인증키를 발급 받아 주세요.',406);
        }

        //OTP 타입 GOOGLE 일 때...
        if($memberInfo['otp_auth_type'] == 'GOOGLE'){
            $otpDriver = new walletOtp($memberInfo['otp_auth_secret'],'none');
            if(!$otpDriver->otpCodeVerify($filterData['password'])){
                throw new Exception('잘못된 OTP 인증 번호 입니다.',406);
            }
            else{
                //otp auth 일반 인증 일때
                if($memberInfo['otp_auth_use_status'] == 'Y'){
                    $updateProc = $walletDb->createQueryBuilder()
                        ->update('admin_accounts_auth')
                        ->set('aaa_status','?')
                        ->where('aaa_id = ?')
                        ->setParameter(0,1)
                        ->setParameter(1,$loginKeyInfo['aaa_id'])
                        ->execute();
                    if(!$updateProc){
                        throw new Exception($this->container->get('langArray')['commonApiStringDanger03'],406);
                    }
                    $returnArray['data'] = [
                        'otpCode' => 10//인증 성공
                    ];
                }
                else if($memberInfo['otp_auth_use_status'] == 'W'){
                    //otp auth 최초 발급 인증 일 때.
                    $updateProc = $walletDb->createQueryBuilder()
                        ->update('admin_accounts')
                        ->set('otp_auth_use_status','?')
                        ->where('id = ?')
                        ->setParameter(0,'Y')
                        ->setParameter(1,$memberInfo['id'])
                        ->execute();
                    if(!$updateProc){
                        throw new Exception($this->container->get('langArray')['commonApiStringDanger03'],406);
                    }
                    $returnArray['data'] = [
                        'otpCode' => 20,//등록 성공
                        'otpMsg' => 'OTP 등록에 성공 하였습니다. 다시 로그인을 시도해주세요.'//등록 성공
                    ];
                }
                else{
                    throw new Exception('OTP 인증을 사용중이지 않습니다. OTP를 발급해주세요.', 406);
                }
            }

        }

        $response->getBody()->write($util->success($returnArray));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    //login key check
    private function loginKeyCheck($loginKey){
        $auth = walletAuth::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        $loginKeyInfo =$walletDb->createQueryBuilder()
            ->select('aaa_id, aaa_accounts_id, aaa_key, aaa_datetime')
            ->from('admin_accounts_auth')
            ->where('aaa_key = ?')
            ->andwhere('aaa_status = ?')
            ->andWhere('aaa_type = ?')
            ->setParameter(0,$loginKey)
            ->setParameter(1,0)
            ->setParameter(2,'OTP')
            ->execute()->fetch();
        if(!$loginKeyInfo){
            throw new Exception($this->container->get('langArray')['commonErrorString01'].'(2)', 403);
        }

        if(!$auth->adminAccountsAuthCheck(session_id(),new_getUserIpAddr(),strtotime($loginKeyInfo['aaa_datetime']),$loginKeyInfo['aaa_key'])){
            throw new Exception($this->container->get('langArray')['commonErrorString01'].'(3)', 403);
        }

        $memberInfo = $walletDb->createQueryBuilder()
            ->select('id, name, lname, id_auth, auth_name, otp_auth_secret, otp_auth_use_status, otp_auth_type')
            ->from('admin_accounts')
            ->where('id = ?')
            ->setParameter(0,$loginKeyInfo['aaa_accounts_id'])
            ->execute()->fetch();
        if(!$memberInfo){
            throw new Exception($this->container->get('langArray')['commonErrorString01'], 403);
        }

        return $loginKeyInfo;
    }

}

?>