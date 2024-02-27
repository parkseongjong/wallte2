<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use wallet\common\Auth as walletAuth;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;
use Pachico\Magoo\Magoo as walletMasking;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

return function (App $app) {
    $app->group('/admin/withdrawal', function (RouteCollectorProxy $group) {
        try {
            $group->GET('[/{page:[0-9]+}]', function (Request $request, Response $response, array $args) {
                $auth = walletAuth::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();
                $walletMasking = new walletMasking();

                if(!$auth->sessionAuthLoginCheck() || !$auth->sessionAuthAdminCheck()){
                    throw new Exception('로그인이 필요한 서비스 입니다.', 9999);
                }
                $memberId = $auth->getSessionId();
                $pageNationInfo = array();
                if(isset($args['page'])){
                    $pageNationInfo['page'] = $args['page'];
                }
                else{
                    $pageNationInfo['page'] = 1;
                }
                $pageNationInfo['pageRow'] = 10;

                $listInfoBuilder = $walletDb->createQueryBuilder()
                    ->select('A.*, B.email')
                    ->from('withdrawal_user','A')
                    ->innerJoin('A','admin_accounts','B','A.wu_accounts_id = B.id');
                $listInfoCount = $listInfoBuilder
                    ->execute()->rowCount();

                $pageNationInfo['totalPage'] = ceil($listInfoCount / $pageNationInfo['pageRow']); // 전체 페이지 계산
                $pageNationInfo['fromRecord']  = ($pageNationInfo['page'] - 1) * $pageNationInfo['pageRow'];// 시작 열
                if($pageNationInfo['fromRecord'] < 0) $pageNationInfo['fromRecord'] = 0;

                if($pageNationInfo['page'] > $pageNationInfo['totalPage']){
                    throw new Exception('존재하지 않는 페이지 입니다.',9999);
                }

                $listInfo = $listInfoBuilder->orderBy('wu_datetime','DESC')->setFirstResult($pageNationInfo['fromRecord'])->setMaxResults($pageNationInfo['pageRow'])->execute()->fetchAll();
                foreach ($listInfo as $key =>$value){
                    $listInfo[$key]['email'] = $walletMasking->reset()->pushEmailMask()->pushUniversalIdMask()->getMasked(htmlspecialchars($value['email']));
                }

                $templates = new plateTemplate(WALLET_PATH . '/skinAdmin/withdrawal', 'html');
                $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skinAdmin/common/assets', false));

                $bufferData = $this->get('bufferData');
                $randerData = $templates->render('list', [
                    'info' => [
                        'htmlHeader' => $bufferData['header'],
                        'htmlFooter' => $bufferData['footer'],
                        'lang' => $this->get('langArray'),
                        'asstsUrl' => WALLET_URL . '/skinAdmin/common/assets',
                        'pageNationInfo' => $pageNationInfo,
                        'listInfo' => $listInfo,
                    ]
                ]);
                $response->getBody()->write($randerData);
                return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
            });
        }
        catch (Exception $e){
            //slim 에서 처리??
        }

    });
};
?>