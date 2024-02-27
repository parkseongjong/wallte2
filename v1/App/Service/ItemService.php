<?php
namespace App\Service;

/**
 * Class ItemService
 *
 * @package App\Service
 * @author seungo.jo
 * @since 2022-08-18
 */
class ItemService
{
    //5당 1000
    //패키지 1~2  + 수수료 1000원
    const PURCHASE = [
        'PACKAGE_001' => [
            'price' => 12000,
            'paid' => 100,
            'free' => 10
        ],
        'PACKAGE_002' => [
            'price' => 31000,
            'paid' => 250,
            'free' => 50
        ],
        'PACKAGE_003' => [
            'price' => 59000,
            'paid' => 500,
            'free' => 100
        ],
        'PACKAGE_004' => [
            'price' => 89000,
            'paid' => 700,
            'free' => 200
        ],
        'PACKAGE_005' => [
            'price' => 119000,
            'paid' => 1000,
            'free' => 300
        ]
    ];

    const ADMOB = [
        'ADMOB_FRONT_001' => [
            'type' => 'FRONT',
            'paid' => 0,
            'free' => 1
        ],
    ];

    /**
     */
    public function __construct()
    {
    }

    /**
     */
    public function getItem($code)
    {
        return $code ? static::PURCHASE[$code] : static::PURCHASE;
    }

}
