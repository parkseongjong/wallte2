<?php

namespace App\Controllers;

use App\Models\PaidModel;
use Respect\Validation\Validator as V;
use App\Exception\InvalidArgumentException;
use App\Exception\UnauthorizedException;
use App\Exception\BadRequestException;
use App\Service\AuthService;
use App\Service\UserService;


/**
 * Class UserController
 *
 * @package App\Controllers\BaseController
 * @author jso
 */
class UserController extends BaseController
{
    /**
     * @throws BadRequestException
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws InvalidArgumentException|UnauthorizedException
     * @throws BadRequestException
     * @description 무료 포인트 리스트
     */
    public function freeList()
    {
        $this->isAuth();
        $params = $this->params;
        $params['page'] = $params['page'] ?? 1;
        $params['s_date'] = $params['s_date'] ?? TIME_YMD;
        $params['e_date'] = $params['e_date'] ?? TIME_YMD;

        $this->validator->validate($params, [
            'page' => [
                'rules' => V::numeric(),
                'message' => 'invalid_page'
            ],
            's_date' => [
                'rules' => V::date(),
                'message' => 'invalid_s_date'
            ],
            'e_date' => [
                'rules' => V::date(),
                'message' => 'invalid_e_date'
            ],
        ])->isValid();

        $userService = new UserService();
        $userService->setUserId($this->userID);
        $data = $userService->UserFreeList($params);

        static::responses($data);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws InvalidArgumentException|UnauthorizedException
     * @throws BadRequestException
     * @description 유료 포인트 리스트
     */
    public function paidList()
    {
        $this->isAuth();
        $params = $this->params;
        $params['page'] = $params['page'] ?? 1;
        $params['s_date'] = $params['s_date'] ?? TIME_YMD;
        $params['e_date'] = $params['e_date'] ?? TIME_YMD;

        $this->validator->validate($params, [
            'page' => [
                'rules' => V::numeric(),
                'message' => 'invalid_page'
            ],
            's_date' => [
                'rules' => V::date(),
                'message' => 'invalid_s_date'
            ],
            'e_date' => [
                'rules' => V::date(),
                'message' => 'invalid_e_date'
            ],
        ])->isValid();

        $userService = new UserService();
        $userService->setUserId($this->userID);
        $data = $userService->UserPaidList($params);

        static::responses($data);
    }

    /**
     * @throws UnauthorizedException
     * @throws \Exception
     * @description 회원정보 조회
     */
    public function info()
    {
        $this->isAuth();
        $PaidModel = new PaidModel();
        $userEtc = $PaidModel->getUserEtc($this->userID);

        static::responses([
            'paid_point' => $userEtc['ue_paid_point'] ?? 0,
            'free_point' => $userEtc['ue_free_point'] ?? 0
        ]);
    }

    /**
     * @desc 구글 인앱 결제
     * @throws BadRequestException
     * @throws \Doctrine\DBAL\Exception
     * @throws UnauthorizedException
     * @throws InvalidArgumentException
     * @description 결제 리스트
     */
    public function purchaseList()
    {
        $this->isAuth();
        $params = $this->params;
        $params['page'] = $params['page'] ?? 1;
        $params['s_date'] = $params['s_date'] ?? TIME_YMD;
        $params['e_date'] = $params['e_date'] ?? TIME_YMD;

        $this->validator->validate($params, [
            'page' => [
                'rules' => V::numeric(),
                'message' => 'invalid_page'
            ],
            's_date' => [
                'rules' => V::date(),
                'message' => 'invalid_s_date'
            ],
            'e_date' => [
                'rules' => V::date(),
                'message' => 'invalid_e_date'
            ],
        ])->isValid();

        $userService = new UserService();
        $userService->setUserId($this->userID);
        $data = $userService->UserPurchaseList($params);

        static::responses($data);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function token()
    {
        $this->isAuth();
        $auth = new AuthService();
        $accessToken = $auth::accessToken($this->userID);

        static::responses($accessToken);
    }
}


/* End of file UserController.php */
/* Location: /App/Controller/UserController.php */
