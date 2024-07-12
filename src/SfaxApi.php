<?php

namespace Msogl\Sfax;

class SfaxApi
{
    public $lastError = null;

    private $serviceEndpointUrl = 'https://api.sfaxme.com/api/';
    private $securityToken = null;
    private $config = null;
    private $apiKey = null;
    private $errorCode = [
        '0001' => 'Fax file not found',
        '0002' => 'From fax number is not a real fax number',
        '0003' => 'Recipient fax number is not a real fax number',
        '0004' => 'Must have a fax file if no cover page is specified',
    ];

    private $coverPage = null;

    public function __construct($config)
    {
        $this->config = &$config;
        $this->apiKey = &$config['SFAX_APIKEY'];
        $this->init();
    }

    public function init()
    {
        $this->coverPage = [
            'name' => 'None',
            'subject' => '',
            'reference' => '',
            'remarks' => '',
            'fromname' => '',
            'fromphone' => '',
            'timezone' => '',
        ];
    }

    public function setCoverPage($params)
    {
        $this->coverPage['name'] = $params['name'];
        $this->coverPage['subject'] = $params['subject'] ?? '';
        $this->coverPage['reference'] = $params['reference'] ?? '';
        $this->coverPage['remarks'] = $params['remarks'] ?? '';
        $this->coverPage['fromname'] = $params['fromname'] ?? '';
        $this->coverPage['fromphone'] = $params['fromphone'] ?? '';
        $this->coverPage['timezone'] = $params['timezone'] ?? '';
    }

    public function sendFax(string $fromFaxNumber, string $recipientFaxNumber, string $recipientName, string $faxFilePath)
    {
        if ($faxFilePath == null && $this->coverPage['name'] == 'None') {
            $this->lastError = $this->errorCode['0004'];
            return false;
        }

        if ($faxFilePath != null && !file_exists($faxFilePath)) {
            $this->lastError = $this->errorCode['0001'];
            return false;
        }

        $sFaxApiUtil = new SfaxApiUtil($this->config);

        $useDefaultFromFax = (strcasecmp($fromFaxNumber, 'DEFAULT') == 0);

        if (!$useDefaultFromFax) {
            try {
                $fromFaxNumber = $sFaxApiUtil->normalizeFaxNumber($fromFaxNumber);
            } catch (\Exception $e) {
                $this->lastError = $this->errorCode['0002'];
                return false;
            }
        }

        try {
            $recipientFaxNumber = $sFaxApiUtil->normalizeFaxNumber($recipientFaxNumber);
        } catch (\Exception $e) {
            $this->lastError = $this->errorCode['0003'];
            return false;
        }

        // RecipientName cannot be blank
        if ($recipientName == '') {
            $recipientName = $recipientFaxNumber;
        }

        $optionalParams = $this->buildCoverPageParams();

        if (!$useDefaultFromFax) {
            $optionalParams .= ";SenderFaxNumber=" . $fromFaxNumber;
        }

        $this->securityToken = $sFaxApiUtil->generateSecurityTokenUrl();

        // Construct the base service URL endpoint
        $url = $this->serviceEndpointUrl;
        $url .= "SendFax";
        $url .= "?token=" . urlencode($this->securityToken);
        $url .= "&ApiKey=" . urlencode($this->apiKey);

        // Add the method specific parameters
        $url .= "&RecipientFax=" . urlencode($recipientFaxNumber);
        $url .= "&RecipientName=" . urlencode($recipientName);
        $url .= "&OptionalParams=" . urlencode($optionalParams);
        $url .= "&";

        $params = ['file' => new \CurlFile($faxFilePath)];

        $headers = [
            'Content-Type: multipart/form-data',
        ];

        //echo $url."\n";die;

        $resp = $this->callApi($url, $params, $headers);
        if (is_object($resp) && $resp->isSuccess) {
            return $resp;
        }

        $this->lastError = $resp->message;
        return false;
    }

    public function usageReport($startDate = null, $endDate = null)
    {
        $this->securityToken = (new SfaxApiUtil($this->config))->generateSecurityTokenUrl();

        if ($startDate == null) {
            $startDate = (new \DateTime())->sub(new \DateInterval('P30D'))->format('Y-m-d');
        }

        if ($endDate == null) {
            $endDate = (new \DateTime())->format('Y-m-d');
        }

        $startDateUTC = date('Y-m-d', strtotime($startDate)) . 'T00:00:00Z';
        $endDateUTC = date('Y-m-d', strtotime($endDate)) . 'T23:59:59Z';

        // Construct the base service URL endpoint
        $url = $this->serviceEndpointUrl;
        $url .= "UsageReport?";
        $url .= "token=" . urlencode($this->securityToken);
        $url .= "&ApiKey=" . urlencode($this->apiKey);
        $url .= "&ReportType=1";
        $url .= "&ReportParams=StartDateUTC={$startDateUTC};EndDateUTC={$endDateUTC}";

        $resp = $this->callApi($url);

        if (is_object($resp) && $resp->isSuccess) {
            return $resp;
        }

        $this->lastError = $resp->message;
        return false;
    }

    private function callApi($url, $postData = null, $headers = null)
    {
        //initialize cURL and set cURL options
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);            // 0 = Don't return the header, 1 = Return the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // Return contents as a string
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);           // Unlimited
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);        // 06/02/2023 JLC Respects case-sensitivity on header keys
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($postData != null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }

        $responseBody = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //get headers and response data
        $apiResponse = new SfaxApiResponse();
        $headers = $apiResponse->getHeaders($responseBody, $responseInfo);

        if ($responseInfo['http_code'] == 200) {
            return $apiResponse->getResponseData($responseBody, $responseInfo);
        }

        // Do something header
        $this->lastError = 'HTTP Error: ' . $responseInfo['http_code'];
        return false;
    }

    private function buildCoverPageParams()
    {
        $params = 'CoverPageName=None';

        if (strcasecmp($this->coverPage['name'], 'none') == 0) {
            return $params;
        }

        $params = 'CoverPageName=' . $this->coverPage['name'];

        if ($this->coverPage['subject']) {
            $params .= ';CoverPageSubject=' . $this->coverPage['subject'];
        }

        if ($this->coverPage['reference']) {
            $params .= ';CoverPageReference=' . $this->coverPage['reference'];
        }

        if ($this->coverPage['remarks']) {
            $params .= ';CoverPageRemarks=' . $this->coverPage['remarks'];
        }

        if ($this->coverPage['fromname']) {
            $params .= ';CoverPageFromName=' . $this->coverPage['fromname'];
        }

        if ($this->coverPage['fromphone']) {
            $params .= ';CoverPageFromPhone=' . $this->coverPage['fromphone'];
        }

        if ($this->coverPage['timezone']) {
            $params .= ';CoverPageTimeZone=' . $this->coverPage['timezone'];
        }

        return $params;
    }
}
