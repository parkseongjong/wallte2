<?php
/**
 * @author sojo
 * @since 0.1 2016-11-19
 * Class AttendanceModel
 */
namespace control\Models;

use Exception;
use wallet\ctcDbDriver\Driver as walletDb;

class AttendanceModel
{
    /**
     * @var int
     */
    public $amount;
    public $walletDb;

    /**
     * CashModel constructor.
     * @param int
     */
    public function __construct($db = null)
    {
        if (empty($db) === true) {
            $this->walletDb = walletDb::singletonMethod()->init();
        } else {
            $this->walletDb = $db;
        }
    }

    /**
     * 출석체크 설정
     * @param int userID
     * @return mixed
     * @throws Exception
     */
    public function getAttendCount($userId)
    {
        $sDate = TIME_YMD . ' 00:00:00';
        $eDate = TIME_YMD . ' 23:59:59';
        $bind = [
            ':user_id' => $userId,
            ':s_date' => $sDate,
            ':e_date' => $eDate
        ];
        $sql = "
                SELECT 
                    count(0) 
                FROM 
                    tb_user_attendance 
                WHERE 
                    ad_user_id = :user_id 
                    AND reg_dt >= :s_date and  reg_dt <= :e_date
                FOR UPDATE 
            ";
        $stmt = $this->walletDb->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchColumn();
    }

    /**
     * @param $type
     * @param $userId
     * @param $amount
     * @param $comment
     * @param $relTable
     * @param $relKey
     * @return string
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function addAttendance($userId, $data)
    {
        $sql = "
            INSERT INTO
                tb_user_attendance
            SET 
                ad_user_id = :ad_user_id,
                ad_network = :ad_network,
                ad_unit = :ad_unit,
                custom_data = :custom_data,
                key_id = :key_id,
                reward_amount = :reward_amount,
                reward_item = :reward_item,
                signature = :signature,
                timestamp = :timestamp,
                transaction_id = :transaction_id,
                reg_dt = NOW()
        ";
        $stmt = $this->walletDb->prepare($sql);
        $stmt->execute([
            ':ad_user_id' => $userId,
            ':ad_network' => $data['ad_network'],
            ':ad_unit' => $data['ad_unit	'],
            ':custom_data' => $data['custom_data'],
            ':key_id' => $data['key_id'],
            ':reward_amount' => $data['reward_amount'],
            ':reward_item' => $data['reward_item'],
            ':signature' => $data['signature'],
            ':timestamp' => $data['timestamp'],
            ':transaction_id' => $data['transaction_id']
        ]);

        return $this->walletDb->lastInsertId();
    }


}

