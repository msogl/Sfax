# SfaxApi

PHP API for sending a fax via Sfax

## Sending a fax

```
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