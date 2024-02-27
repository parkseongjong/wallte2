<?php

namespace control\controller\maintenance;

use control\Models\AttendanceModel;
use control\Models\PointModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wallet\common\Auth as walletAuth;
use control\handlers\WalletErrorRenderJson;
use \Exception;

class AdmobController
{
    /**
     * @var ContainerInterface
     */
    private $container;
    protected $userId;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function callback(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = 1;
        $amount = 1000;
        $data = [
            'user_id' => $userId,
            'ad_network' => 1953547073528090325,
            'ad_unit	' => 2747237135,
            'custom_data' => 'POINT',
            'key_id' => 1234567890,
            'reward_amount' => $amount,
            'reward_item' => 'POINT',
            'signature' => 'MEUCIQCLJS_s4ia_sN06HqzeW7Wc3nhZi4RlW3qV0oO-6AIYdQIgGJEh-rzKreO-paNDbSCzWGMtmgJHYYW9k2_icM9LFMY',
            'timestamp' => 1507770365237823,
            'transaction_id' => '18fa792de1bca816048293fc71035638'
        ];


//        if (isset($data["signature"])) {
//
//            //Get current admob public keys
//            $Url = 'https://www.gstatic.com/admob/reward/verifier-keys.json';
//            $verifier_keys = file_get_contents($Url);
//
//            if (!$verifier_keys_arr = json_decode($verifier_keys, true)) {
//                $error = "Invalid Public Keys";
//                $payload = json_encode([
//                    'error' => 'error',
//                    'message' => $error
//                ]);
//                $response->getBody()->write($payload);
//                return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
//            } elseif (!is_array($verifier_keys_arr)) {
//                $error = "Empty Public Keys";
//                $payload = json_encode([
//                    'error' => 'error',
//                    'message' => $error
//                ]);
//                $response->getBody()->write($payload);
//                return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
//            }
//
//            $public_key_pem = $verifier_keys_arr['keys'][0]['pem'];
//            //Admob sdk will send the query string upon watching ad, just grab them:
//            $query_string = $_SERVER['QUERY_STRING'];
//
//            $signature = trim($data['signature']);
//            // The important thing is the replacement and padding of the signature result string here
//            $signature = str_replace(['-', '_'], ['+', '/'], $signature);
//            $signature .= '===';
//
//            // The data element string for signing
//            $message = substr($query_string, 0, strpos($query_string, 'signature') - 1);
//            if (openssl_verify($message, $signature, $public_key_pem, OPENSSL_ALGO_SHA256)) {
//                $output = "verified";
//                //Get All keys from https://developers.google.com/admob/android/ssv#ssv_callback_parameters
//                $reward_item = $data['reward_item']; //CODE : POINT
//                $user_id = $data['user_id'];         //CODE : 1
//                $custom_data = $data['custom_data']; //TYPE : ATTEND
//
//                //for example
//                if ($custom_data === "GIVE_COINS") {
//                    //give coins
//                } elseif ($custom_data === "LEVEL_UP") {
//                    //level up
//                }
//
//            }
//        } else {
//            // do nothing, somebody just opened the link
//            echo `Error 404, this page not exists`;
//            die;
//        }


//            $auth = walletAuth::singletonMethod();
//            if (!$auth->sessionAuthLoginCheck() || !$auth->sessionAuthAdminCheck()) {`
//                   $payload = json_encode([
//                       'error' => 'error',
//                       'message' =>  '로그인이 필요한 서비스 입니다.'
//                   ]);
//                   $response->getBody()->write($payload);
//                   return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
//            }
//            $userId = $auth->getSessionId();


        define('SERVER_TIME', time());
        define('TIME_YMDHIS', date('Y-m-d H:i:s', SERVER_TIME));
        define('TIME_YMD', substr(TIME_YMDHIS, 0, 10));
        define('TIME_HIS', substr(TIME_YMDHIS, 11, 8));
        try {
        $attendModel = new AttendanceModel();
        $attendModel->addUserEtc($userId);
            $payload = json_encode([
                'error' => 'error',
                'message' => '오늘은 이미 출석체크를 완료 하였습니다.'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
        } catch (Exception $e) {
            $payload = json_encode([
                'error' => 'error',
                'message' => '오늘은 이미 출석체크를 완료 하였습니다.'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
        }

        try {
            $attendModel->walletDb->beginTransaction();

            $userEtc = $attendModel->getUserEtc($userId);
            if (empty($userEtc) === true) {
                $attendModel->addUserEtc($userId);
            }

            $checkCount = $attendModel->getAttendCount($userId);
            if ($checkCount > 10) {
                $payload = json_encode([
                    'error' => 'error',
                    'message' => '오늘은 이미 출석체크를 완료 하였습니다.'
                ]);
                $response->getBody()->write($payload);
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
            }

            //TODO admob log
            $lastId = $attendModel->addAttendance($userId, $data);

            //TODO tb_user_point_log
            $pointModel = new PointModel($attendModel->walletDb);
            $result = $pointModel->addUserPointLog(
                $userId,
                "ATTENDANCE",
                $amount,
                "광고시청 보상",
                'tb_attendance',
                $lastId
            );

            $attendModel->walletDb->commit();
        } catch (Exception $e) {
            $attendModel->walletDb->rollBack();
            $payload = json_encode([
                'error' => 'error',
                'message' => '이미 처리되었습니다.'
            ]);
            $response->getBody()->write($payload);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json; charset=UTF-8');
        }

        $payload = json_encode([
            'code' => '200',
            'message' => '출석체크 완료!'
        ]);
        $response->getBody()->write($payload);
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }

}