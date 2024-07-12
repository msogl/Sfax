# SfaxApi

PHP API for sending a fax via Sfax.

Based on the documentation at https://sfax.scrypt.com/category/1309-getting-started.

The PHP code sample there is outdated and uses the old mcrypt libraries in PHP. This uses OpenSSL.

## Install
```
composer require msogl\sfax
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

## Getting a usage report

$startDate can be null or a specific date. If null, it will automatically be 30 days prior to the current date.
$endDate can be null or a specific date. If null, it will be the current date.

```
// Get usage report for last 30 days
$sfax = new SfaxApi($config);
$resp = $sfax->usageReport(null, null);
```