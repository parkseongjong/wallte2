<?php

namespace App\Service;

use Exception;
use App\Models\BaseModel;

/**
 * Class ExchangeService
 *
 * @package App\Service
 * @author seungo.jo
 * @since 2018-02-27
 */
class ExchangeService
{
    public $model;
    private $userID;
    public $builder;

    const CODE = 'EXCHANGE';
    const TABLE = 'user_transactions';


    public function __construct($db = null)
    {
        $this->model = new BaseModel($db);
    }

    public function getUserId()
    {
        return $this->userID;
    }

    public function setUserId($userID)
    {
        $this->userID = $userID;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function exchangePoint($transactionId, $amount = 1)
    {
        if (empty($this->userID) === true) {
            throw new Exception('bad_request_autologin');
        }

        $default = 10000;
        $min = 5;
        $max = 50;
        $comment = '출금';
        $fee = ceil(($amount / $default) / $min);
        $type = 'M';
        if ($fee > $max) {
            throw new Exception('over_the_limit');
        }

        try {
            $this->model->walletDb->beginTransaction();
            $this->builder = $this->model->walletDb->createQueryBuilder();
            $userEtc = $this->builder
                ->select('ue_user_id as user_id, ue_free_point as free, ue_paid_point as paid')
                ->from('tb_user_etc')
                ->where('ue_user_id = :ue_user_id FOR UPDATE')
                ->setParameter('ue_user_id', $this->userID)
                ->fetchAssociative();

            //TODO 유료와 무료 포인트 합이 수수료 보다 작을경우
            if ($userEtc['free'] + $userEtc['paid'] < $fee) {
                throw new Exception('paid_is_not_enough');
            }

            //TODO 유료 차감 로그
            $paidBalance = $userEtc['paid'] - $fee;
            $freeBalance = $userEtc['free'];
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = md5($_SERVER['HTTP_USER_AGENT']);
            $data = [
                'user_id' => $this->userID,
                'code' => self::CODE,
                'type' => $type,
                'amount' => $fee,
                'balance' => $paidBalance,
                'comment' => $comment,
                'rel_table' => self::TABLE,
                'rel_key' => $transactionId,
                'ip' => $ip,
                'user_agent' => $userAgent
            ];

            $data = $this->model::addPrefix($data, $this->model::PREFIX['PAID']);
            $this->builder = $this->model->walletDb->createQueryBuilder();
            $this->builder->insert('tb_user_paid_point_log');
            $this->builder->setValue('pp_created', 'unix_timestamp()');
            $this->dataBind($data);
            $this->builder->executeQuery();

            //TODO 무료 차감 로그
            if ($paidBalance <= 0) {
                $freeBalance = $userEtc['free'] - $paidBalance;

                $data = [
                    'user_id' => $this->userID,
                    'code' => self::CODE,
                    'type' => $type,
                    'amount' => $amount,
                    'balance' => $freeBalance,
                    'comment' => $comment,
                    'rel_table' => self::TABLE,
                    'rel_key' => $transactionId,
                    'ip' => $ip,
                    'user_agent' => $userAgent
                ];

                $data = $this->model::addPrefix($data, $this->model::PREFIX['FREE']);
                $this->builder = $this->model->walletDb->createQueryBuilder();
                $this->builder->insert('tb_user_free_point_log');
                $this->builder->setValue('fp_created', 'unix_timestamp()');
                $this->dataBind($data);
                $this->builder->executeQuery();
            }

            $this->builder = $this->model->walletDb->createQueryBuilder();
            $rowCount = $this->builder
                ->update('tb_user_etc')
                ->set('ue_paid_point', ':ue_paid_point')
                ->set('ue_free_point', ':ue_free_point')
                ->where('ue_user_id = :ue_user_id')
                ->setParameter('ue_paid_point', $paidBalance)
                ->setParameter('ue_free_point', $freeBalance)
                ->setParameter('ue_user_id', $this->userID)
                ->executeQuery();

            $this->model->walletDb->commit();
        } catch (Exception $e) {
            $this->model->walletDb->rollBack();
            throw new Exception($e);
        }

        return [
            'paid_point' => $paidBalance,
            'free_point' => $freeBalance
        ];
    }

    public function dataBind($data)
    {
        $setValue = [];
        foreach ($data as $key => $value) {
            $bindKey = ':' . $key;
            $setValue[$key] = $bindKey;
            $this->builder->setParameter($key, $value);
        }

        foreach ($setValue as $key => $value) {
            $this->builder->setValue($key, $value);
        }
    }

}