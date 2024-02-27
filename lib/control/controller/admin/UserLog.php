<?php

namespace control\controller\admin;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use wallet\exception\WalletHttpForbiddenException;
use wallet\exception\WalletHttpNotacceptableException;

use wallet\common\Auth as walletAuth;
use wallet\common\Filter as walletFilter;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

use \Exception;

//composer를 못 쓰는 환경을 위해.. include 하는 walletLogger 입니다.
use WalletLogger\Logger as walletLogger;
include_once (BASE_PATH.'/lib/WalletLogger.php');

class UserLog
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $walletLoggerLoader = new walletLogger();
        $this->walletLogger = $walletLoggerLoader->init();
        $this->walletLoggerUtil = $walletLoggerLoader->initUtil();
    }

    public function list(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface{
        $auth = walletAuth::singletonMethod();
        $walletDb = walletDb::singletonMethod();
        $walletDb = $walletDb->init();

        if(!$auth->sessionAuthLoginCheck() || !$auth->sessionAuthAdminCheck()){
            throw new WalletHttpNotacceptableException($request,'로그인이 필요한 서비스 입니다.');
        }

        $this->walletLogger->info('관리자 모드 > 유저 로그 > 유저 로그 목록 > 조회',['admin_id'=>$this->walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$this->walletLoggerUtil->getUrl(),'action'=>'S']);

        $memberId = $auth->getSessionId();
        $pageNationInfo = array();
        if(isset($args['page'])){
            $pageNationInfo['page'] = $args['page'];
        }
        else{
            $pageNationInfo['page'] = 1;
        }
        $pageNationInfo['pageRow'] = 50 ;

        $listInfoBuilder = $walletDb->createQueryBuilder()
            ->select('A.id')
            ->from('system_user_log','A');
        $listInfoCount = $listInfoBuilder
            ->execute()->rowCount();

        //memory fix 메모리 오버플로우 때문에 전체 row count는 id 값만...
        $listInfoBuilder = $walletDb->createQueryBuilder()
            ->select('A.*')
            ->from('system_user_log','A');

        $pageNationInfo['totalPage'] = ceil($listInfoCount / $pageNationInfo['pageRow']); // 전체 페이지 계산
        $pageNationInfo['fromRecord']  = ($pageNationInfo['page'] - 1) * $pageNationInfo['pageRow'];// 시작 열
        if($pageNationInfo['fromRecord'] < 0) $pageNationInfo['fromRecord'] = 0;

        if($pageNationInfo['totalPage'] == 0){
            //데이터가 없는 경우는 어떻게 처리 할지 ?
        }
        else if($pageNationInfo['page'] > $pageNationInfo['totalPage']){
            throw new WalletHttpNotacceptableException($request,'존재하지 않는 페이지 입니다.');
        }

        $listInfo = $listInfoBuilder->orderBy('created','DESC')->setFirstResult($pageNationInfo['fromRecord'])->setMaxResults($pageNationInfo['pageRow'])->execute()->fetchAll();

        //data build
        //http://192.168.13.1:9010/admin/ctclogger
        $channelBuild = array(
            'W' => 'CTC WALLET',
            'other' => '등록 안됨',
        );
        $methodBuild = array(
            100 => 'debug',
            200 => 'info',
            250 => 'notice',
            300 => 'warning',
            400 => 'error',
            500 => 'critical',
            550 => 'alert',
            600 => 'emergency',
            'other' => '사용자 정의'
        );
        $actionBuild = array(
            1 => '조회',
            2 => '추가',
            3 => '수정',
            4 => '삭제',
            5 => '다운로드',
            0 => '기본값'
        );

        foreach ($listInfo as $key => $value){

            //채널 명 build
            if(key_exists($value['channel'],$channelBuild)){
                $listInfo[$key]['channelBuild'] = $channelBuild[$value['channel']];
            }
            else{
                $listInfo[$key]['channelBuild'] = $channelBuild[$value['other']];
            }

            //로그 레벨 build
            if(key_exists($value['log_level'],$methodBuild)){
                $listInfo[$key]['logLevelBuild'] = $methodBuild[$value['log_level']];
            }
            else{
                $listInfo[$key]['logLevelBuild'] = $methodBuild['other'];
            }

            //action 값 build
            $listInfo[$key]['actionBuild'] = $actionBuild[$value['action']];
        }
        $templates = new plateTemplate(WALLET_PATH . '/skinAdmin/userLog', 'html');
        $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skinAdmin/common/assets', false));

        $bufferData = $this->container->get('bufferData');
        $randerData = $templates->render('list', [
            'info' => [
                'htmlHeader' => $bufferData['header'],
                'htmlFooter' => $bufferData['footer'],
                'lang' => $this->container->get('langArray'),
                'asstsUrl' => WALLET_URL . '/skinAdmin/common/assets',
                'pageNationInfo' => $pageNationInfo,
                'listInfo' => $listInfo,
            ]
        ]);
        $response->getBody()->write($randerData);
        return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

}

?>