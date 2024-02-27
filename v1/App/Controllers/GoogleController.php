<?php

namespace App\Controllers;

use Respect\Validation\Validator as V;
use App\Exception\InvalidArgumentException;
use App\Exception\BadRequestException;
use App\Exception\ApiException;
use App\Exception\CustomException;

use Exception;

use App\Models\AdmobModel;
use App\Models\PointModel;
use App\Models\PurchaseModel;

use wallet\common\Auth as walletAuth;
use App\Library\Admob\Signature;
use App\Service\GoogleService;
use App\Service\AuthService;
use App\Library\Log;

/**
 * Class BaseController
 *
 * @package App\Controllers\BaseController
 * @author jso
 * @description 구글 API 레퍼런스: https://developers.google.com/android-publisher/api-ref/purchases/products
 */
class GoogleController extends BaseController
{
    const APP_TYPE = 'android';

    /**
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     * @throws ApiException
     * @throws \Doctrine\DBAL\Exception
     * @throws \App\Exception\UnauthorizedException
     * @description 광고 보기 확인용 최대 7일
     */
    public function admobDisplay()
    {
        $this->isAuth();
        $admobModel = new AdmobModel();

        //TODO 유저 ETC 테일블 조회 후 포인트 데이터 밀어넣기
        $userEtcInfo = $admobModel->getUserEtc($this->userID);
        if (empty($userEtcInfo) === true) {
            $rowCount = $admobModel->addUserEtc($this->userID);
        }

        //TODO 유저 참여 체크
        $display = 'show';
        $admob = $admobModel->getAttendInfo($this->userID);
        $nextTime = strtotime("+10 minutes", $admob['created']);
        if ($admob['cnt'] == 5 && SERVER_TIME < $nextTime) {
            $display = 'hide';
        }

        $nextTime = strtotime("+1 hours", $admob['created']);
        if ($admob['cnt'] == 6 && SERVER_TIME < $nextTime) {
            $display = 'hide';
        }

        //TODO 오늘 하루 광고 보기 완료
        if ($admob['cnt'] >= 7) {
            $display = 'hide';
        }

        static::responses([
            'display' => $display
        ]);
    }


    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @description 광고 보상
     */
    public function admob()
    {
        $this->isAuth();
        $this->validator->validate($this->params, [
            'signature' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_signature.'
            ],
            'transaction_id' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_transaction_id'
            ],
            'key_id' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_key_id'
            ],
            'ad_network' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_ad_network'
            ],
            'ad_unit' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_ad_unit'
            ],
            'reward_item' => [
                'rules' => V::notEmpty(),
                'message' => 'Invalid_Reward_Item'
            ],
        ])->isValid();

        $log = new Log();
        try {
            $log->instance->info(json_encode($this->params));
        } catch (Exception $e) {
            $log->instance->error($e->getMessage());
        }

        $data = $this->params;
        $admobItem = \App\Service\ItemService::ADMOB['ADMOB_FRONT_001'];
        if (empty($admobItem) === true) {
            throw new BadRequestException('Invalid_Reward_Item');
        }

        try {
            $queryString = $this->request['QUERY_STRING'];
            $Signature = new Signature($queryString);
            $Signature->verify();
        } catch (Exception $e) {
            throw new BadRequestException($e->getMessage());
        }

        $admobModel = new AdmobModel();
        try {
            $admobModel->walletDb->beginTransaction();

            //TODO 유저 ETC 테일블 조회 후 포인트 데이터 밀어넣기
            $userEtcInfo = $admobModel->getUserEtc($this->userID);
            if (empty($userEtcInfo) === true) {
                $rowCount = $admobModel->addUserEtc($this->userID);
            }

            //TODO 유저 참여 체크
            $admob = $admobModel->getAttendInfo($this->userID);
            $nextTime = strtotime("+10 minutes", $admob['created']);
            if ($admob['cnt'] == 5 && SERVER_TIME < $nextTime) {
                $timeDiffer = getWaitTime($nextTime - SERVER_TIME);
                throw new CustomException('waiting_time', $timeDiffer);
            }

            $nextTime = strtotime("+1 hours", $admob['created']);
            if ($admob['cnt'] == 6 && SERVER_TIME < $nextTime) {
                $timeDiffer = getWaitTime($nextTime - SERVER_TIME);
                throw new CustomException('waiting_time', $timeDiffer);
            }

            //TODO 오늘 하루 광고 보기 완료
            if ($admob['cnt'] >= 7) {
                throw new BadRequestException('finished_watching_ad');
            }

            //TODO 광고 시청 로그 (admob log)
            $admobInfo = $admobModel->getAttend($this->userID, $data['transaction_id']);
            if (empty($admobInfo) === false) {
                throw new BadRequestException('completed_request');
            }
            $lastId = $admobModel->addAttendance($this->userID, $data);

            //TODO 유저 포인트 로그
            $balance = $admobModel->addUserFreePointLog(
                $this->userID,
                $admobModel::ATTENDANCE['CODE'],
                $admobItem['free'],
                $admobModel::ATTENDANCE['COMMENT'],
                $admobModel::ATTENDANCE['TABLE'],
                $lastId
            );

            $admobModel->walletDb->commit();
        } catch (Exception $e) {
            $admobModel->walletDb->rollBack();
            throw new ApiException($e);
        }

        $userEtc = $admobModel->getUserEtc($this->userID);
        static::responses([
            'paid_point' => $userEtc['ue_paid_point'],
            'free_point' => $userEtc['ue_free_point']
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     * @desc 구글 인앱 결제
     */
    public function purchase()
    {
        $this->isAuth();
        $params = $this->getParsedBody();
        $this->validator->validate($params, [
            'product_id' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_product_id.'
            ],
            'purchase_token' => [
                'rules' => V::notEmpty(),
                'message' => 'invalid_purchase_token'
            ]
        ])->isValid();
        $purchaseToken = $params['purchase_token'];
        $productId = $params['product_id'];

        $log = new Log();
        try {
            $log->instance->info(json_encode($params));
        } catch (Exception $e) {
            $log->instance->error($e->getMessage());
        }

        $google = new GoogleService();
        $purchase = $google->getPurchase($productId, $purchaseToken);
        $purchaseModel = new PurchaseModel();

        try {
            //TODO DB처리 시작
            $purchaseModel->walletDb->beginTransaction();

            //TODO 유저 ETC 테일블 조회 후 포인트 데이터 밀어넣기
            $userEtcInfo = $purchaseModel->getUserEtc($this->userID);
            if (empty($userEtcInfo) === true) {
                $rowCount = $purchaseModel->addUserEtc($this->userID);
            }

            //TODO 이미 처리 된 주문 내역입니다.
            $info = $purchaseModel->getPurchase($this->userID, $purchase['order_id']);
            if (empty($info) === false) {
                throw new BadRequestException('order_already_complete');
            }

            //TODO 유저 포인트 로그
            // purchase_type ( null:상용 , 0:테스트, 프로모션:1, 2:광고시청 보상 )
            if ($purchase['purchase_type'] == 0 || empty($purchase['purchase_type']) === true) {
                $lastId = $purchaseModel->addPurchase
                (
                    $this->userID,
                    self::APP_TYPE,
                    PURCHASE['PACKAGE_NAME'],
                    $purchase['order_id'],
                    $productId,
                    $purchase['purchase_time_millis'],
                    $purchase['purchase_state'],
                    $purchaseToken,
                    $purchase['purchased_price'],
                    $purchase['purchase_type'],
                    $purchase['price']
                );

                for ($quantity = 1; $quantity <= $purchase['quantity']; $quantity++) {
                    $balance = $purchaseModel->addUserFreePointLog(
                        $this->userID,
                        $purchaseModel::PURCHASE['CODE'],
                        $purchase['free'],
                        $purchase['title'],
                        $purchaseModel::PURCHASE['TABLE'],
                        $lastId,
                        $quantity
                    );

                    $balance = $purchaseModel->addUserPaidPointLog(
                        $this->userID,
                        $purchaseModel::PURCHASE['CODE'],
                        $purchase['paid'],
                        $purchase['title'],
                        $purchaseModel::PURCHASE['TABLE'],
                        $lastId,
                        $quantity
                    );
                }
            }

            $purchaseModel->walletDb->commit();
        } catch (Exception $e) {
            $purchaseModel->walletDb->rollBack();
            throw new InvalidArgumentException($e->getMessage());
        }

        $userEtc = $purchaseModel->getUserEtc($this->userID);
        static::responses([
            'paid_point' => $userEtc['ue_paid_point'],
            'free_point' => $userEtc['ue_free_point'],
            'purchase_type' => $purchase['purchase_type']
        ]);
    }

    public function purchaseItem()
    {
        $items = \App\Service\ItemService::PURCHASE;
        static::responses($items);
    }
}

/* End of file GoogleController.php */
/* Location: /App/Controller/GoogleController.php */
