<?php
namespace App\Exception;

use Exception;

/**
 * Class BadRequestException extends Exception
 *
 * @package App\Exception
 * @author seungo.jo
 * @since 2018-02-05
 */
final class BadRequestException extends Exception
{
    /**
     * @param $error
     */
    public function __construct($error)
    {
        parent::__construct();
        http_response_code(400);
        $response = [
            'error' => $error,
            'message' => LANG['EXCEPTION'][$error] ?? getLangException($error)
        ];

        output($response);
    }
}

/* End of file BadRequestException.php */
/* Location: /app/Exception/BadRequestException.php */