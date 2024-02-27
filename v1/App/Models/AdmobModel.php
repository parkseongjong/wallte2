<?php
/**
 * @author sojo
 * @since 0.1 2016-11-19
 * Class AttendanceModel
 */
namespace App\Models;

class AdmobModel extends BaseModel
{
    const ATTENDANCE = [
        'CODE' => "ADMOB",
        'TABLE' => "tb_user_admob_log",
        'COMMENT' => '구글 광고 보상'
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
     * 출석체크 설정
     * @param int userID
     * @return mixed
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAttend($userId, $transactionId)
    {
        return $this->walletDb->createQueryBuilder()
            ->select('*')
            ->from('tb_user_admob_log')
            ->where('ad_user_id = :user_id ')
            ->andWhere('transaction_id = :transaction_id')
            ->setParameter('user_id', $userId)
            ->setParameter('transaction_id', $transactionId)
            ->fetchAssociative();
    }

    /**
     * 출석체크 설정
     * @param int userID
     * @return mixed
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAttendInfo($userId)
    {
        $sDate = strtotime(TIME_YMD . ' 00:00:00');
        $eDate = strtotime(TIME_YMD . ' 23:59:59');

        return $this->walletDb->createQueryBuilder()
            ->select('*,(SELECT count(0) FROM tb_user_admob_log WHERE ad_user_id = a.ad_user_id) as cnt')
            ->from('tb_user_admob_log', 'a')
            ->where('ad_user_id = :user_id AND created >= :s_date AND created <= :e_date')
            ->setParameter('user_id', $userId)
            ->setParameter('s_date', $sDate)
            ->setParameter('e_date', $eDate)
            ->orderBy('created','DESC')
            ->fetchAssociative();
    }

    /**
     * @param $userId
     * @param $data
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    public function addAttendance($userId, $data): string
    {
        $data = [
            'ad_user_id' => $userId,
            'ad_network' => $data['ad_network'],
            'ad_unit' => $data['ad_unit'],
            'custom_data' => $data['custom_data'],
            'key_id' => $data['key_id'],
            'reward_amount' => $data['reward_amount'],
            'reward_item' => $data['reward_item'],
            'signature' => $data['signature'],
            'timestamp' => $data['timestamp'],
            'transaction_id' => $data['transaction_id']
        ];
        $this->builder = $this->walletDb->createQueryBuilder();
        $this->builder->insert('tb_user_admob_log');
        $this->dataBind($data);
        $this->builder->setValue('created', 'unix_timestamp()');
        $this->builder->executeQuery();

        return $this->walletDb->lastInsertId();
    }
}


/* End of file AttendanceModel.php */
/* Location: /App/Models/AttendanceModel.php */
