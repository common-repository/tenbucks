# TenbucksRegistrationClient
Send API keys TenBucks API server

# Usage
```php
// Client
$client = new TenbucksRegistrationClient();

// Data
$opts = array(
    'email' => 'joe.doe@example.org',
    'sponsor' => 'jane.doe@example.org', // optionnal
    'company' => 'My company name',
    'platform' => 'WooCommerce',
    'locale' => 'fr',
    'country' => 'FR',
    'url' => 'http://localhost',
    'credentials' => array(
        'key'    => md5('test_key'), // key
        'secret' => md5('test_secret'), // secret
    )
);

$query = $client->send($opts);
$success = array_key_exists('success', $query) && (bool)$query['success'];
if ($success) {
    // success
} else {
    // Error
}
```

# test
```bash
$ phpunit --bootstrap src/TenbucksRegistrationClient.php tests/TenbucksRegistrationClientTest
```
