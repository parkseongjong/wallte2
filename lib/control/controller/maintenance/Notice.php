<?php

namespace control\controller\maintenance;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use wallet\exception\WalletHttpForbiddenException;
use wallet\exception\WalletHttpNotacceptableException;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class Notice
{
    private $container;
    private $templatesOption = false;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
            /*
        $bufferData = $this->container->get('bufferData');
        $this->templatesOption = array(
            'info' => [
                'htmlHeader' => $bufferData['header'],
                'htmlFooter' => $bufferData['footer'],
            ]
        ) ;
            */
    }

    public function v1(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        if(!SYSTEM_MAINTENANCE){
            return $response->withHeader('Location', WALLET_URL)->withStatus(302);
        }
        $templates = new plateTemplate(WALLET_PATH.'/skin/systemMaintenance', 'html');
        //$html = $templates->render('V1',$this->templatesOption);
        $msgArray = array(
            'ko'=>[
                '안녕하세요.',
                '보다 나은 서비스 제공을 위해 서버 점검을 실시 합니다.',
                '작업 중에는 접속이 중지되오니 양해 부탁드립니다.',
                '작업 예정 시간 :',
                '2021-11-18 11:00 ~ 작업 완료 시 까지',
                '감사합니다.',
            ],
            'en'=>[
                'Hello.',
                'Server check is performed to provide better service.',
                'Please understand that access will be suspended during the operation.',
                'Task Scheduled Time (KST): ',
                '2021-11-18 11:00 ~ Work completed',
                'Thank you.',
            ]
        );

        $html = $templates->render('v1',['msg'=>$msgArray]);
        $response->getBody()->write($html);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

}

?>