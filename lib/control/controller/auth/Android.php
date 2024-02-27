<?php
//하쌈이 구성한 구조는 사용하지 않는 쪽으로...
//lib common auth에 app valid 확인 하는 것 추가.
//라드가 API으로 제공 요청
namespace control\controller\auth;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class Android
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function authenticate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
		$auth = walletAuth::singletonMethod();

        try{
            //$postData = $request->getParsedBody();
            $postData = $request->getBody()->getContents();
            if(empty($postData)){
                throw new Exception('postData empty');
            }
            $rawDataFilter = explode(',',$postData);

            if(!isset($rawDataFilter[0]) || !isset($rawDataFilter[1])){
                throw new Exception('raw key exists empty');
            }


            if($auth->appAuthenticate(['deviceValid'=>$rawDataFilter[0],'deviceId'=>$rawDataFilter[1]])){
                $response->getBody()->write('OK');
                $_SESSION['androidAuthenticateCheck'] = true;
            }
            else{
                $response->getBody()->write('FAIL');
                $_SESSION['androidAuthenticateCheck'] = false;
            }
        }
        catch (Exception $e){
            $response->getBody()->write('FAIL');
        }


        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function jailbreak(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $util = walletUtil::singletonMethod();
        //굳이 데이터 필터 안해도 될듯?
        try{
            //$postData = $request->getParsedBody();
            $postData = $request->getBody()->getContents();
            if(empty($postData)){
                throw new Exception('postData empty');
            }

            //CTC APP에서 루팅이 된 기기면 true 를 던져줌.
            if($postData == "false"){
                $response->getBody()->write('OK');
                $_SESSION['androidJailbreakCheck'] = true;
            }
            else{
                $response->getBody()->write('FAIL');
                $_SESSION['androidJailbreakCheck'] = false;
            }
        }
        catch (Exception $e){
            $response->getBody()->write('FAIL');
        }

        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
	
	//하쌈코드
    /*
    public function OLDauthenticate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $filter = walletFilter::singletonMethod();
        $util = walletUtil::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        //모든 디바이스에서 해시 값이 올 때 ID, PW 동일하게 보내준다고 함........... 이거 라드가 할 때 다시 확인을 해야 할 것 같음.
        //라드에게 확인 결과 google play 스토어에서 동일하게 보내주는 HASH 값 이라고 함.
        $rawData = $request->getBody()->getContents();
        $targetPostData = array(
            'rawData' => 'stringNotEmpty'
        );
        $filterData = $filter->postDataFilter(['rawData'=>$rawData],$targetPostData);
        $signature = hash_hmac('sha256',$this->container->get('APP_ALIAS').$this->container->get('APP_ALIAS_PW').$filterData['deviceId'],$this->container->get('APP_PUBLIC_SECRET_KEY'));

        var_dump($filterData);
        if(!empty($filterData['rawData'])){
            if($filterData['rawData'] == $signature){
                //라드한테 이야기해서.. app alias , pw .. 값 ...확인.
                $mobileAuth = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('mobile_auth')
                    ->orderBy('id','DESC')
                    ->execute()->fetch();
                    //->where()// where 해야함..
                if(!empty($mobileAuth)){
                    $walletDb->createQueryBuilder()
                        ->update('mobile_auth')
                        ->set('signature','?')
                        ->set('modified_at','?')
                        ->setParameter(0,$filterData['rawData'])
                        ->setParameter(1,$util->getDateSql())
                        //->where()//where 해야함.
                        ->execute();
                }
                else {
                    //WHERE 문 확인...
                    $walletDb->createQueryBuilder()
                        ->insert('mobile_auth')
                        ->setValue('signature','?')
                        ->setParameter(0,$filterData['rawData'])
                        ->execute();
                }
                $response->getBody()->write('OK');
            }
            else {
                $response->getBody()->write('ERROR');
            }
        }
        else {
            $response->getBody()->write('Empty Object');
        }
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
	
	//하쌈코드
    public function authjblogin(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $util = walletUtil::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        //모든 디바이스에서 해시 값이 올 때 ID, PW 동일하게 보내준다고 함........... 이거 라드가 할 때 다시 확인을 해야 할 것 같음.
        $this->container->get('APP_ALIAS_PW');
        $signature = hash_hmac('sha256', $this->container->get('APP_PUBLIC_SECRET_KEY') . $this->container->get('APP_ALIAS'),$this->container->get('APP_ALIAS_PW'));
        $rawData = $request->getBody()->getContents();

        if(!empty($rawData)){
            if($rawData == $signature){
                //라드한테 이야기해서.. app alias , pw .. 값 ...확인.
                $mobileAuth = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('mobile_auth')
                    ->orderBy('id','DESC')
                    ->execute()->fetch();
                    //->where()// where 해야함..
                if(!empty($mobileAuth)){
                    $walletDb->createQueryBuilder()
                        ->update('mobile_auth')
                        ->set('signature','?')
                        ->set('modified_at','?')
                        ->setParameter(0,$rawData)
                        ->setParameter(1,$util->getDateSql())
                        //->where()//where 해야함.
                        ->execute();
                }
                else {
                    //WHERE 문 확인...
                    $walletDb->createQueryBuilder()
                        ->insert('mobile_auth')
                        ->setValue('signature','?')
                        ->setParameter(0,$rawData)
                        ->execute();
                }
                $response->getBody()->write('OK');
            }
            else {
                $response->getBody()->write('ERROR');
            }
        }
        else {
            $response->getBody()->write('Empty Object');
        }
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
    */
}

?>