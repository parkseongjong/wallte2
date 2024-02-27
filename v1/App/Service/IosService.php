<?php

namespace App\Service;

use App\Exception\InvalidArgumentException;
use App\Exception\BadRequestException;
use App\Exception\ApiException;
use Exception;
use Firebase\JWT\JWT;

/**
 * Class GoogleService
 *
 * @package App\Service
 * @author seungo.jo
 * @since 2018-02-27
 */
class IosService
{
    const VERIFY_URL = "https://sandbox.itunes.apple.com/verifyReceipt";
    #const VERIFY_URL =  "https://buy.itunes.apple.com/verifyReceipt";

    /**
     */
    public function __construct()
    {

    }

    /**
     * @throws BadRequestException
     */
    function getPurchase($store, $transactionID, $payload)
    {
//        [
//            "Store"=>"AppleAppStore",
//            "TransactionID"=>"1000000629303951",
//            "Payload"=>"MIIT+QYJKoZIhvBFja19....BkpGkqEZQbQ8l8fNdoKPFRDc="
//        ]
        $params = json_encode([
            "receipt-data" => [
                "Store" => $store,
                "TransactionID" => $transactionID,
                "Payload" => $payload
            ]
        ]);
        $curl = curl_init(self::VERIFY_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

        $response = curl_exec($curl);
        $errno = curl_errno($curl);
        $errmsg = curl_error($curl);
        curl_close($curl);
        $result = (object)json_decode($response);

        if ($result->status != 0) {
            throw new BadRequestException("invalid_recipt");
        }

        if ($result->receipt->bundle_id != "package name goes here") {
            throw new BadRequestException("invalid_pkg_name");
        }

        if ($result->receipt->in_app{0}->product_id != "hp_potion") {
            throw new BadRequestException("invalid_pid");
        }

        return [
            "order_id" => $result->receipt->in_app[0]->transaction_id,
            "product_id" => $result->receipt->in_app[0]->product_id,
            "purchase_date" => $result->receipt->in_app[0]->purchase_date
        ];
    }
}
