<?php
/*
 *
 * INDEX 하나를 갖고 route를 할 수 있는 상황이 아니어서 별도로 ajax 처리 후 완료 페이지나 기타 상황에 skin을 불러올 때 이용 합니다.
 *  추후 삭제 될 수 있는... 페이지 입니다... control 로 사용해서... 보여줄 예정.
 */
session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
require_once './config/config.php';
require_once './config/new_config.php';
//require_once 'includes/auth_validate.php';

use wallet\common\Auth as walletAuth;
use wallet\common\Util as walletUtil;
use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Filter as walletFilter;
use wallet\common\Request as walletRequest;
use \League\Plates\Engine as plateTemplate;
use \League\Plates\Extension\Asset as plateTemplateAsset;
require(BASE_PATH . '/vendor/autoload.php');

$bufferData = array();
//header와 footer를 별도 변수로 저장 해놓고 사용..
ob_start();
require_once WALLET_PATH.'/includes/header.php';
$bufferData['header'] = ob_get_contents();
ob_clean();
require_once WALLET_PATH.'/includes/footer.php';
$bufferData['footer'] = ob_get_contents();
ob_end_clean();

try {
    $auth = walletAuth::singletonMethod();
    $util = walletUtil::singletonMethod();
    $walletDb = walletDb::singletonMethod();
    $walletDb = $walletDb->init();
    $filter = walletFilter::singletonMethod();
    $request = walletRequest::singletonMethod();

    $request = $request->getRequest();
    $plainData = $request->getQueryParams();

    $targetPostData = array(
        'type' => 'stringNotEmpty',
    );
    $filterData = $filter->postDataFilter($plainData,$targetPostData);

    unset($targetPostData,$plainData);

    /*
     *
     * route 할게 많아지면, 클로저로 따로 분리 해야 함..
     * method 는 어떻게 처리 할지... 고민이 필요함..
     *
     */
    $templates = new plateTemplate(WALLET_PATH.'/skin', 'html');
    $templates->loadExtension(new plateTemplateAsset(WALLET_PATH.'/skin/common/assets',false));
    //ajax sleep restore 완료 시 노출 페이지
    if($request->getMethod() == 'GET' && $filterData['type'] == 'sleepRestoreFormComplete'){
        $randerData = $templates->render('/sleepUser/sleepRestoreFormComplete', [
            'info' => [
                'htmlHeader'=>$bufferData['header'],
                'htmlFooter'=>$bufferData['footer'],
                'lang'=>$langArr,
                'asstsUrl'=>WALLET_URL.'/skin/common/assets',
                'walletUrl'=>WALLET_URL,
            ]
        ]);

        echo($randerData);
    }

    exit();
}
catch (Exception $e) {
    //여긴 에러페이지 구현
    echo $util->fail(['data' => ['msg' => $e->getMessage()]]);
    exit();
}
?>