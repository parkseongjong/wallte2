<?php
namespace control\controller\auth;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

use wallet\common\Otp as walletOtp;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class Ios
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
            }
            else{
                $response->getBody()->write('FAIL');
            }
        }
        catch (Exception $e){
            $response->getBody()->write('FAIL');
        }


        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function jailbreak(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        try{
            $postData = $request->getBody()->getContents();
            if(empty($postData)){
                throw new Exception('postData empty');
            }

            //CTC APP에서 루팅이 된 기기면 true 를 던져줌.
            if($postData == "false"){
                $response->getBody()->write('OK');
            }
            else{
                $response->getBody()->write('FAIL');
            }
        }
        catch (Exception $e){
            $response->getBody()->write('FAIL');
        }

        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}

?>