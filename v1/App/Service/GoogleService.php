<?php

namespace App\Service;

use App\Exception\InvalidArgumentException;
use App\Exception\BadRequestException;
use App\Exception\ApiException;
use Exception;
use Firebase\JWT\JWT;
use Google;

/**
 * Class GoogleService
 *
 * @package App\Service
 * @author seungo.jo
 * @since 2018-02-27
 */
class GoogleService
{
    private $client;
    private $service;

    /**
     */
    public function __construct()
    {
        $this->client = new Google\Client();
        $this->client->setAuthConfig(GOOGLE['credentials']);
        $this->client->addScope('https://www.googleapis.com/auth/androidpublisher');
        $this->service = new Google\Service\AndroidPublisher($this->client);
    }

    /**
     * @param $productId
     * @param $purchaseToken
     * @return array
     * @throws InvalidArgumentException
     * @throws BadRequestException
     * @desc 구글 상품 조회
     */
    public function getPurchase($productId, $purchaseToken): array
    {
//        $productItem = \App\Service\ItemService::PURCHASE[$productId];
//        if (empty($productItem) === true) {
//            throw new BadRequestException('no_product');
//        }
//        $purchase['order_id'] = 'GPA.3331-7513-9788-46011';
//        $purchase['purchase_time_millis'] = 1633449519729;
//        $purchase['purchase_state'] = '0';
//        $purchase['purchase_type'] = '';
//        $purchase['purchased_price'] = '6.86';
//        $purchase['quantity'] = 2;
//        $purchase['price'] = $productItem['price'];
//        $purchase['paid'] = $productItem['paid'];
//        $purchase['free'] = $productItem['free'];
//        $purchase['title'] = '패키지1 구매. 1000 Cash, 10 Free';
//        return $purchase;

        $productItem = \App\Service\ItemService::PURCHASE[$productId];
        if (empty($productItem) === true) {
            throw new BadRequestException('no_product');
        }

        $price = 0;
        $purchasedPrice = 0;
        $str = '';
        try {
            //TODO 판매 상품인지 확인
            $list = $this->service->inappproducts->listInappproducts(PURCHASE['PACKAGE_NAME']);
            foreach ($list as $key => $value) {
                if ($value['sku'] == $productId) {
                    $str = $value['listings']['en-US']['title'];
                    $purchasedPrice = $value['defaultPrice']['priceMicros'];
                    $price = preg_replace("/[^0-9]*/s", "", $str);
                    break;
                }
            }

            if (empty($purchasedPrice) === true) {
                throw new BadRequestException('developer_console_error');
            }

            //TODO 결제 조회
            $purchase = $this->service->purchases_products->get(
                PURCHASE['PACKAGE_NAME'],
                $productId,
                $purchaseToken
            );

            //TODO 구매 상품 확인
            $data['kind'] = $purchase->getKind();
            $data['purchase_time_millis'] = $purchase->getPurchaseTimeMillis();//구매한 시간 string (int64 format)
            $data['purchase_state'] = $purchase->getPurchaseState();//0. 구매함 1. 취소됨 2. 보류 중
            $data['consumption_state'] = $purchase->getConsumptionState();//0. 아직 소비되지 않음 1. 소비됨
            $data['developer_payload'] = $purchase->getDeveloperPayload();
            $data['order_id'] = $purchase->getOrderId();//주문 ID
            $data['purchase_type'] = $purchase->getPurchaseType();
            $data['acknowledgement_state'] = $purchase->getAcknowledgementState();
            $data['quantity'] = $purchase->getQuantity();//구매 수량 (기본 1개)
            $data['purchased_price'] = $purchasedPrice / 1000000;
            $data['paid'] = $productItem['paid'];
            $data['free'] = $productItem['free'];
            $data['price'] = $productItem['price'];;
            $data['title'] = $str;

            //TODO 취소 된 주문 내역입니다.
            if ($data['purchase_state'] == '1') {
                throw new BadRequestException('cancelled_order');
            }

            return $data;

        } catch (Exception $e) {
            throw new InvalidArgumentException('Invalid_token_product_id');
        }
    }

    /**
     * @param $params
     * @return bool
     * @throws InvalidArgumentException
     */
    private function SignatureTest($params): bool
    {
        //Get current admob public keys
        $Url = 'https://www.gstatic.com/admob/reward/verifier-keys.json';
        $verifier_keys = file_get_contents($Url);

        if (!$verifier_keys_arr = json_decode($verifier_keys, true)) {
            throw new InvalidArgumentException("Invalid Public Keys");
        } elseif (!is_array($verifier_keys_arr)) {
            throw new InvalidArgumentException("Empty Public Keys");
        }

        $public_key_pem = $verifier_keys_arr['keys'][0]['pem'];
        $query_string = $_SERVER['QUERY_STRING'];

        $signature = trim($params['signature']);
        // The important thing is the replacement and padding of the signature result string here
        $signature = str_replace(['-', '_'], ['+', '/'], $signature);
        $signature .= '===';

        // The data element string for signing
        $message = substr($query_string, 0, strpos($query_string, 'signature') - 1);
        if (openssl_verify($message, $signature, $public_key_pem, OPENSSL_ALGO_SHA256)) {
            $output = "verified";
            //Get All keys from https://developers.google.com/admob/android/ssv#ssv_callback_parameters
            $reward_item = $params['reward_item']; //CODE : POINT
            $user_id = $params['user_id'];         //CODE : 1
            $custom_data = $params['custom_data']; //TYPE : ATTEND

            //for example
            if ($custom_data === "GIVE_COINS") {
                debug('GIVE_COINS');
            } elseif ($custom_data === "LEVEL_UP") {
                debug('LEVEL_UP');
            }
        } else {
            throw new InvalidArgumentException("Unable to verify the query");
        }
    }
}
