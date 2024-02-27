<?php
/**
 * @author sojo
 * @since 0.1 2022-07-21
 * Class PurchaseModel
 */

namespace App\Models;

use Exception;

class PurchaseModel extends BaseModel
{
    const PURCHASE = [
        'CODE' => "PURCHASE",
        'TABLE' => "tb_user_purchase_log",
        'COMMENT' => "인앱 결제",
        'PRE' => 'pc_'
    ];

    /**
     * CashModel constructor.
     * @param int
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    /**
     * @param $userId
     * @param $orderId
     * @throws \Doctrine\DBAL\Exception
     * @Desc 인앱 결제
     */
    public function getPurchase($userId, $orderId)
    {
        return $this->walletDb->createQueryBuilder()
            ->select('*')
            ->from('tb_user_purchase_log', 'pc')
            ->where('user_id = :user_id AND order_id = :order_id FOR UPDATE')
            ->setParameter('user_id', $userId)
            ->setParameter('order_id', $orderId)
            ->fetchAssociative();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPurchaseCount($bind)
    {
        return $this->walletDb->createQueryBuilder()
            ->select('count(0) as cnt')
            ->from('tb_user_purchase_log', 'pc')
            ->where('user_id = :user_id')
            ->andWhere('created >= :s_date')
            ->andWhere('created <= :e_date')
            ->setParameter('user_id', $bind['user_id'])
            ->setParameter('s_date', $bind['s_date'])
            ->setParameter('e_date', $bind['e_date'])
            ->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPurchaseList($bind)
    {
        $fields = [
            'order_id',
            'product_id',
            'app_type',
            'purchase_date',
            'purchase_state',
            'amount',
            'purchase_price',
            'purchase_type'
        ];

        return $this->walletDb->createQueryBuilder()
            ->select($fields)
            ->from('tb_user_purchase_log', 'pc')
            ->where('user_id = :user_id')
            ->andWhere('created >= :s_date')
            ->andWhere('created <= :e_date')
            ->orderBy('created','DESC')
            ->setParameter('user_id', $bind['user_id'])
            ->setParameter('s_date', $bind['s_date'])
            ->setParameter('e_date', $bind['e_date'])
            ->setFirstResult($bind['start'])
            ->setMaxResults($bind['end'])
            ->fetchAllAssociative();
    }

    /**
     * @param $userId
     * @param $appType
     * @param $packageName
     * @param $orderId
     * @param $productId
     * @param $purchaseDate
     * @param $purchaseState
     * @param $message
     * @param $token
     * @param $code
     * @throws \Doctrine\DBAL\Exception
     * @Desc 인앱 결제
     */
    public function addPurchase
    (
        $userId,
        $appType,
        $packageName,
        $orderId,
        $productId,
        $purchaseDate,
        $purchaseState,
        $token,
        $purchasedPrice,
        $purchaseType,
        $price
    )
    {
        $this->walletDb->createQueryBuilder()
            ->update('tb_user_etc')
            ->set('ue_purchase_krw', ':ue_purchase_krw')
            ->set('ue_purchase_usd', ':ue_purchase_usd')
            ->where('ue_user_id = :ue_user_id')
            ->setParameter('ue_purchase_krw', $price)
            ->setParameter('ue_purchase_usd', $purchasedPrice)
            ->setParameter('ue_user_id', $userId)
            ->executeQuery();

        $this->walletDb->createQueryBuilder()
            ->insert('tb_user_purchase_log')
            ->setValue('user_id', ':user_id')
            ->setValue('app_type', ':app_type')
            ->setValue('package_name', ':package_name')
            ->setValue('order_id', ':order_id')
            ->setValue('product_id', ':product_id')
            ->setValue('purchase_date', ':purchase_date')
            ->setValue('purchase_state', ':purchase_state')
            ->setValue('purchase_price', ':purchase_price')
            ->setValue('purchase_type', ':purchase_type')
            ->setValue('token', ':token')
            ->setValue('price', ':price')
            ->setValue('created', 'unix_timestamp()')
            ->setParameter('user_id', $userId)
            ->setParameter('app_type', $appType)
            ->setParameter('package_name', $packageName)
            ->setParameter('order_id', $orderId)
            ->setParameter('product_id', $productId)
            ->setParameter('purchase_date', $purchaseDate)
            ->setParameter('purchase_state', $purchaseState)
            ->setParameter('purchase_price', $purchasedPrice)
            ->setParameter('purchase_type', $purchaseType)
            ->setParameter('token', $token)
            ->setParameter('price', $price)
            ->execute();

        return $this->walletDb->lastInsertId();
    }
}

