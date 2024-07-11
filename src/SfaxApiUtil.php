<?php

namespace Msogl\Sfax;

class SfaxApiUtil
{
    private $encryptionKey = null;
    private $initVector = null;
    private $username = '';
    private $apikey = '';
    private $client = '';

    public function __construct($config)
    {
        $this->init($config);
    }

    public function init($config)
    {
        $this->encryptionKey = $config['SFAX_ENCKEY'];
        $this->initVector = $config['SFAX_IV'];
        $this->username = $config['SFAX_USERNAME'];
        $this->apikey = $config['SFAX_APIKEY'];
        $this->client = $config['SFAX_CLIENT'] ?? '';
    }

    public function generateSecurityTokenUrl($context='')
    {

        // Context is always blank. Might be reserved for future use.
        $tokenDataInput = "Context={$context}";
        $tokenDataInput .= "&Username={$this->username}";
        $tokenDataInput .= "&ApiKey={$this->apikey}";

        // Generate date in UTC format
        $tokenGenDT = gmdate('Y-m-d') . 'T' . gmdate('H:i:s') . 'Z';
        $tokenDataInput .= "&GenDT={$tokenGenDT}";

        if ($this->client != '') {
            $tokenDataInput .= "&Client={$this->client}";
        }

        return base64_encode($this->encrypt($tokenDataInput));
    }

    public function encrypt($text)
    {
        if ($this->encryptionKey == null) {
            throw new \Exception('Missing encryptionKey');
        }

        if ($this->initVector == null) {
            throw new \Exception('Missing initVector');
        }

        // $option 0 = PKCS#7 padding, base64-encoded
        // $option 1 = PKCS#7 padding, raw (OPENSSL_RAW_DATA)
        return openssl_encrypt($text, 'aes-256-cbc', $this->encryptionKey, OPENSSL_RAW_DATA, $this->initVector);
    }

    public function decrypt($text)
    {
        if ($this->encryptionKey == null) {
            throw new \Exception('Missing encryptionKey');
        }

        if ($this->initVector == null) {
            throw new \Exception('Missing initVector');
        }

        return rtrim(openssl_decrypt($text, 'aes-256-cbc', $this->encryptionKey, OPENSSL_RAW_DATA, $this->initVector), "\x00..\x20");
    }

    public function normalizeFaxNumber(string $faxNumber)
    {
        if (substr($faxNumber, 0, 1) != '1') {
            $faxNumber = '1' . $faxNumber;
        }

        $faxNumber = preg_replace('/\D/', '', $faxNumber);

        if ($faxNumber == null || $faxNumber == '') {
            throw new \Exception('invalid fax number format');
        }

        return $faxNumber;
    }
}