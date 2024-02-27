<?php
/*
 *
 * 회원 탈퇴 완료 시 NULL 처리와 탈퇴 테이블로 데이터 이관 하는 CLASS 입니다.
 *
 */

namespace wallet\withdrwal;

use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Auth as walletAuth;
use wallet\common\Util as walletUtil;
use \exception;


class Release{

    private $targetId = false;

    private $column = array(
        'id','email','wallet_phone_email','register_with','passwd','passwd_new','passwd_salt','passwd_datetime',
        'name','lname','user_ip','phone','gender','dob','location','auth_phone','auth_name','auth_gender',
        'auth_dob','auth_local_code','n_country','n_phone','device','devId','devId2','devId3'
    );

    public  function __construct($targetId){
        $this->targetId = $targetId;
    }

    public function userRelease(){
        try {

            $db = walletDb::singletonMethod();
            $db = $db->init();
            $auth = walletAuth::singletonMethod();

            $memberInfoBuild = $db->createQueryBuilder()
                ->select('*')
                ->from('withdrawal_user')
                ->where('wu_accounts_id = ?')
                ->andWhere('wu_status is NULL OR wu_status = ?')
                ->setParameter(0,$this->targetId)
                ->setParameter(1,'PENDING');

            $memberInfo = $memberInfoBuild->execute()->fetch();
            if(!$memberInfo){
                throw new Exception('회원 탈퇴 대상자가 아닙니다.',9999);
            }

            $updateBuilder = array();
            foreach ($this->column as $columnValue){
                $updateBuilder[$columnValue] = NULL;
            }
            unset($updateBuilder['id']);

            //회원 정보 table null 처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select($this->column)
                ->from('admin_accounts')
                ->where('id = ?')
                ->setParameter(0,$memberInfo['wu_accounts_id'])
                ->execute()->fetch();
//            var_dump($targetMemberInfo);

            if($targetMemberInfo){
                $targetMemberInfo['wu_id'] = $memberInfo['wu_id'];
                $proc = $db->insert('admin_accounts_withdrawal',$targetMemberInfo);
                if(!$proc){
                    throw new Exception('회원 정보를 탈퇴 table에 insert 실패 하였습니다.',9999);
                }
                $proc = $db->update('admin_accounts',$updateBuilder,['id'=>$memberInfo['wu_accounts_id']]);
            }

            //barry_auth null 처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('email')
                ->from('barry_auth')
                ->where('mb_id = ?')
                ->setParameter(0,$memberInfo['wu_accounts_id'])
                ->execute()->fetch();
            //var_dump($targetMemberInfo);
            if($targetMemberInfo) {
                $proc = $db->insert('barry_auth_withdrawal', [
                    'email' => $targetMemberInfo['email'],
                    'wu_id' => $memberInfo['wu_id']
                ]);
                if (!$proc) {
                    throw new Exception('barry auth 를 탈퇴 table insert를 실패 하였습니다.', 9999);
                }
                $proc = $db->update('barry_auth', ['email' => null], ['mb_id' => $memberInfo['wu_accounts_id']]);
            }


            //barry_seller_request 처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('id,barry_id, barry_name')
                ->from('barry_seller_request')
                ->where('admin_id = ?')
                ->setParameter(0,$memberInfo['wu_accounts_id'])
                ->execute()->fetchAll();
            //var_dump($targetMemberInfo);
            foreach ($targetMemberInfo as $targetMemberInfoValue){
                $proc = $db->insert('barry_seller_request_withdrawal',[
                    'id'=>$targetMemberInfoValue['id'],
                    'barry_id'=>$targetMemberInfoValue['barry_id'],
                    'barry_name'=>$targetMemberInfoValue['barry_name'],
                    'wu_id'=>$memberInfo['wu_id']
                ]);
                if(!$proc){
                    throw new Exception('barry seller request 를 탈퇴 table insert를 실패 하였습니다.',9999);
                }
                $proc = $db->update('barry_seller_request',['barry_id'=>null,'barry_name'=>null],['admin_id'=>$memberInfo['wu_accounts_id']]);
            }


            //coupon_result처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('id,bank_insert_name')
                ->from('coupon_result')
                ->where('user_id = ?')
                ->setParameter(0,$memberInfo['wu_accounts_id'])
                ->execute()->fetchAll();
            //var_dump($targetMemberInfo);
            foreach ($targetMemberInfo as $targetMemberInfoValue){
                $proc = $db->insert('coupon_result_withdrawal',['id'=>$targetMemberInfoValue['id'],'bank_insert_name'=>$targetMemberInfoValue['bank_insert_name'],'wu_id'=>$memberInfo['wu_id']]);
                if(!$proc){
                    throw new Exception('coupon result를 탈퇴 table insert를 실패 하였습니다.',9999);
                }
                $proc = $db->update('coupon_result',['bank_insert_name'=>null],['user_id'=>$memberInfo['wu_accounts_id']]);
            }



            //KCP ORDER 처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('idx,order_name,gbank_name')
                ->from('kcp_order')
                ->where('order_uid = ?')
                ->setParameter(0,$memberInfo['wu_accounts_id'])
                ->execute()->fetchAll();
            //var_dump($targetMemberInfo);
            foreach ($targetMemberInfo as $targetMemberInfoValue){
                $proc = $db->insert('kcp_order_withdrawal',[
                    'idx'=>$targetMemberInfoValue['idx'],
                    'order_name'=>$targetMemberInfoValue['order_name'],
                    'gbank_name'=>$targetMemberInfoValue['gbank_name'],
                    'wu_id'=>$memberInfo['wu_id']
                ]);
                if(!$proc){
                    throw new Exception('coupon result를 탈퇴 table insert를 실패 하였습니다.',9999);
                }
                $proc = $db->update('kcp_order',['order_name'=>null,'gbank_name'=>null],['order_uid'=>$memberInfo['wu_accounts_id']]);
            }

            //stores 처리
            $targetMemberInfo = $db->createQueryBuilder()
                ->select('id,store_name,store_region,store_address,store_phone')
                ->from('stores')
                ->where('admin_id = ?')
                ->setParameter(0,$memberInfo['wu_accounts_id'])
                ->execute()->fetchAll();
            //var_dump($targetMemberInfo);
            foreach ($targetMemberInfo as $targetMemberInfoValue){
                $proc = $db->insert('stores_withdrawal',[
                    'id'=>$targetMemberInfoValue['id'],
                    'store_name'=>$targetMemberInfoValue['store_name'],
                    'store_region'=>$targetMemberInfoValue['store_region'],
                    'store_address'=>$targetMemberInfoValue['store_address'],
                    'store_phone'=>$targetMemberInfoValue['store_phone'],
                    'wu_id'=>$memberInfo['wu_id']
                ]);
                if(!$proc){
                    throw new Exception('stores 탈퇴 table insert를 실패 하였습니다.',9999);
                }
                $proc = $db->update('stores',[
                    'store_name'=>null,
                    'store_region'=>null,
                    'store_address'=>null,
                    'store_phone'=>null
                ],
                    ['admin_id'=>$memberInfo['wu_accounts_id']]);
            }

//            완료 update 기록
            $updateProc = $db->createQueryBuilder()
                ->update('withdrawal_user')
                ->set('wu_status', '?')
                ->where('wu_id = ?')
                ->setParameter(0, 'SUCCESS')
                ->setParameter(1, $memberInfo['wu_id'])
                ->execute();
            //완료 후 기존 세션은 파괴...( 일반 유저 요청 시 세션을 파괴...)
            if(!$auth->sessionAuthAdminCheck()){
                session_destroy();
            }

            return array('code' => 200);
        }
        catch (Exception $e){
            return array('msg'=>$e->getMessage(),'code'=>$e->getCode());
        }
    }

}