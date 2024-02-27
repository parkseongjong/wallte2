<?php

namespace App\Controllers;

use App\Library\Validation;
use App\Exception\UnauthorizedException;

use App\Models\PaidModel;
use App\Service\AuthService;
use Exception;


/**
 * Class BaseController
 *
 * @package App\Controllers\BaseController
 * @author jso
 */
class BaseController
{
    /**
     * @var $validator
     */
    protected $validator;

    /**
     * @var $request
     */
    protected $request;

    /**
     * @var $params
     */
    protected $params;

    /**
     * @var $accessToken
     */
    protected $accessToken;

    /**
     * @var $userID
     */
    protected $userID;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        $this->accessToken = $this->getBearerToken();
        $this->validator = new Validation();
        $params = $_GET ?? $_POST;
        $method = $_SERVER['REQUEST_METHOD'];
        $request = $_SERVER['REQUEST_URI'];
        $parts = parse_url($request);
        $path = $parts['path'];
        $query = $parts['query'];
        $last = substr($path, strrpos($path, '/') + 1);

        unset($params['_url']);
        if (empty($params) === true) {
            $params = json_decode(file_get_contents("php://input"), true);
        }

        if (empty($params) === true) {
            parse_str(file_get_contents('php://input'), $_PUT);
            $params = $_PUT;
        }

        $this->params = $params;
        $this->request = [
            'PARAMS' => $params,
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $request,
            'QUERY_STRING' => $query,
            'PARTS' => $parts,
            'PATH' => $path,
            'last' => $last
        ];

    }

    /**
     * @desc 지갑용 로그인 체크
     */
    protected static function getParsedBody()
    {
        return json_decode(file_get_contents("php://input"), true) ?? [];
    }

    /**
     * @return mixed
     * @throws UnauthorizedException
     * @throws \Doctrine\DBAL\Exception
     */
    protected function isAuth()
    {
        $this->accessToken = urldecode($this->accessToken);
        if (strpos($this->accessToken,'autologin') == 0) {
            $password = str_replace('autologin|', '', $this->accessToken);
            $PaidModel = new PaidModel();
            $memberInfo = $PaidModel->walletDb->createQueryBuilder()
                ->select('*')
                ->from('user_autologin')
                ->where('ua_key = ?')
                ->setParameter(0, $password)
                ->fetchAssociative();

            if (empty($memberInfo) === true) {
                throw new UnauthorizedException('bad_request_autologin');
            }

            if (time() > strtotime($memberInfo['ua_datetime'] . '+15 days')) {
                throw new UnauthorizedException('expired_key');
            }

            $tempMemberInfo = $PaidModel->walletDb->createQueryBuilder()
                ->select('*')
                ->from('admin_accounts')
                ->where('id = ?')
                ->setParameter(0, $memberInfo['ua_admin_accounts_id'])
                ->fetchAssociative();

            if (empty($tempMemberInfo) === true) {
                throw new UnauthorizedException('not_match_key');
            }

            $userID = $tempMemberInfo['id'];
        } else {
            try {
                if (empty($this->accessToken)) {
                    throw new Exception('invalid_access_token');
                }
                $authService = new AuthService();
                $jwt = $authService->decodeToken($this->accessToken);
                list($userID) = explode("-", $jwt->jti);
            } catch (Exception $e) {
                throw new UnauthorizedException('invalid_access_token');
            }
        }

        $this->userID = $userID;
    }


    /**
     * @param mixed $vars
     * @param bool $options
     * @return void
     */
    protected static function responses($vars = null, $options = 'NUMERIC')
    {
        header('Content-Type: application/json');

        $responses = [];
        if (is_array($vars) === false) {
            $responses['message'] = $vars;
        } else {
            $responses = $vars;
        }

        switch ($options) {
            case 'NUMERIC':
                echo json_encode($responses, JSON_NUMERIC_CHECK);
                break;
            case "TAG":
                echo json_encode($responses, JSON_HEX_TAG);
                break;
            case "APOS":
                echo json_encode($responses, JSON_HEX_APOS);
                break;
            case "QUOT":
                echo json_encode($responses, JSON_HEX_QUOT);
                break;
            case "AMP":
                echo json_encode($responses, JSON_HEX_AMP);
                break;
            case "UNICODE":
                echo json_encode($responses, JSON_UNESCAPED_UNICODE);
                break;
            case "OBJECT":
                echo json_encode($responses, JSON_FORCE_OBJECT);
                break;
            case "ALL":
                echo json_encode($responses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                break;
            default:
                echo json_encode($responses);
                break;
        }
    }

    public function getAuthorizationHeader(): ?string
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}

/* End of file BaseController.php */
/* Location: /app/Controller/BaseController.php */
