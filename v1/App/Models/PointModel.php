<?php
/**
 * @author sojo
 * @since 0.1 2016-11-19
 * Class PointModel
 */

namespace App\Models;

use Exception;

class PointModel extends BaseModel
{
    const TABLE = 'tb_user_free_point_log';

    /**
     * CashModel constructor.
     * @param int
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPointCount($bind)
    {
        return $this->walletDb->createQueryBuilder()
            ->select('count(0) as cnt')
            ->from(static::TABLE, 'pl')
            ->where('fp_user_id = :fp_user_id')
            ->andWhere('fp_created >= :s_date')
            ->andWhere('fp_created <= :e_date')
            ->setParameter('fp_user_id', $bind['user_id'])
            ->setParameter('s_date', $bind['s_date'])
            ->setParameter('e_date', $bind['e_date'])
            ->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPointList($bind): array
    {
        $fields = [
            'code',
            'quantity',
            'amount',
            'balance',
            'comment',
            'created',
            'type'
        ];
        $fields = static::addPrefixFields($fields, static::PREFIX['FREE']);

        return $this->walletDb->createQueryBuilder()
            ->select($fields)
            ->from(static::TABLE, 'pl')
            ->where('fp_user_id = :fp_user_id')
            ->andWhere('fp_created >= :s_date')
            ->andWhere('fp_created <= :e_date')
            ->orderBy('fp_created','DESC')
            ->setParameter('fp_user_id', $bind['user_id'])
            ->setParameter('s_date', $bind['s_date'])
            ->setParameter('e_date', $bind['e_date'])
            ->setFirstResult($bind['start'])
            ->setMaxResults($bind['end'])
            ->fetchAllAssociative();
    }
}

/* End of file PointModel.php */
/* Location: /App\Models/PointModel.php */


