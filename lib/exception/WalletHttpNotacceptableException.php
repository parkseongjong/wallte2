<?php
declare(strict_types=1);

namespace wallet\exception;

use Slim\Exception\HttpSpecializedException;

class WalletHttpNotacceptableException extends HttpSpecializedException
{
    protected $code = 200;
    protected $message = '일치하는 정보가 존재하지 않습니다.';
    protected $title = 'WALLET NOT ACCEPTABLE';
    protected $description = '일치하는 정보가 존재하지 않습니다. 관리자에 문의해 주세요.';
}