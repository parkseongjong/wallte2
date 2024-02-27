<?php
namespace App\Library\Admob;
use Exception;

/**
 *
 */
class Signature
{
    /**
     * google ssv
     * @var
     */
    private $queryString = '';

    /**
     *
     */
    private $keyId = '';

    /**
     *
     * @var
     */
    private $message = '';

    /**
     *
     * @var
     */
    private $signature = '';

    /**
     *
     * @var
     */
    private $retVerify = ['code'=> 0, 'message'=> 'success!'];

    /**
     * object publicKey
     * @var
     */
    private $objPublicKey = null;

    /**
     *
     * @param string $queryString
     * @throws Exception
     */
    public function __construct($queryString='')
    {
        $queryString = trim($queryString);
        if (empty($queryString)) {
            throw new Exception('empty_query_string!');
        }

        if (strpos($queryString, '&signature') === false || strpos($queryString, '&key_id') === false) {
            throw new Exception('error_format!');
        }
        $this->queryString = $queryString;
    }


    /**
     *
     * @return void
     * @throws Exception
     */
    private function parseQueryString()
    {
        parse_str($this->queryString, $query_arr);

        $this->keyId = trim($query_arr['key_id'] ?? '');

        $this->signature = trim($query_arr['signature']?? '');

        $this->signature = str_replace(['-', '_'], ['+', '/'], $this->signature);

        $this->message = urldecode(substr($this->queryString, 0, strpos($this->queryString, '&signature')));

        if (empty($this->keyId) || empty($this->signature) || empty($this->message)) {
            throw new Exception('query_string Missing required parameters!');
        }
        $this->objPublicKey = new PublicKey($this->keyId);
    }

    /**
     *
     * @throws Exception
     */
    public function verify()
    {
        $this->parseQueryString();

        $publicKey = $this->objPublicKey->fetchPem();

        if ( !is_resource($publicKey)) {
            $this->retVerify['code'] = -2;
            $this->retVerify['message'] = 'public_key_error';
            throw new Exception($this->retVerify['message']);
        }

        $result = openssl_verify($this->message, base64_decode($this->signature), $publicKey, OPENSSL_ALGO_SHA256);

        if ($result === 1) {
            return $this->retVerify;
        }

        $this->retVerify['code'] = -3;
        $this->retVerify['message'] = openssl_error_string();

        throw new Exception($this->retVerify['message']);
    }

}