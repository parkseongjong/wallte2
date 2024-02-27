<?php
namespace App\Exception;

use Exception;
use App\Library\Log;

/**
 * Class ApiException extends Exception
 *
 * @package App\Exception
 * @author seungo.jo
 * @since 2018-02-05
 */
final class ApiException extends Exception
{
    /**
     * @param Exception $e
     */
    public function __construct($e)
    {
        parent::__construct();
        http_response_code(500);
        $error = 'server_internal_error';
        $message = '서버 에러. 관리자에게 문의하세요.';

        $log = new Log(get_class());
        try {
            $log->instance->error(json_encode([
                $e->getCode(),
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            ]));
        } catch (Exception $e) {
            $log->instance->critical($e->getMessage());
        }

        if (APP_ENV == 'production') {
            $message = 'server internal error';
        }

        if (!IS_OFFICE) {
            $message = $e->getMessage();
            $error =  $e->getCode();
        }

        $response = [
            'error' => $error,
            'message' => $message
        ];

        output($response);
    }
}

/* End of file ApiException.php */
/* Location: /app/Exception/ApiException.php */