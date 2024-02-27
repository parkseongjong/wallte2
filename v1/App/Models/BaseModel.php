<?php

namespace App\Models;

use App\Library\Driver as walletDb;
use Exception;

/**
 * Class BaseModel
 *
 * @package App\Models
 * @author jso
 */
class BaseModel
{
    /**
     * */
    public $walletDb;

    /**
     * */
    public $builder;

    const PREFIX = [
        'USER' => 'ue_',
        'FREE' => 'fp_',
        'PAID' => 'pp_',
        'PURCHASE' => 'pc_',
    ];

    const USER_ETC = [
        'user_id',
        'paid_point',
        'free_point'
    ];

    /**
     * CashModel constructor.
     * @param int
     */
    public function __construct($db = null)
    {
        if (empty($db) === true) {
            $this->walletDb = walletDb::init();
        } else {
            $this->walletDb = $db;
        }
    }

    /**
     * 캐시 정보를 가져오거나, 캐시 드라이브(Redis) Class 객체를 가져온다
     * @param string|null $key
     * @return mixed|\Predis\Client|string
     */
    public static function cache(string $key = NULL)
    {
        static $client;
        (isset($client) !== TRUE || !($client instanceof \Predis\Client)) && ($client = walletDb::redis(3));

        if (empty($key) === TRUE) {
            return $client;
        } else if (empty($data = $client->get($key)) === TRUE) {
            return NULL;
        }

        $jsonArray = json_decode($data, TRUE);
        return json_last_error() === JSON_ERROR_NONE ? $jsonArray : $data;
    }

    /**
     * 데이터를 캐시로 저장
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return mixed
     */
    public function saveCache(string $key, $value, $ttl = 300)
    {
        $client = self::cache();
        $stringify = json_encode($value);
        $result = $client->set($key, $stringify);
        $ttl > 0 && $client->expire($key, $ttl);
        return $result;
    }

    /**
     *
     * @param int userID
     * @return mixed
     * @throws Exception
     * @desc 유저 etc 조회
     */
    public function getUserEtc($userId)
    {
        return $this->walletDb->createQueryBuilder()
            ->select(static::addPrefixFields(static::USER_ETC, static::PREFIX['USER']))
            ->from('tb_user_etc')
            ->where('ue_user_id = :ue_user_id')
            ->setParameter('ue_user_id', $userId)
            ->fetchAssociative();
    }

    /**
     * @param $userId
     * @description 초기 유저 포인트 셋팅
     * @throws \Doctrine\DBAL\Exception
     */
    public function addUserEtc($userId)
    {
        return $this->walletDb->createQueryBuilder()
            ->insert('tb_user_etc')
            ->setValue('ue_user_id', ':user_id')
            ->setValue('ue_created', 'unix_timestamp()')
            ->setParameter('user_id', $userId)
            ->executeQuery();
    }

    /**
     * @param string $type
     * @param $userId
     * @param $amount
     * @param $comment
     * @param $relTable
     * @param $relKey
     * @return string
     * @throws Exception
     * @description 포인트 로그
     */
    public function addUserFreePointLog
    (
        $userId,
        $code,
        $amount,
        $comment,
        $relTable,
        $relKey,
        $quantity = 1,
        string $type = 'P'
    ): string
    {
        if ($amount < 0) {
            $type = 'M';
        }

        $this->builder = $this->walletDb->createQueryBuilder();
        $userEtc = $this->builder
            ->select('ue_user_id as user_id, ue_free_point as free')
            ->from('tb_user_etc')
            ->where('ue_user_id = :ue_user_id FOR UPDATE')
            ->setParameter('ue_user_id', $userId)
            ->fetchAssociative();

        if (empty($userEtc) === true) {
            throw new Exception('no_members');
        }

        $balance = $userEtc['free'] + $amount;

        if ($balance < 0) {
            throw new Exception('point_is_not_enough');
        }

        $rowCount = $this->builder
            ->update('tb_user_etc')
            ->set('ue_free_point', ':ue_free_point')
            ->where('ue_user_id = :ue_user_id')
            ->setParameter('ue_free_point', $balance)
            ->setParameter('ue_user_id', $userId)
            ->executeQuery();

        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = md5($_SERVER['HTTP_USER_AGENT']);
        $data = [
            'user_id' => $userId,
            'code' => $code,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance,
            'comment' => $comment,
            'rel_table' => $relTable,
            'rel_key' => $relKey,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'quantity' => $quantity
        ];

        $data = static::addPrefix($data, static::PREFIX['FREE']);

        $this->builder = $this->walletDb->createQueryBuilder();
        $this->builder->insert('tb_user_free_point_log');
        $this->builder->setValue('fp_created', 'unix_timestamp()');
        $this->dataBind($data);
        $this->builder->executeQuery();

        return $balance;
    }


    /**
     * @param string $type
     * @param $userId
     * @param $amount
     * @param $comment
     * @param $relTable
     * @param $relKey
     * @return string
     * @throws Exception
     * @description 캐시 로그
     */
    public function addUserPaidPointLog
    (
        $userId,
        $code,
        $amount,
        $comment,
        $relTable,
        $relKey,
        $quantity,
        string $type = 'P'
    ): string
    {
        if ($amount < 0) {
            $type = 'M';
        }

        $this->builder = $this->walletDb->createQueryBuilder();
        $userEtc = $this->builder
            ->select('ue_user_id as user_id, ue_paid_point as paid')
            ->from('tb_user_etc')
            ->where('ue_user_id = :ue_user_id FOR UPDATE')
            ->setParameter('ue_user_id', $userId)
            ->fetchAssociative();

        if (empty($userEtc) === true) {
            throw new Exception('no_members');
        }

        $balance = $userEtc['paid'] + $amount;

        if ($balance < 0) {
            throw new Exception('paid_is_not_enough');
        }

        $rowCount = $this->builder
            ->update('tb_user_etc')
            ->set('ue_paid_point', ':ue_paid_point')
            ->where('ue_user_id = :ue_user_id')
            ->setParameter('ue_paid_point', $balance)
            ->setParameter('ue_user_id', $userId)
            ->executeQuery();

        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = md5($_SERVER['HTTP_USER_AGENT']);
        $data = [
            'user_id' => $userId,
            'code' => $code,
            'type' => $type,
            'amount' => $amount,
            'balance' => $balance,
            'comment' => $comment,
            'rel_table' => $relTable,
            'rel_key' => $relKey,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'quantity' => $quantity
        ];

        $data = static::addPrefix($data, static::PREFIX['PAID']);

        $this->builder = $this->walletDb->createQueryBuilder();
        $this->builder->insert('tb_user_paid_point_log');
        $this->builder->setValue('pp_created', 'unix_timestamp()');
        $this->dataBind($data);
        $this->builder->executeQuery();

        return $balance;
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

    public static function addPrefixFields($fields, $prefix)
    {
        return preg_filter('/^/', $prefix, $fields);
    }

    public static function removePrefixFields($fields, $prefix)
    {
        return preg_filter("/^$prefix/", '', $fields);
    }

    public static function removePrefix($array, $prefix)
    {
        return array_combine(
            array_map(
                function ($k) use ($prefix) {
                    return preg_filter("/^$prefix/", '', $k);;
                },
                array_keys($array)
            ),
            array_values($array)
        );
    }
    public static function addPrefix($array, $prefix)
    {
        return array_combine(
            array_map(
                function ($k) use ($prefix) {
                    return $prefix . $k;
                },
                array_keys($array)
            ),
            array_values($array)
        );
    }
}

/* End of file BaseModel.php */
/* Location: /App/Models/BaseModel.php */
