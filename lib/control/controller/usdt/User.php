<?php

namespace control\controller\usdt;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use wallet\exception\WalletHttpForbiddenException;
use wallet\exception\WalletHttpNotacceptableException;
use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\common\Info as walletInfo;
use wallet\ctcDbDriver\Driver as walletDb;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

class User
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getUserWalletInfo(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $walletAuth = walletAuth::getInstance();
        $util = walletUtil::getInstance();
        $filter = walletFilter::getInstance();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        if(!$walletAuth->sessionAuthLoginCheck()){
            throw new WalletHttpNotacceptableException($request, '로그인이 필요한 서비스 입니다.');
        }

        $memberId = $walletAuth->getSessionId();
        //throw new WalletHttpForbiddenException($request);
        //throw new WalletHttpNotacceptableException($request,'ㅁㅁ');

        $memberInfo = $walletDb->createQueryBuilder()
            ->select('id, wallet_address, etoken_use, etoken_ectc, etoken_etp3, etoken_emc, etoken_ekrw, etoken_eusdt, etoken_eeth')
            ->from('admin_accounts')
            ->where('id = ?')
            ->setParameter(0,$memberId)
            ->execute()->fetch();
        if(!$memberInfo){
            throw new WalletHttpNotacceptableException($request, '로그인이 필요한 서비스 입니다.(2)');
        }
        $buildInfo = array(
            'walletAddress'=>$memberInfo['wallet_address'],
            'epayUseStatus'=>$memberInfo['etoken_use'],
            'epayBalance'=>[
                'ectc'=>$memberInfo['etoken_ectc'],
                'etp3'=>$memberInfo['etoken_etp3'],
                'emc'=>$memberInfo['etoken_emc'],
                'ekrw'=>$memberInfo['etoken_ekrw'],
                'eusdt'=>$memberInfo['etoken_eusdt'],
                'eeth'=>$memberInfo['etoken_eeth'],
            ],
            'coinBalance'=>[],
        );
        $walletInfos = new walletInfo();
        $getbalances = $walletInfos->wi_get_balance('', 'all', $memberInfo['wallet_address'], $this->container->get('contractAddressArray'));
        //$getbalances = ['aaa'=>155];
        $buildInfo['coinBalance'] = $getbalances;



        $returnArray['data'] = [
            'info' => $buildInfo
        ];

        $response->getBody()->write($util->success($returnArray));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }

    public function getUserWalletInfoTest(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $walletAuth = walletAuth::getInstance();
        $util = walletUtil::getInstance();
        $filter = walletFilter::getInstance();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();
//sessionAuth
        $targetPostData = array(
            'address' => 'string'
        );
        $filterData = $filter->postDataFilter($args,$targetPostData);
        unset($targetPostData);

        $memberInfo = $walletDb->createQueryBuilder()
            ->select('id, wallet_address, etoken_use, etoken_ectc, etoken_etp3, etoken_emc, etoken_ekrw, etoken_eusdt, etoken_eeth')
            ->from('admin_accounts')
            ->where('wallet_address = ?')
            ->setParameter(0,$filterData['address'])
            ->execute()->fetch();
        if(!$memberInfo){
            throw new WalletHttpNotacceptableException($request, '찾을 수 없는 월렛 주소 정보 입니다.');
        }
        $buildInfo = array(
            'walletAddress'=>$memberInfo['wallet_address'],
            'epayUseStatus'=>$memberInfo['etoken_use'],
            'epayBalance'=>[
                'ectc'=>$memberInfo['etoken_ectc'],
                'etp3'=>$memberInfo['etoken_etp3'],
                'emc'=>$memberInfo['etoken_emc'],
                'ekrw'=>$memberInfo['etoken_ekrw'],
                'eusdt'=>$memberInfo['etoken_eusdt'],
                'eeth'=>$memberInfo['etoken_eeth'],
            ],
            'coinBalance'=>[],
        );
        $walletInfos = new walletInfo();
        $getbalances = $walletInfos->wi_get_balance('', 'all', $memberInfo['wallet_address'], $this->container->get('contractAddressArray'));
        //$getbalances = ['aaa'=>155];
        $buildInfo['coinBalance'] = $getbalances;



        $returnArray['data'] = [
            'info' => $buildInfo
        ];

        $response->getBody()->write($util->success($returnArray));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }

}

?>