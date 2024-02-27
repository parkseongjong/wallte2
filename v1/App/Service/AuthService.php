<?php

namespace App\Service;

use App\Exception\UnauthorizedException;
use App\Exception\InvalidArgumentException;
use App\Exception\BadRequestException;
use App\Exception\ApiException;
use Exception;
use Firebase\JWT\JWT;

/**
 * Class AuthService
 *
 * @package App\Service
 * @author seungo.jo
 * @since 2018-02-27
 */
class AuthService
{
    /**
     * constructor receives container instance
     *
     * @internal param container $container
     */
    public function __construct()
    {
    }

    /**
     * @param string $token
     * @return object
     * @throws UnauthorizedException
     * @desc
     */
    public static function decodeToken(string $token): object
    {
        try {
            $alg[] = security['alg'];
            $jwt = JWT::decode($token, security['secret_token'], $alg);
            if (time() > $jwt->exp) {
                throw new UnauthorizedException('token_expired');
            }

            return $jwt;
        } catch (Exception $e) {
            throw new UnauthorizedException('invalid_access_token');
        }
    }

    /**
     * @param $userId
     * @return array
     * @throws InvalidArgumentException
     * @desc
     */
    public static function accessToken($userId): array
    {
        if (empty($userId) === true) {
            throw new InvalidArgumentException('Unexpected_user_id');
        }

        $iat = time();
        $nbf = $iat;
        $exp = $nbf + security['exp'];
        $refresh_exp = $nbf + security['refresh_exp'];
        $jti = $userId . '-' . $iat . '-' . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        $data = [
            'jti' => $jti,
            'iss' => security['iss'],
            'iat' => $iat,
            'nbf' => $nbf,
            'exp' => $exp,
            'refresh_exp' => $refresh_exp,
            'api_type' => API_TYPE
        ];

        $accessToken = JWT::encode($data, security['secret_token']);

        return [
            'accessToken' => $accessToken,
            'expire' => $exp
        ];
    }

}

/* End of file AuthService.php */
/* Location: ./app/Service/AuthService.php */