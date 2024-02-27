<?php
namespace App\Exception;

use Exception;

/**
 * Class CustomException extends Exception
 *
 * @package App\Exception
 * @author seungo.jo
 * @since 2018-02-05
 */
final class CustomException extends Exception
{
    /**
     * @param $error
     */
    public function __construct($error, $time = 0)
    {
        parent::__construct();
        http_response_code(400);

        $message = LANG['EXCEPTION'][$error] ?? getLangException($error);
        if ($time) {
            $message = str_replace('{0}', $time, $message);
        }


        $response = [
            'error' => $error,
            'message' => $message
        ];

        output($response);
    }
}

/* End of file BadRequestException.php */
/* Location: /app/Exception/BadRequestException.php */