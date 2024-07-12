# SfaxApi

PHP API for sending a fax via Sfax.

Based on the documentation at https://sfax.scrypt.com/category/1309-getting-started.

The PHP code sample there is outdated and uses the old mcrypt libraries in PHP. This uses OpenSSL.

## Install
```
composer require msogl/sfax
```

## Configuration
This uses a $config array, like so:
```
$config = [
    'SFAX_USERNAME' => 'your sfax username',
    'SFAX_APIKEY' => 'your sfax api key'',
    'SFAX_ENCKEY' => 'your sfax encryption key',
    'SFAX_IV' => 'your sfax init vector',
    'SFAX_CLIENT' => 'your sfax client',
];
```

You can build the $config array any way you like.

NOTE: SFAX_CLIENT is optional. You can leave it off, or leave it on, but blank.

## Sending a fax

```
// $fromFaxNumber should either be specified, or, if using the default fax number
// associated with the user account for the API, set it to the literal
// string, "DEFAULT".
$fromFaxNumber = 'DEFAULT';

// Fax number including area code. The API assumes as U.S. number, so will auto-add
// the country code of "1". The API will auto-format the number to all numbes, so
// you can leave dashes in, if you want.
$recipientFaxNumber = '555-555-5555';

// A recipient name is required. You may use free-text. If you don't have a name,
// just put the fax number in here as well.
$recipientFaxName = 'Some Company Name';

$sfax = new SfaxApi($config);
$resp = $sfax->sendFax($fromFaxNumber, $recipientFaxNumber, $recipientFaxName, $faxFilePath);

if ($resp === false) {
    echo $sfax->lastError . "\n";
} else {
    var_dump($resp);
}
```

## Test mode
You can put the API in test mode. Doing so will allow you to call our API code without actually calling Sfax's API. Instead, it will return the composed URL for the API in the $lastError field and return false before Sfax's API is called.

```
$sfax = new SfaxApi($config);
$sfax->testMode();
$resp = $sfax->sendFax($fromFaxNumber, $recipientFaxNumber, $recipientFaxName, $faxFilePath);

if ($resp === false) {
    echo $sfax->lastError . "\n";
} else {
    var_dump($resp);
}
```

## Using a named cover page
Sfax supports including a cover page via their templated cover pages, as defined in their system.

Unfortunately, it does not support sending their templated cover page alone. A PDF file must still be sent along.

The name of the coverpage defaults to "None". If the name is different than "None" (case-insensitive) then a cover page will be sent. Only parameters that are populated will be used. There isn't much logic around this, so be careful about array parameter names.

```
$sfax = new \Msogl\Sfax\SfaxApi($config);

$sfax->setCoverPage([
    'name' => 'Sfax traditional cover page',
    'subject' => 'Testing only',
    'reference' => 'Ref # here',
    'remarks' => 'This is just a test. Please ignore.',
    'fromname' => 'Your name nere',
    'fromphone' => 'Your phone nere',
    'timezone' => 'see the API specifications',
]);

$resp = $sfax->sendFax($fromFaxNumber, $recipientFaxNumber, $recipientFaxName, $pdfFile);
```

## Getting a usage report

$startDate can be null or a specific date. If null, it will automatically be 30 days prior to the current date.
$endDate can be null or a specific date. If null, it will be the current date.

```
// Get usage report for last 30 days
$sfax = new SfaxApi($config);
$resp = $sfax->usageReport(null, null);
```