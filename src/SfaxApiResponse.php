<?php

namespace Msogl\Sfax;

class SfaxApiResponse
{
    public static function getHeaders($responseBody, $responseInfo)
    {
        $headerData = substr($responseBody, 0, $responseInfo['header_size']);
        $headers = [];

        foreach (explode("\n", $headerData) as $line) {
            $parts = explode(": ", $line);
            if (count($parts) == 2) {
                if (isset($headers[$parts[0]])) {
                    if (is_array($headers[$parts[0]])) {
                        $headers[$parts[0]][] = chop($parts[1]);
                    } else {
                        $headers[$parts[0]] = array($headers[$parts[0]], chop($parts[1]));
                    }
                } else {
                    $headers[$parts[0]] = chop($parts[1]);
                }
            }
        }

        return $headers;
    }

    public static function getResponseData($responseBody, $responseInfo)
    {
        return json_decode(substr($responseBody, $responseInfo['header_size']));
    }

    // Common ONLY to Inbound Fax RetrieveSet
    public static function getInboundResponseData($responseBody, $responseInfo)
    {
        return json_decode(substr($responseBody, $responseInfo['header_size']));
    }

    // Common to ONLY InboundFaxDownloadTIF & InboundFaxDownloadPDF
    public static function writeResponseToFile($responseBody, $responseInfo, $localFilePath)
    {
        $data = substr($responseBody, $responseInfo['header_size']);
        $fp = fopen($localFilePath, "w");
        fwrite($fp, $data, strlen($data));
        fclose($fp);
    }
}
