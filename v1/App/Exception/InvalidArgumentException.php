<?php
namespace App\Exception;

use Exception;

/**
 * Class InvalidArgumentException extends Exception
 *
 * @package App\Exception
 * @author seungo.jo
 * @since 2018-02-05
 */
final class InvalidArgumentException extends Exception
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

/* End of file InvalidArgumentException.php */
/* Location: /app/Exception/InvalidArgumentException.php */