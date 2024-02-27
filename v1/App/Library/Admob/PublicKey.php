<?php
namespace App\Library\Admob;
use Exception;

class PublicKey
{
    /**
     * @var string $keysUrl
     */
    private $keysUrl = 'https://www.gstatic.com/admob/reward/verifier-keys.json';

    /**
     *
     * @var string $keyId
     */
    private $keyId;

    /**
     * @var array keysMap
     */
    private $keysMap = ['pem'=> '', 'base64'=>''];

    /**
     *
     * @param string $keyId
     * @throws Exception
     */
    public function __construct($keyId='')
    {
        $keyId = trim($keyId);
        if (empty($keyId)) {
            throw new Exception("key_id error！");
        }
        $this->keyId = $keyId;
    }

    /**
     *
     * @param string $keysUrl
     * @throws Exception
     */
    public function setVerifierKeysUrl($keysUrl)
    {
        $keysUrl = trim($keysUrl);
        if (empty($keysUrl) || !filter_var($keysUrl, FILTER_VALIDATE_URL)) {
            throw new Exception("key_url error！");
        }
        $this->keysUrl = $keysUrl;
    }

    /**
     *
     * @return false
     */
    public function fetchVerifierKeys()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,3);
        curl_setopt($ch, CURLOPT_URL, $this->keysUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);

        if(curl_errno($ch)){
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $result = json_decode($result, true);
        $keysArr = [];
        foreach ($result['keys'] as $keys) {
            $keyId = trim($keys['keyId'] ?? '');
            if (empty($keyId)) {
                continue;
            }
            $keysArr[$keyId]['pem'] = trim($keys['pem']);
            $keysArr[$keyId]['base64'] = trim($keys['base64']);
        }

        if (!isset($keysArr[$this->keyId])) {
            return false;
        }

        $this->keysMap = $keysArr[$this->keyId];
    }

    /**
     *
     * @return resource
     */
    public function fetchPem()
    {
        if (empty($this->keysMap['pem'])) {
            $this->fetchVerifierKeys();
        }

        if (!isset($this->keysMap['pem'])) {
            return false;
        }
        return openssl_get_publickey(trim($this->keysMap['pem']));
    }

    /**
     *
     * @return resource
     */
    public function fetchBase64()
    {
        if (empty($this->keysMap['base64'])) {
            $this->fetchVerifierKeys();
        }

        if (!isset($this->keysMap['base64'])) {
            return false;
        }
        $pem = "-----BEGIN PUBLIC KEY-----\n" . wordwrap(trim($this->keysMap['base64']), 64, "\n", true) . "\n-----END PUBLIC KEY-----";

        return openssl_get_publickey($pem);
    }

}