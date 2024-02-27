<?php
/**
 * @author sojo
 * @since 0.1 2016-11-19
 * Class PointModel
 */
namespace control\Models;

use Exception;
use wallet\ctcDbDriver\Driver as walletDb;

class PointModel
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
     * @param $type
     * @param $userId
     * @param $amount
     * @param $comment
     * @param $relTable
     * @param $relKey
     * @param $type
     * @return string
     * @throws Exception
     * @description 포인트 로그
     */
    public function addUserPointLog
    (
        $userId,
        $code,
        $amount,
        $comment,
        $relTable,
        $relKey,
        $type = 'P'
    )
    {
        if ($amount < 0) {
            $type = 'M';
        }

        $sql = "
            SELECT 
                ue_user_id as user_id, 
                ue_point as point, 
                ue_admob_count as count 
            FROM
                tb_user_etc 
            WHERE 
                ue_user_id = :user_id
            FOR UPDATE
        ";
        $stmt = $this->walletDb->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $userEtc = $stmt->fetch();

        if (empty($userEtc) === true) {
            echo '존재하지 않는 회원입니다.';
            die;

        }

        $balance = $userEtc['point'] + $amount;

        if ($balance < 0) {
            echo '포인트가 모자랍니다.';
            die;
        }

        $sql = "
            UPDATE tb_user_etc SET 
                ue_point = :balance,
                ue_admob_count = ue_admob_count + 1
            WHERE 
                ue_user_id = :user_id
        ";
        $stmt = $this->walletDb->prepare($sql);
        $stmt->bindValue(':balance', $balance);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = md5($_SERVER['HTTP_USER_AGENT']);
        $sql = "
            INSERT INTO
                tb_user_point_log
            SET
               pl_user_id = :user_id,
               pl_code = :code,
               pl_type = :type,
               pl_amount = :amount,
               pl_balance = :balance,
               pl_comment = :comment,
               pl_rel_table = :rel_table,
               pl_rel_key = :rel_key,
               pl_ip = :ip,
               pl_user_agent = :user_agent,
               pl_reg_dt = now()
        ";
        $stmt = $this->walletDb->prepare($sql);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':code', $code);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':balance', $balance);
        $stmt->bindParam(':comment', $comment);
        $stmt->bindParam(':rel_table', $relTable);
        $stmt->bindParam(':rel_key', $relKey);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();

        return $this->walletDb->lastInsertId();
    }

    /**
     * @param $userId
     * @return void
     * @description 초기 유저 포인트 셋팅
     */
    public function setUserEtc($userId) {
        $sql = "
            REPLACE INTO tb_user_etc SET 
                ue_user_id = :ue_user_id,
                ue_point = 0,
                ue_admob_count = 0
        ";
        $stmt = $this->walletDb->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
    }
}


