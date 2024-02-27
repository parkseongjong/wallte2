<?php

namespace wallet\sleep;

use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Util as walletUtil;
use \exception;

class Restore{

    private $targetId = false;

    public function __construct($targetId){
        $this->targetId = $targetId;
    }

    public function userRetore(){
        $db = walletDb::singletonMethod();
        $db = $db->init();

        if ($this->targetId !== false) {

            //복원 가능한 휴면 계정 상태인지 확인
            $sleepInfo = $db->createQueryBuilder()
                ->select('sue_id, sue_accounts_id')
                ->from('sleep_user_email')
                ->where('sue_accounts_id = ?')
                ->andWhere('sue_transfer = "SUCCESS"')
                ->setParameter(0, $this->targetId )
                ->execute()->fetch();

            //var_dump($sleepInfo);
            if (!$sleepInfo) {
                throw new Exception('복원 가능한 휴면 계정이 아닙니다.', 9999);
            }

            //admin_accounts_sleep 처리

            $targetMemberInfo = $db->createQueryBuilder()
                ->select('*')
                ->from('admin_accounts_sleep')
                ->where('id = ?')
                ->setParameter(0, $sleepInfo['sue_accounts_id'])
                ->execute()->fetch();
            //var_dump($targetMemberInfo);
            if (!$targetMemberInfo) {
                throw new Exception('sleep 회원 정보를 찾을 수 없습니다.', 9999);
            }
            $column = array(
                'id', 'email', 'wallet_phone_email', 'passwd', 'passwd_new', 'passwd_salt', 'passwd_datetime',
                'name', 'lname', 'user_ip', 'phone', 'gender', 'dob', 'location', 'auth_phone', 'auth_name', 'auth_gender',
                'auth_dob', 'auth_local_code', 'n_country', 'n_phone', 'device', 'devId', 'devId2', 'devId3'
            );

            $updateBuilder = array();
            foreach ($column as $columnValue) {
                $updateBuilder[$columnValue] = $targetMemberInfo[$columnValue];
            }
            //var_dump($targetMemberInfo);
            //var_dump($updateBuilder);
            //id랑.. sue_id 까지 조회를..?
            $proc = $db->update('admin_accounts', $updateBuilder, ['id' => $targetMemberInfo['id']]);
            if (!$proc) {
                throw new Exception('휴면 회원 정보 table에 update 실패 하였습니다.', 9999);
            }
            $proc = $db->delete('admin_accounts_sleep', ['aas_id' => $targetMemberInfo['aas_id']]);
            if (!$proc) {
                throw new Exception('휴면 회원 분리 보관 table에 delete 실패 하였습니다.', 9999);
            }


            //barry_seller_request_sleep 처리
            /*
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('*')
                ->from('barry_seller_request_sleep')
                ->where('sue_id = ?')
                ->setParameter(0, $sleepInfo['sue_id'])
                ->execute()->fetchAll();

            foreach ($targetMemberInfo as $targetMemberInfoValue) {
                $proc = $db->update('barry_seller_request', [
                    'barry_id' => $targetMemberInfoValue['barry_id'],
                    'barry_name' => $targetMemberInfoValue['barry_name'],
                ], ['id' => $targetMemberInfoValue['id']]);
                //update 에 성공하면, 해당 레코드는 제거 함... ( 전체 sue_id에 전체를 지우는게 효율적이긴 한데.... 혹시 모르니까)
                if ($proc) {
                    $proc = $db->delete('barry_seller_request_sleep', ['id' => $targetMemberInfoValue['id']]);
                }
            }
            */

            //kcp_order_sleep 처리

            $targetMemberInfo = $db->createQueryBuilder()
                ->select('*')
                ->from('kcp_order_sleep')
                ->where('sue_id = ?')
                ->setParameter(0, $sleepInfo['sue_id'])
                ->execute()->fetchAll();

            foreach ($targetMemberInfo as $targetMemberInfoValue) {
                $proc = $db->update('kcp_order', [
                    'order_name' => $targetMemberInfoValue['order_name'],
                    'gbank_name' => $targetMemberInfoValue['gbank_name'],
                ], ['idx' => $targetMemberInfoValue['idx']]);
                //update 에 성공하면, 해당 레코드는 제거 함... ( 전체 sue_id에 전체를 지우는게 효율적이긴 한데.... 혹시 모르니까)
                if ($proc) {
                    $proc = $db->delete('kcp_order_sleep', ['idx' => $targetMemberInfoValue['idx']]);
                }
            }


            //kcp_order_sleep 처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('*')
                ->from('stores_sleep')
                ->where('sue_id = ?')
                ->setParameter(0, $sleepInfo['sue_id'])
                ->execute()->fetchAll();

            foreach ($targetMemberInfo as $targetMemberInfoValue) {
                $proc = $db->update('stores', [
                    'store_name' => $targetMemberInfoValue['store_name'],
                    'store_region' => $targetMemberInfoValue['store_region'],
                    'store_address' => $targetMemberInfoValue['store_address'],
                    'store_phone' => $targetMemberInfoValue['store_phone'],
                ], ['id' => $targetMemberInfoValue['id']]);
                //update 에 성공하면, 해당 레코드는 제거 함... ( 전체 sue_id에 전체를 지우는게 효율적이긴 한데.... 혹시 모르니까)
                if ($proc) {
                    $proc = $db->delete('stores_sleep', ['id' => $targetMemberInfoValue['id']]);
                }
            }

            //복원 상태 수정
            $sleepUserEmailInsert = $db->createQueryBuilder()
                ->update('sleep_user_email')
                ->set('sue_transfer', '?')
                ->where('sue_id = ?')
                ->setParameter(0, 'FAIL')
                ->setParameter(1, $sleepInfo['sue_id'])
                ->execute();

            return true;

        }
        else{
            throw new Exception('user sleep restore 고유 ID가 누락 되었습니다.',9999);
        }

    }
}

?>
