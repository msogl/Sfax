<?php

namespace Msogl\Sfax;

require_once __DIR__ . '/../vendor/autoload.php';

class SfaxTest
{
    public function testSendFax($config, $fromFaxNumber, $recipientFaxNumber, $recipientFaxName, $faxFilePath)
    {
        $sfax = new SfaxApi($config);
        $resp = $sfax->sendFax($fromFaxNumber, $recipientFaxNumber, $recipientFaxName, $faxFilePath);

        if ($resp === false) {
            echo $sfax->lastError . "\n";
        } else {
            var_dump($resp);
        }
    }

    public function testUsageReport($config, $startDate=null, $endDate=null)
    {
        $sfax = new SfaxApi($config);
        $resp = $sfax->usageReport($startDate, $endDate);
        
        if ($resp === false) {
            echo $sfax->lastError . "\n";
        } else {
            var_dump($resp);
        }
    }
}

/*
Insert code here to load env vars with the following:
SFAX_USERNAME
SFAX_APIKEY
SFAX_ENCKEY
SFAX_IV
SFAX_CLIENT (optional - see Sfax API documentation)

Use your favorite dotenv loader, or create the $config
array some other way.
*/

$config = [
    'SFAX_USERNAME' => $_ENV['SFAX_USERNAME'],
    'SFAX_APIKEY' => $_ENV['SFAX_APIKEY'],
    'SFAX_ENCKEY' => $_ENV['SFAX_ENCKEY'],
    'SFAX_IV' => $_ENV['SFAX_IV'],
    'SFAX_CLIENT' => '',            // (optional)
];

if (!isset($argv[1])) {
    die("Usage: php SfaxTest.php path-to-pdf-file\n\n");
}

$pathToPdfFile = $argv[1];

$fromFaxNumber = 'DEFAULT';
$recipientFaxNumber = '5555555555';
$recipientFaxName = 'Test Fax';

$sfaxTest = new SfaxTest();
$sfaxTest->testSendFax($config, $fromFaxNumber, $recipientFaxNumber, $recipientFaxName, $pathToPdfFile);
$sfaxTest->testUsageReport($config);
$sfaxTest->testUsageReport($config, '2024-07-08');
