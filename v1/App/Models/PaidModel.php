<?php
/**
 * @author sojo
 * @since 0.1 2016-11-19
 * Class CashModel
 */

namespace App\Models;

use Exception;

class PaidModel extends BaseModel
{
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
    public function getPaidCount($bind)
    {
        return $this->walletDb->createQueryBuilder()
            ->select('count(0) as cnt')
            ->from('tb_user_paid_point_log', 'cl')
            ->where('pp_user_id = :user_id')
            ->andWhere('pp_created >= :s_date')
            ->andWhere('pp_created <= :e_date')
            ->setParameter('user_id', $bind['user_id'])
            ->setParameter('s_date', $bind['s_date'])
            ->setParameter('e_date', $bind['e_date'])
            ->fetchOne();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPaidList($bind)
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
        $fields = static::addPrefixFields($fields, static::PREFIX['PAID']);

        return $this->walletDb->createQueryBuilder()
            ->select($fields)
            ->from('tb_user_paid_point_log', 'cl')
            ->where('pp_user_id = :user_id')
            ->andHaving('pp_created >= :s_date')
            ->andHaving('pp_created <= :e_date')
            ->orderBy('pp_created','DESC')
            ->setParameter('user_id', $bind['user_id'])
            ->setParameter('s_date', $bind['s_date'])
            ->setParameter('e_date', $bind['e_date'])
            ->setFirstResult($bind['start'])
            ->setMaxResults($bind['end'])
            ->fetchAllAssociative();
    }
}

/* End of file CashModel.php */
/* Location: /App/Models/CashModel.php */


