<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

use wallet\common\Util as walletUtil;
use wallet\common\Filter as walletFilter;
use wallet\common\Push as walletPush;
use wallet\ctcDbDriver\Driver as walletDb;

use League\Plates\Engine as plateTemplate;
use League\Plates\Extension\Asset as plateTemplateAsset;

return function (App $app) {
    $app->group('/API/kiosk', function (RouteCollectorProxy $group) {
        try {

            $group->GET('/exchange/{coinType}/{price}[/{targetUnit}]', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $response->getBody()->write($util->success(['otherMsg'=>"변경된 주소를 이용해주세요."]));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            });
            //KRW -> 코인/E-pay
            $group->GET('/coin/exchange/{coinType}/{price}[/{targetUnit}]', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $filter = walletFilter::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();

                $serverParams = $request->getServerParams();

                if(!in_array($serverParams['REMOTE_ADDR'],$this->get('remoteIp'))){
                    throw new Exception('허용 된 접근이 아닙니다.',403);
                }

//                $parsedBody = $request->getParsedBody();
                $parsedBody['coin_type'] = $args['coinType'];
                $parsedBody['price'] = $args['price'];
                $parsedBody['target_unit'] = isset($args['targetUnit'])?strtoupper($args['targetUnit']):'';//path가 없으면 비어있는 상태로 저장..
                //new_config 함수
                //function new_coin_price_change_won($coin_type, $won, $coin_type2) {
                //new_coin_price_change_1won
                //new_coupon_ex_rate
                //new_walletapp_coin_list 리스트 ?
                //new_walletapp_epay_list 리스트 ?
                $targetPostData = array(
                    'coin_type' => 'stringNotEmpty',
                    'price' => 'integerNotEmpty',
                    'target_unit' => 'string'
                );
                $filterData = $filter->postDataFilter($parsedBody,$targetPostData);

                if(empty($parsedBody['target_unit'])){
                    $parsedBody['target_unit'] = 'KRW';
                }
                // /var/www/html/wallet2/config/new_config.php 변수
                /*
                 *   [0]=>
                      string(3) "ctc"
                      [1]=>
                      string(3) "tp3"
                      [2]=>
                      string(2) "mc"
                      [3]=>
                      string(3) "krw"
                      [4]=>
                      string(4) "usdt"
                      [5]=>
                      string(3) "eth"
                      [6]=>
                      string(4) "ectc"
                      [7]=>
                      string(4) "etp3"
                      [8]=>
                      string(3) "emc"
                      [9]=>
                      string(4) "ekrw"
                      [10]=>
                      string(5) "eusdt"
                      [11]=>
                      string(4) "eeth"
                 */
                $unitList = array_merge($this->get('newWalletappCoinList'),$this->get('newWalletappEpayList'));
                if(!in_array($filterData['coin_type'],$unitList)){
                    throw new Exception('coin type 필드 값이 유효하지 않습니다.',406);
                }

                foreach ($unitList as $key => $value){
                    if($value == $filterData['coin_type']){
                        if(stristr($value, 'e-') === TRUE ) {
                            $tempValue = str_replace('e-', '', $value);
                        }
                        else if(strpos($value,'e') === (int) 0 ){
                            $tempValue = substr_replace($value, '', 0,1);
                        }
                        else{
                            $tempValue = $value;
                        }
                    }
                }
                /*
                $set02Info = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('settings2')
                    ->where('module_name = ?')
                    ->andWhere('coin_type = ?')
                    ->setParameter(0,'krw_per_coin')
                    ->setParameter(1,$tempValue)
                    ->execute()->fetch();
                */
                $set02Info = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('settings')
                    ->where('module_name = ?')
                    ->setParameter(0,'krw_per_'.$tempValue.'_kiosk')
                    ->execute()->fetch();
                if(!$set02Info){
                    throw new Exception('유효한 coin이 아닙니다.',406);
                }

                if(empty($filterData['target_unit'])){
                    $filterData['target_unit'] = 'KRW';
                }
                else if($filterData['target_unit'] == 'KRW'){
                    $filterData['target_unit'] = 'KRW';
                }
                else{
                    throw new Exception('유효한 taget unit이 아닙니다.',406);
                }

                $price = bcdiv((string)$filterData['price'], $set02Info['value'], 16);

                $returnArray = array(
                    'code' => '00',
                    'msg' => 'ok',
                    'price' => $price
                );

                $response->getBody()->write($util->jsonEncode($returnArray));
                $util->logFileWrite($filterData,$returnArray,'coin-exchange-coinType-price-targetUnit','/var/www/ctc/wallet/logs/kioskAPI');
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            });

            //코인/E-pay -> KRW
            $group->GET('/currency/exchange/{coinType}/{price}[/{targetUnit}]', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $filter = walletFilter::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();

                $serverParams = $request->getServerParams();

                if(!in_array($serverParams['REMOTE_ADDR'],$this->get('remoteIp'))){
                    throw new Exception('허용 된 접근이 아닙니다.',403);
                }

//                $parsedBody = $request->getParsedBody();
                $parsedBody['coin_type'] = $args['coinType'];
                $parsedBody['price'] = $args['price'];
                $parsedBody['target_unit'] = isset($args['targetUnit'])?strtoupper($args['targetUnit']):'';//path가 없으면 비어있는 상태로 저장..
                //new_config 함수
                //function new_coin_price_change_won($coin_type, $won, $coin_type2) {
                //new_coin_price_change_1won
                //new_coupon_ex_rate
                //new_walletapp_coin_list 리스트 ?
                //new_walletapp_epay_list 리스트 ?
                $targetPostData = array(
                    'coin_type' => 'stringNotEmpty',
                    'price' => 'integerNotEmpty',
                    'target_unit' => 'string'
                );
                $filterData = $filter->postDataFilter($parsedBody,$targetPostData);

                if(empty($parsedBody['target_unit'])){
                    $parsedBody['target_unit'] = 'KRW';
                }
                // /var/www/html/wallet2/config/new_config.php 변수
                /*
                 *   [0]=>
                      string(3) "ctc"
                      [1]=>
                      string(3) "tp3"
                      [2]=>
                      string(2) "mc"
                      [3]=>
                      string(3) "krw"
                      [4]=>
                      string(4) "usdt"
                      [5]=>
                      string(3) "eth"
                      [6]=>
                      string(4) "ectc"
                      [7]=>
                      string(4) "etp3"
                      [8]=>
                      string(3) "emc"
                      [9]=>
                      string(4) "ekrw"
                      [10]=>
                      string(5) "eusdt"
                      [11]=>
                      string(4) "eeth"
                 */
                $unitList = array_merge($this->get('newWalletappCoinList'),$this->get('newWalletappEpayList'));
                if(!in_array($filterData['coin_type'],$unitList)){
                    throw new Exception('coin type 필드 값이 유효하지 않습니다.',406);
                }

                foreach ($unitList as $key => $value){
                    if($value == $filterData['coin_type']){
                        if(stristr($value, 'e-') === TRUE ) {
                            $tempValue = str_replace('e-', '', $value);
                        }
                        else if(strpos($value,'e') === (int) 0 ){
                            $tempValue = substr_replace($value, '', 0,1);
                        }
                        else{
                            $tempValue = $value;
                        }
                    }
                }
                /* settigs2 -> settings 로.. 변경
                $set02Info = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('settings2')
                    ->where('module_name = ?')
                    ->andWhere('coin_type = ?')
                    ->setParameter(0,'krw_per_coin')
                    ->setParameter(1,$tempValue)
                    ->execute()->fetch();
                */
                $set02Info = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('settings')
                    ->where('module_name = ?')
                    ->setParameter(0,'krw_per_'.$tempValue.'_kiosk')
                    ->execute()->fetch();
                if(!$set02Info){
                    throw new Exception('유효한 coin이 아닙니다.',406);
                }

                if(empty($filterData['target_unit'])){
                    $filterData['target_unit'] = 'KRW';
                }
                else if($filterData['target_unit'] == 'KRW'){
                    $filterData['target_unit'] = 'KRW';
                }
                else{
                    throw new Exception('유효한 taget unit이 아닙니다.',406);
                }
                $price = ($filterData['price'] * $set02Info['value']);

                $returnArray = array(
                    'code' => '00',
                    'msg' => 'ok',
                    'price' => $price
                );

                $response->getBody()->write($util->jsonEncode($returnArray));
                $util->logFileWrite($filterData,$returnArray,'currency-exchange-coinType-price-targetUnit','/var/www/ctc/wallet/logs/kioskAPI');
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            });


            $group->GET('/etoken/transaction/{paymentNo}', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $filter = walletFilter::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();

                $serverParams = $request->getServerParams();

                if(!in_array($serverParams['REMOTE_ADDR'],$this->get('remoteIp'))){
                    throw new Exception('허용 된 접근이 아닙니다.',403);
                }

                $parsedBody['payment_no'] = $args['paymentNo'];

                $targetPostData = array(
                    'payment_no' => 'stringNotEmpty'
                );
                $filterData = $filter->postDataFilter($parsedBody,$targetPostData);

                $logInfo = $walletDb->createQueryBuilder()
                    ->select('id, send_wallet_address, user_id, points, coin_type')
                    ->from('etoken_logs')
                    ->where('kiosk_payment_no = ?')//가맹점에 들어오는 in,out 로그 둘다 확인
                    ->andWhere('send_type = "kiosk"')
                    ->setParameter(0,$filterData['payment_no'])
                    ->execute()->fetch();
                if($logInfo){
                    $response->getBody()->write($util->success());
                    /*
                     * send_etoken 완료 시 처리 하는 것으로 변경
                     * 베리 키오스크 구매 완료 시 문자 메시지 발송.
                     * [BARRY KIOSK] OOO 님이 OO etp3 입금하였습니다
                     *  etoken log에 send wallet 데이터가 키오스크 주소임, 해당 주소로 kr 쪽 db에 조회
                     */
//                    $pushInfo = $util->getCurl(
//                        'https://cybertronchain.kr/admin/index.php/api/getPushTargetInfo',
//                        ['authKey'=>'1b39309314f7b7e4e02','walletAddress'=>$logInfo['send_wallet_address']],
//                    );
                    /*
                      ["count"]=>
                      int(1)
                      ["list"]=>
                      array(1) {
                        [0]=>
                        array(11) {
                          ["kp_id"]=>
                          string(1) "1"
                          ["kp_franchise_id"]=>
                          string(2) "17"
                          ["kp_admin_detail_id"]=>
                          string(1) "2"
                          ["kp_status"]=>
                          string(1) "Y"
                          ["kp_push_type"]=>
                          string(3) "SMS"
                          ["kp_push_type_corp"]=>
                          string(7) "COOLSMS"
                          ["kp_target"]=>
                          string(11) "01050958112"
                          ["kp_payment_type"]=>
                          string(4) "COIN"
                          ["kp_datetime"]=>
                          string(19) "2021-12-06 15:00:00"
                          ["kp_update_datetime"]=>
                          NULL
                          ["name"]=>
                          string(26) "아산 오프라인 베리"
                        }
                      }
                      ["msg"]=>
                      string(7) "success"
                     */
                    /*
                    if(!empty($pushInfo)){
                        $pushInfo = $util->jsonDecode($pushInfo);
                        if($pushInfo['count'] >= 1){// json decode 후 , count가 1 이상 일 때 push를 수행 한다.
                            if($pushInfo['list'][0]['kp_status'] == 'Y'){
                                //본인인증 처리 함수 하나 만들어야 할 것 같음
                                $authNameInfo = $walletDb->createQueryBuilder()
                                    ->select('name, lname, id_auth, auth_name')
                                    ->from('admin_accounts')
                                    ->where('id = ?')
                                    ->setParameter(0,$logInfo['user_id'])
                                    ->execute()->fetch();
                                if($authNameInfo['auth_name'] == 'Y'){
                                    $pushName = $authNameInfo['auth_name'];
                                }
                                else{
                                    $pushName = $authNameInfo['lname'].$authNameInfo['name'];
                                }
                                $walletPush = new walletPush();
                                $msg = '[BARRY KIOSK]'.$pushName.' 님이 '.abs($logInfo['points']).' '.$logInfo['coin_type'].' 입금 하였습니다.';
                                $walletPush->sendMessage($pushInfo['list'][0]['kp_target'],82,$msg, 'SMS');
                                $util->logFileWrite(['type'=>'SMS PUSH','selectWalletAddress'=>$logInfo['send_wallet_address'],'paymenetNo'=>$filterData['payment_no']],['status'=>true],'etoken-transaction-paymentNo','/var/www/ctc/wallet/logs/kioskAPI');
                            }
                        }
                    }
                    */
                    $util->logFileWrite($filterData,$logInfo,'etoken-transaction-paymentNo','/var/www/ctc/wallet/logs/kioskAPI');
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
                }
                else{
                    $response->getBody()->write($util->jsonEncode(['code'=>'40','msg'=>'트랜잭션이 존재하지 않습니다.']));
                    $util->logFileWrite($filterData,$logInfo,'etoken-transaction-paymentNo','/var/www/ctc/wallet/logs/kioskAPI');
                    return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
                }

            });

            $group->POST('/etoken/refund/{paymentNo}', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $filter = walletFilter::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();

                $serverParams = $request->getServerParams();

                if(!in_array($serverParams['REMOTE_ADDR'],$this->get('remoteIp'))){
                    throw new Exception('허용 된 접근이 아닙니다.',403);
                }

                $parsedBody = $request->getParsedBody();
                $parsedBody['payment_no'] = $args['paymentNo'];

                $targetPostData = array(
                    'payment_no' => 'stringNotEmpty',
                    'price' => 'string',
                    'coin_type' => 'string'
                );
                $filterData = $filter->postDataFilter($parsedBody,$targetPostData);

                if(empty($filterData['price'])){
                    $filterData['price'] = false;
                }
                if(empty($filterData['coin_type'])){
                    $filterData['coin_type'] = false;
                }


                //kiosk_payment_no가 out을 기준으로 정보를 불러옴.
                $logInfo = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('etoken_logs')
                    ->where('kiosk_payment_no = ?')
                    ->andWhere('send_type = "kiosk"')
                    ->andWhere('in_out = "out"')// in_out type도 추가 비교 2021.07.19 By.OJT
                    ->setParameter(0,$filterData['payment_no'])
                    ->execute()->fetch();
                if(!$logInfo){
                    throw new Exception('유효하지 않은 결제 번호 입니다.',406);
                }

                //price와 coin_Type이 없는 경우 payment 기준으로 처리, 존재 한다면 지정 한 값으로 처리.
                if($filterData['price'] === false){
                    //out으로 유입되는 값은 음수 이기 때문에 양수로 변환.
                    $filterData['price'] = abs($logInfo['points']);
                }
                else{
                    //직접 입력하는 값도..  음수를 양수로 변환
                    $filterData['price'] = abs($filterData['price']);
                }

                if($filterData['coin_type'] === false){
                    $filterData['coin_type'] = $logInfo['coin_type'];
                }

                //만약 음수를 양수로 변환 했는데도 불구하고 양수가 아닌 경우 fail 처리
                if($filterData['price'] < 0){
                    throw new Exception('points가 양의 값이 아닙니다.',406);
                }

                //지정 된 e-token(e-coin)이 아니라면 fail 처리
                if(!in_array($filterData['coin_type'],$this->get('newWalletappEpayList'))){
                    throw new Exception('지원하는 E-PAY가 아닙니다. 관리자에게 문의해주세요.',406);
                }

                //points build
                //nDecimalPointArray, DI 참조. 코딩 당시엔.. 8자리 ?
                //db 상에도 데시멀 20,8
                //debits (출금,out) credits (입금,in)
                //모두 double나 float가 아닙니다. STRING 타입으로 저장 되어 있습니다.

                $filterData['pointsDecimalBuild'] = trim(sprintf('%12.8f', $filterData['price']));
                $filterData['pointsFloat'] = (float) $filterData['price'];
                $filterData['pointsDebits'] = '-'.$filterData['pointsDecimalBuild'];
                $filterData['pointsCredits'] = '+'.$filterData['pointsDecimalBuild'];
                $filterData['cumulusTokenName'] = 'etoken_'.$filterData['coin_type'];
                $nowDateTime = $util->getDateTime();

                //e-token 이 나중에 추가 될 수 있어서.... 특정 컬럼만 불러오게 build
                $adminAccountsCumulus = array();
                foreach ($this->get('newWalletappEpayList') as $value){
                    array_push($adminAccountsCumulus,'etoken_'.$value);
                }

                //etoken log cumulus,
                $etokenLogsCumulus = array(
                    'user_id' => '?',
                    'wallet_address' => '?',
                    'coin_type' => '?',
                    'points' => '?',
                    'in_out' => '?', //5
                    'send_type' => '?',
                    'send_user_id' => '?',
                    'send_wallet_address' => '?',
                    'send_fee' => '?',
                    'created_at' => '?',
                    'kiosk_payment_no' => '?'
                );

                //더 불러올 컬럼이 있는지 확인?
                array_push($adminAccountsCumulus,'id','wallet_address');

                //가맹점 정보 build
                $marketInfo = $walletDb->createQueryBuilder()
                    ->select($adminAccountsCumulus)
                    ->from('admin_accounts')
                    ->where('id = ?')
                    ->setParameter(0,$logInfo['send_user_id'])
                    ->execute()->fetch();
                if(!$marketInfo){
                    throw new Exception('가맹점 정보를 불러오지 못하였습니다.',406);
                }

                //환불 대상자 정보 build
                $targetUserInfo = $walletDb->createQueryBuilder()
                    ->select($adminAccountsCumulus)
                    ->from('admin_accounts')
                    ->where('id = ?')
                    ->setParameter(0,$logInfo['user_id'])
                    ->execute()->fetch();
                if(!$targetUserInfo){
                    throw new Exception('사용자 정보를 불러오지 못하였습니다.',406);
                }

//                $insertData = array(
//                    9999,
//                    $filterData['pointsCredits'],
//                    'in'
//                );
//                $insertData = array(
//                    9999,
//                    $filterData['pointsDebits'],
//                    'out'
//                );
//                $walletDb->createQueryBuilder()
//                    ->insert('etoken_logs')
//                    ->setValue('user_id','?')
//                    ->setValue('points','?')
//                    ->setValue('in_out','?')
//                    ->setParameters($insertData)
//                    ->execute();

                //가맹점 e-coin 감소 처리
                $updateProc = $walletDb->createQueryBuilder()
                    ->update('admin_accounts')
                    ->set('etoken_'.$filterData['coin_type'], '?')
                    ->where('id = ?')
                    ->setParameter(0,($marketInfo[$filterData['cumulusTokenName']]-$filterData['pointsFloat']))
                    ->setParameter(1,$marketInfo['id'])
                    ->execute();
                if(!$updateProc){
                    throw new Exception('가맹점 e-coin 감소 처리를 실패 하였습니다.',406);
                }

                //감소 처리 log 기록
                $insertProc = $walletDb->createQueryBuilder()
                    ->insert('etoken_logs')
                    ->values($etokenLogsCumulus)
                    ->setParameter(0,$marketInfo['id'])//가맹점 id
                    ->setParameter(1,$logInfo['send_wallet_address']) //가맹점 wallet 주소, log상 주소 사용
                    ->setParameter(2,$filterData['coin_type'] )
                    ->setParameter(3,$filterData['pointsDebits']) // 차감
                    ->setParameter(4,'out')
                    ->setParameter(5,'kiosk_cancel')
                    ->setParameter(6,$targetUserInfo['id']) //환불 대상자 id
                    ->setParameter(7,$targetUserInfo['wallet_address']) //환불 대상자 address
                    ->setParameter(8,0)
                    ->setParameter(9,$nowDateTime)
                    ->setParameter(10,$logInfo['kiosk_payment_no'])//kiosk payment no 기록
                    ->execute();

                //환불 대상자 e-coin 증감
                $updateProc = $walletDb->createQueryBuilder()
                    ->update('admin_accounts')
                    ->set('etoken_'.$filterData['coin_type'], '?')
                    ->where('id = ?')
                    ->setParameter(0,$targetUserInfo[$filterData['cumulusTokenName']]+$filterData['pointsFloat'])
                    ->setParameter(1,$targetUserInfo['id'])
                    ->execute();
                if(!$updateProc){
                    throw new Exception('환불 대상자 e-coin 증감 처리를 실패 하였습니다.',406);
                }

                //증감 처리 log 기록
                $insertProc = $walletDb->createQueryBuilder()
                    ->insert('etoken_logs')
                    ->values($etokenLogsCumulus)
                    ->setParameter(0,$targetUserInfo['id'])//환불 대상자 id
                    ->setParameter(1,$targetUserInfo['wallet_address']) //환불 대상자 address
                    ->setParameter(2,$filterData['coin_type'] )
                    ->setParameter(3,$filterData['pointsCredits']) // 증감
                    ->setParameter(4,'in')
                    ->setParameter(5,'kiosk_cancel')
                    ->setParameter(6,$marketInfo['id']) //가맹점 id
                    ->setParameter(7,$logInfo['send_wallet_address']) // 가맹점 wallet 주소, log상 주소 사용
                    ->setParameter(8,0)
                    ->setParameter(9,$nowDateTime)
                    ->setParameter(10,null)//kiosk payment no 기록 안함
                    ->execute();

                $response->getBody()->write($util->success());
                $util->logFileWrite($filterData,$logInfo,'etoken-refund-paymentNo','/var/www/ctc/wallet/logs/kioskAPI');
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');


            });

            $group->POST('/etoken/payment/list', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $filter = walletFilter::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();

                $parsedBody = $request->getParsedBody();
                $serverParams = $request->getServerParams();


                if(!in_array($serverParams['REMOTE_ADDR'],$this->get('remoteIp'))){
                    throw new Exception('허용 된 접근이 아닙니다.',403);
                }


                $targetPostData = array(
                    'wallet_address' => 'stringNotEmpty',
                    'filter_col' => 'stringNotEmpty', // points, send_fee, id
                    'order_by' => 'stringNotEmpty',
                    'offset' => 'integer',
                    'filter_name' => 'string',
                    'page' => 'integer',
                );
                $filterData = $filter->postDataFilter($parsedBody,$targetPostData);

                //인증은 일단 제외...?
                $pageNationInfo = array();

                if(!in_array($filterData['filter_col'],['points','send_fee','id'])){
                    throw new Exception('order 필드 값이 유효하지 않습니다.',403);
                }

                if(!in_array(strtoupper($filterData['order_by']),['ASC','DESC'])){
                    throw new Exception('order 필드 값이 유효하지 않습니다.',403);
                }
                else{
                    $filterData['order_by'] = strtoupper($filterData['order_by']);
                }

                if (!empty($parsedBody['offset'])) {
                    $pageNationInfo['pageRow'] = $filterData['offset'];
                } else {
                    $pageNationInfo['pageRow'] = 10;
                }

                /*
                    2021.09.06 By.ojt
                    이름이 없는 거래 내역 노출 여부,
                    filter_name을 요청 안할 시 모두 노출
                    상대방의 이름이 이름 있는 경우만 조회 : true
                 */
                if(!empty($parsedBody['filter_name'])){
                    if(!in_array(strtoupper($filterData['filter_name']),['TRUE','FALSE'])){
                        throw new Exception('name 필드 값이 유효하지 않습니다.',403);
                    }
                    else{
                        $filterData['filter_name'] = strtoupper($filterData['filter_name']);
                    }
                }
                else{
                    $filterData['filter_name'] = 'ALL';
                }

                if(!empty($parsedBody['page'])){
                    $pageNationInfo['page'] = $filterData['page'];
                }
                else{
                    $pageNationInfo['page'] = 1;
                }

                $listInfoBuilder = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('etoken_logs','A')
                    ->where('wallet_address = ?');
                if($filterData['filter_name'] == 'ALL'){
                    // ALL 일 땐 전체가 노출..., 추 후 처리가 추가 될 수 있으니 ALL로 분기 처리 해놓고 아무런 처리 안함.
                }
                else if($filterData['filter_name'] == 'TRUE'){
                    $listInfoBuilder
                        ->andWhere('(
                                SELECT B.name FROM admin_accounts AS B 
                                WHERE B.id = A.send_user_id
                            ) IS NOT NULL');// IS NULL, IS NOT NULL는 분기가 길어지면... andWhere 하나로 사용 할 것을 권장.
                }
                else if($filterData['filter_name'] == 'FALSE'){
                    $listInfoBuilder
                        ->andWhere('(
                                SELECT B.name FROM admin_accounts AS B 
                                WHERE B.id = A.send_user_id
                            ) IS NULL');
                }
                $listInfoBuilder->setParameter(0,$filterData['wallet_address']);
                $listInfoCount = $listInfoBuilder
                    ->execute()->rowCount();

                $pageNationInfo['totalPage'] = ceil($listInfoCount / $pageNationInfo['pageRow']); // 전체 페이지 계산
                $pageNationInfo['fromRecord']  = ($pageNationInfo['page'] - 1) * $pageNationInfo['pageRow'];// 시작 열
                if($pageNationInfo['fromRecord'] < 0) $pageNationInfo['fromRecord'] = 0;

                if($pageNationInfo['page'] > $pageNationInfo['totalPage']){
                    throw new Exception('존재하지 않는 페이지 입니다.',406);
                }

                $listInfo = $listInfoBuilder->orderBy($filterData['filter_col'],$filterData['order_by'])->setFirstResult($pageNationInfo['fromRecord'])->setMaxResults($pageNationInfo['pageRow'])->execute()->fetchAll();

                $returnTargetdata = array(
                    'user_id', // 회원 테이블 조회용
                    'send_user_id', // 회원 테이블 조회용
                    'points',
                    'coin_type',
                    'wallet_address',
                    'send_wallet_address',
                    'in_out',
                    'send_fee',
                    'created_at',
                );
                $returnArray = array();
                //user_id_name
                //send_user_id_name
                foreach ($listInfo as $key => $value){
                    foreach ($value as $innerKey => $innerValue){
                        if(in_array($innerKey,$returnTargetdata)){
                            //이름 build
                            if($innerKey == 'user_id' || $innerKey == 'send_user_id'){
                                $nameBuildInfo = $walletDb->createQueryBuilder()
                                    ->select('name, lname, id_auth, auth_name')
                                    ->from('admin_accounts')
                                    ->where('id = ?')
                                    ->setParameter(0,$innerValue)
                                    ->execute()->fetch();
                                //실명인 경우와 아닌 경우
                                if($nameBuildInfo['id_auth'] == 'Y'){
                                    $returnArray['data'][$key][$innerKey.'_name'] = $nameBuildInfo['auth_name'];
                                }
                                else{
                                    $returnArray['data'][$key][$innerKey.'_name'] = $nameBuildInfo['lname'].$nameBuildInfo['name'];
                                }
                            }
                            else{
                                $returnArray['data'][$key][$innerKey] = $innerValue;
                            }
                        }
                    }
                }
                $returnArray['totalPage'] = $pageNationInfo['totalPage'];
                $util->logFileWrite($filterData,$returnArray,'etoken-payment-list','/var/www/ctc/wallet/logs/kioskAPI');
                $response->getBody()->write($util->success($returnArray));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            });

            $group->POST('/etoken/payment/list/test', function (Request $request, Response $response, array $args) {
                $util = walletUtil::singletonMethod();
                $filter = walletFilter::singletonMethod();
                $walletDb = walletDb::singletonMethod();
                $walletDb = $walletDb->init();

                $parsedBody = $request->getParsedBody();
                $serverParams = $request->getServerParams();

                if(!in_array($serverParams['REMOTE_ADDR'],$this->get('remoteIp'))){
                    throw new Exception('허용 된 접근이 아닙니다.',403);
                }

                $targetPostData = array(
                    'wallet_address' => 'stringNotEmpty',
                    'filter_col' => 'stringNotEmpty', // points, send_fee, id
                    'order_by' => 'stringNotEmpty',
                    'offset' => 'integer',
                    'filter_name' => 'string',
                    'page' => 'integer',
                );
                $filterData = $filter->postDataFilter($parsedBody,$targetPostData);

                //인증은 일단 제외...?
                $pageNationInfo = array();

                if(!in_array($filterData['filter_col'],['points','send_fee','id'])){
                    throw new Exception('order 필드 값이 유효하지 않습니다.',403);
                }

                if(!in_array(strtoupper($filterData['order_by']),['ASC','DESC'])){
                    throw new Exception('order 필드 값이 유효하지 않습니다.',403);
                }
                else{
                    $filterData['order_by'] = strtoupper($filterData['order_by']);
                }

                if (!empty($parsedBody['offset'])) {
                    $pageNationInfo['pageRow'] = $filterData['offset'];
                } else {
                    $pageNationInfo['pageRow'] = 10;
                }

                /*
                    2021.09.06 By.ojt
                    이름이 없는 거래 내역 노출 여부,
                    filter_name을 요청 안할 시 모두 노출
                    상대방의 이름이 이름 있는 경우만 조회 : true
                 */
                if(!empty($parsedBody['filter_name'])){
                    if(!in_array(strtoupper($filterData['filter_name']),['TRUE','FALSE'])){
                        throw new Exception('name 필드 값이 유효하지 않습니다.',403);
                    }
                    else{
                        $filterData['filter_name'] = strtoupper($filterData['filter_name']);
                    }
                }
                else{
                    $filterData['filter_name'] = 'ALL';
                }

                if(!empty($parsedBody['page'])){
                    $pageNationInfo['page'] = $filterData['page'];
                }
                else{
                    $pageNationInfo['page'] = 1;
                }

                $listInfoBuilder = $walletDb->createQueryBuilder()
                    ->select('*')
                    ->from('etoken_logs','A')
                    ->where('wallet_address = ?');
                if($filterData['filter_name'] == 'ALL'){
                    // ALL 일 땐 전체가 노출..., 추 후 처리가 추가 될 수 있으니 ALL로 분기 처리 해놓고 아무런 처리 안함.
                }
                else if($filterData['filter_name'] == 'TRUE'){
                    $listInfoBuilder
                        ->andWhere('(
                                SELECT B.name FROM admin_accounts AS B 
                                WHERE B.id = A.send_user_id
                            ) IS NULL');
                }
                else if($filterData['filter_name'] == 'FALSE'){
                    $listInfoBuilder
                        ->andWhere('(
                                SELECT B.name FROM admin_accounts AS B 
                                WHERE B.id = A.send_user_id
                            ) IS NOT NULL');
                }
                $listInfoBuilder->setParameter(0,$filterData['wallet_address']);
                $listInfoCount = $listInfoBuilder
                    ->execute()->rowCount();

                $pageNationInfo['totalPage'] = ceil($listInfoCount / $pageNationInfo['pageRow']); // 전체 페이지 계산
                $pageNationInfo['fromRecord']  = ($pageNationInfo['page'] - 1) * $pageNationInfo['pageRow'];// 시작 열
                if($pageNationInfo['fromRecord'] < 0) $pageNationInfo['fromRecord'] = 0;

                if($pageNationInfo['page'] > $pageNationInfo['totalPage']){
                    throw new Exception('존재하지 않는 페이지 입니다.',406);
                }

                $listInfo = $listInfoBuilder->orderBy($filterData['filter_col'],$filterData['order_by'])->setFirstResult($pageNationInfo['fromRecord'])->setMaxResults($pageNationInfo['pageRow'])->execute()->fetchAll();

                $returnTargetdata = array(
                    'user_id', // 회원 테이블 조회용
                    'send_user_id', // 회원 테이블 조회용
                    'points',
                    'coin_type',
                    'wallet_address',
                    'send_wallet_address',
                    'in_out',
                    'send_fee',
                    'created_at',
                );
                $returnArray = array();
                //user_id_name
                //send_user_id_name
                foreach ($listInfo as $key => $value){
                    foreach ($value as $innerKey => $innerValue){
                        if(in_array($innerKey,$returnTargetdata)){
                            //이름 build
                            if($innerKey == 'user_id' || $innerKey == 'send_user_id'){
                                $nameBuildInfo = $walletDb->createQueryBuilder()
                                    ->select('name, lname, id_auth, auth_name')
                                    ->from('admin_accounts')
                                    ->where('id = ?')
                                    ->setParameter(0,$innerValue)
                                    ->execute()->fetch();
                                //실명인 경우와 아닌 경우
                                if($nameBuildInfo['id_auth'] == 'Y'){
                                    $returnArray['data'][$key][$innerKey.'_name'] = $nameBuildInfo['auth_name'];
                                }
                                else{
                                    $returnArray['data'][$key][$innerKey.'_name'] = $nameBuildInfo['lname'].$nameBuildInfo['name'];
                                }
                            }
                            else{
                                $returnArray['data'][$key][$innerKey] = $innerValue;
                            }
                        }
                    }
                }
                $returnArray['totalPage'] = $pageNationInfo['totalPage'];
                $util->logFileWrite($filterData,$returnArray,'etoken-payment-list','/var/www/ctc/wallet/logs/kioskAPI');
                $response->getBody()->write($util->success($returnArray));
                return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            });

        }
        catch (Exception $e){
            //slim 에서 처리??
        }

    });
};
?>