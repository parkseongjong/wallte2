<?php
declare(strict_types=1);

namespace wallet\exception;

use Slim\Exception\HttpSpecializedException;

class WalletHttpForbiddenException extends HttpSpecializedException
{
    protected $code = 200;
    protected $message = '접근 권한이 없습니다.';
    protected $title = 'WALLET FORBIDDEN';
    protected $description = '접근 권한이 없습니다. 관리자에 문의해 주세요.';
}