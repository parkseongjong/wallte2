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
use wallet\common\Log as walletLog;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

return function (App $app) {
    $app->group('/withdrawal', function (RouteCollectorProxy $group) {
        try {

            $group->GET('', function (Request $request, Response $response, array $args) {
                $auth = walletAuth::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();
                $walletMasking = new walletMasking();
                $log = new walletLog();

                if(!$auth->sessionAuthLoginCheck()){
                    throw new Exception($this->get('langArray')['withdrawalErrorString02'], 403);
                }
                $memberId = $auth->getSessionId();
                $templates = new plateTemplate(WALLET_PATH . '/skin/withdrawal', 'html');
                $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

                //혹시 모르니.. 세션 고유 id 없는 경우 fail
                if ($memberId < 0) {
                    throw new Exception($this->get('langArray')['withdrawalErrorString02'], 403);
                    //번역필요
                }

                $memberInfo = $walletDb->createQueryBuilder()
                    ->select('id, email, register_with, n_phone')
                    ->from('admin_accounts')
                    ->where('id = ?')
                    ->setParameter(0, $memberId)
                    ->execute()->fetch();
                if (!$memberInfo) {
                    throw new Exception($this->get('langArray')['withdrawalErrorString03'], 406);
                }

                $log->info('프로필 > 회원 탈퇴조회',['target_id'=>$memberInfo['id'],'action'=>'S']);

                $withdrawalInfo = $walletDb->createQueryBuilder()
                    ->select('wu_id')
                    ->from('withdrawal_user')
                    ->where('wu_accounts_id = ?')
                    ->setParameter(0,$memberInfo['id'])
                    ->execute()->fetch();
                if($withdrawalInfo){
                    throw new Exception($this->get('langArray')['withdrawalErrorString01'],406);
                }

                //노출 ID build
                if ($memberInfo['register_with'] == 'email') {
                    $convertMemberId = $walletMasking->reset()->pushEmailMask()->getMasked($memberInfo['email']);
                } else {
                    $convertMemberId = $walletMasking->reset()->pushPhoneMask(false)->getMasked($memberInfo['n_phone']);
                }

                $bufferData = $this->get('bufferData');
                $randerData = $templates->render('passwordCheckForm', [
                    'info' => [
                        'htmlHeader' => $bufferData['header'],
                        'htmlFooter' => $bufferData['footer'],
                        'lang' => $this->get('langArray'),
                        'convertMemberId' => $convertMemberId,
                        'asstsUrl' => WALLET_URL . '/skin/common/assets',
                    ]
                ]);
                $response->getBody()->write($randerData);
                return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
            });

            $group->GET('/assetinfo', function (Request $request, Response $response, array $args) {
                $auth = walletAuth::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();
                $log = new walletLog();

                if(!$auth->sessionAuthLoginCheck()){
                    throw new Exception($this->get('langArray')['withdrawalErrorString02'], 403);
                }
                $memberId = $auth->getSessionId();
                $templates = new plateTemplate(WALLET_PATH . '/skin/withdrawal', 'html');
                $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

                //혹시 모르니.. 세션 고유 id 없는 경우 fail
                if ($memberId < 0) {
                    throw new Exception($this->get('langArray')['withdrawalErrorString02'], 403);
                }

                $memberInfo = $walletDb->createQueryBuilder()
                    ->select('id, email, register_with, n_phone')
                    ->from('admin_accounts')
                    ->where('id = ?')
                    ->setParameter(0, $memberId)
                    ->execute()->fetch();
                if (!$memberInfo) {
                    throw new Exception($this->get('langArray')['withdrawalErrorString03'], 406);
                }

                $log->info('프로필 > 회원 탈퇴 조회 > 잔여 자산 확인',['target_id'=>$memberInfo['id'],'action'=>'S']);

                $withdrawalInfo = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('withdrawal_user')
                    ->where('wu_accounts_id = ?')
                    ->andWhere('wu_type = ?')
                    ->setParameter(0, $memberInfo['id'])
                    ->setParameter(1, 'asset')
                    ->execute()->fetch();
                if (!$withdrawalInfo) {
                    throw new Exception($this->get('langArray')['you_don_have_access'], 403);
                }

                $bufferData = $this->get('bufferData');
                $randerData = $templates->render('assetInfo', [
                    'info' => [
                        'htmlHeader' => $bufferData['header'],
                        'htmlFooter' => $bufferData['footer'],
                        'lang' => $this->get('langArray'),
                        'asstsUrl' => WALLET_URL . '/skin/common/assets',
                    ]
                ]);
                $response->getBody()->write($randerData);
                return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
            });

            $group->GET('/assetinfo/upload', function (Request $request, Response $response, array $args) {
                $auth = walletAuth::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();
                $log = new walletLog();

                if(!$auth->sessionAuthLoginCheck()){
                    throw new Exception($this->get('langArray')['withdrawalErrorString02'], 403);
                }
                $memberId = $auth->getSessionId();
                $templates = new plateTemplate(WALLET_PATH . '/skin/withdrawal', 'html');
                $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

                //혹시 모르니.. 세션 고유 id 없는 경우 fail
                if ($memberId < 0) {
                    throw new Exception($this->get('langArray')['withdrawalErrorString02'], 403);
                    //번역필요
                }

                $memberInfo = $walletDb->createQueryBuilder()
                    ->select('id, email, register_with, n_phone')
                    ->from('admin_accounts')
                    ->where('id = ?')
                    ->setParameter(0, $memberId)
                    ->execute()->fetch();
                if (!$memberInfo) {
                    throw new Exception($this->get('langArray')['withdrawalErrorString03'], 406);
                }

                $log->info('프로필 > 회원 탈퇴 조회 > 자산 포기 각서 제출',['target_id'=>$memberInfo['id'],'action'=>'S']);

                $withdrawalInfo = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('withdrawal_user')
                    ->where('wu_accounts_id = ?')
                    ->andWhere('wu_type = ?')
                    ->andWhere('wu_status in (?) OR wu_status is null')
                    ->setParameter(0, $memberInfo['id'])
                    ->setParameter(1, 'asset')
                    ->setParameter(2,['PENDING','REJECT'],\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                    ->execute()->fetch();
                if (!$withdrawalInfo) {
                    throw new Exception($this->get('langArray')['you_don_have_access'], 403);
                }

                $bufferData = $this->get('bufferData');
                $randerData = $templates->render('assetInfoUpload', [
                    'info' => [
                        'htmlHeader' => $bufferData['header'],
                        'htmlFooter' => $bufferData['footer'],
                        'lang' => $this->get('langArray'),
                        'asstsUrl' => WALLET_URL . '/skin/common/assets',
                    ]
                ]);
                $response->getBody()->write($randerData);
                return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');
            });

            $group->GET('/complete', function (Request $request, Response $response, array $args) {
                $log = new walletLog();
//                $auth = walletAuth::singletonMethod();
//                $walletDb = walletDb::singletonMethod();
//                $walletDb = $walletDb->init();

                //탈퇴 처리 후 세션 파괴 하기 때문에 권한 체크 없이 확인 가능하게..
//                $memberId = $auth->getSessionId();
//                $memberInfo = $walletDb->createQueryBuilder()
//                    ->select('*')
//                    ->from('withdrawal_user')
//                    ->where('wu_accounts_id = ?')
//                    ->andWhere('wu_status NOT IN (?)')
//                    ->setParameter(0,$memberId)
//                    ->setParameter(1, ['null','PENDING'],\Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
//                    ->execute()->fetch();
//                if(!$memberInfo){
//                    throw new Exception('권한이 없습니다.',9999);
//                }
//                var_dump($memberInfo);

                $log->info('프로필 > 회원 탈퇴 조회 > 회원 탈퇴 완료 페이지 조회',['target_id'=>0,'action'=>'S']);

                $templates = new plateTemplate(WALLET_PATH . '/skin/withdrawal', 'html');
                $templates->loadExtension(new plateTemplateAsset(WALLET_PATH . '/skin/common/assets', false));

                $bufferData = $this->get('bufferData');
                $randerData = $templates->render('complete', [
                    'info' => [
                        'htmlHeader' => $bufferData['header'],
                        'htmlFooter' => $bufferData['footer'],
                        'lang' => $this->get('langArray'),
                        'asstsUrl' => WALLET_URL . '/skin/common/assets',
                    ]
                ]);
                $response->getBody()->write($randerData);
                return $response->withStatus(200)->withHeader('Content-Type', 'text/html; charset=UTF-8');

            });

        }
        catch (Exception $e){
            //slim 에서 처리??
//            var_dump($e->getMessage());
//            var_dump($e->getFile());
//            var_dump($e->getLine());
//            var_dump($e->getCode());
//            echo('에러에러에러...');
//            $response->withHeader('Location', '/....')->withStatus(302);
//            echo('aaaa');
//            exit();
        }

    });
};
?>