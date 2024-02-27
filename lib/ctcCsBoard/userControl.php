<?php
namespace wallet\ctcCsBoard;

use wallet\ctcDbDriver\Driver as walletDb;
use wallet\common\Util as walletUtill;

class UserControl{

    private $data = false;
    private $memberInfo = false;
    private $logger = false;

    public function __construct($postData, $memberInfo, $logger){
        $this->data = $postData;
        $this->memberInfo = $memberInfo;
        //로거는 아직 안쓰임..
        $this->logger = $logger;
    }

    public function insert(){
        $db = walletDb::singletonMethod();
        $utill = walletUtill::singletonMethod();
        $walletDb = $db->ctcWallet();
        unset($db);

        $nowDate = $utill->getDateSql();
        $defDate = $utill->getDateSqlDefault();

        //제목이 없는 경우는 없지만 만약 없는 경우.. 처리
        if(mb_strlen('최초제목') > 0){
            $md5SaltSubject = substr('최초제목',0,1);
        }
        else{
            $md5SaltSubject = rand(0,9);
        }

        //민감 데이터가 아니기 때문에 기본 md5로도 충분 할 것 같음.
        $md5 = md5('customerService|작성자 고유ID|'.$nowDate.'|'.$md5SaltSubject);

        //고유 해시는 customerService|작성자 고유 ID|게시물 생성일|최초 제목(맨 앞글자)

        $walletDb->insert('customer_service',
            [
                'hash' => $md5,
                'body' => 2,
                'created_date' => $nowDate,
                'updated_date' => $defDate,
                'author_id' => 3,
                'author_login_id' => 3,
                'author_name' => 3,
                'author_email' => 3,
                'author_phone' => 3,
                'author_type' => 3,
                'state' => 3,
                'reply_type' => 'NONE',
                'reply_push' => 0,
            ]
        );
    }

    public function select(){
        $db = walletDb::singletonMethod();
        $utill = walletUtill::singletonMethod();
        $walletDb = $db->ctcWallet();
        unset($db);
        if(empty($this->data['page'])){
            $this->data['page'] = 1;
        }
        if(empty($this->data['pageRows'])){
            $this->data['pageRows'] = 5;
        }
        $walletDb->orderBy('id', 'DESC');
        $walletDb->pageLimit = $this->data['pageRows'];
        return $walletDb->arraybuilder()->paginate("customer_service", $this->data['page'], '*');

    }



}