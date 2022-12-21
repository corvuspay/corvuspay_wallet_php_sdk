# Installation

After downloading the project, add it as a dependancy to your application.

```php
require_once('/path/to/init.php');
```
or use composer

Composer is a package manager for PHP. In the composer.json file in your project add:
```json
{
  "require": {
    "corvuspay/corvuspay_wallet_php_sdk": "*"
  },
  "repositories": [
    {
      "type": "git",
      "url":  "https://github.com/corvuspay/corvuspay_wallet_php_sdk.git"
    }
  ]
}
```

And then in the file where you want to use the library write:
```php
require_once('/path/to/init.php');
```

# Dependencies
The bindings require the following extensions in order to work properly:

- ext-mbstring
- ext-json
- ext-openssl
- ext-posix
- ext-curl
- psr/log
- ext-simplexml
- monolog/monolog

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to
make sure that these extensions are available.

# Getting Started
Simple usage for basic checkout looks like:

```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$params = [
    'order_number'     => "order_number",
    'language'         => "language",
    'currency'         => "currency",
    'amount'           => "amount",
    'cart'             => "cart",
    'require_complete' => "require_complete"
];
$client->checkout->create($params, 'auto');   
```

Optional parameters are: 
- cardholder_name
- cardholder_surname
- cardholder_address
- cardholder_city
- cardholder_zip_code
- cardholder_country
- cardholder_phone
- cardholder_email
- subscription
- number_of_installments
- payment_all
- payment_all_dynamic
- payment_amex
- payment_diners
- payment_dina
- payment_visa
- payment_master
- payment_maestro
- payment_discover
- payment_jcb
- installments_map
- cc_type
- cardholder_country_code
- hide_tabs
- creditor_reference
- debtor_iban
- best_before
- discount_amount

## Example for cancelling a preauthorized transaction
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number"
    ]; 
$client->transaction->cancel($params);
```

## Example for completing a preauthorized transaction
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number"
    ]; 
// or if you are completing a transaction with subscription
$params = [
    'order_number' => "order_number",
    'subscription' => "true",
    'account_id'   => "account_id"
    ]; 
$client->transaction->complete($params);
```

## Example for partially completing a preauthorized transaction
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number",
    'new_amount'   => "new_amount",
    'currency'     => "currency"
    ];
$client->transaction->partiallyComplete($params);
```

## Example for refunding a transaction
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number"
    ];
$client->transaction->refund($params);
```

## Example for partially refunding a transaction
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number",
    'new_amount'   => "new_amount",
    'currency'     => "currency"
    ];
$client->transaction->partiallyRefund($params);
```

## Example for charging the next subscription payment
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number",
    'account_id'   => "account_id"
    ];
// or if you want to pay the order with new amount
$params = [
     'order_number' => "order_number",
     'account_id'   => "account_id",
     'new_amount'   => "new_amount",
     'currency'     => "currency"
     ];
$client->subscription->pay($params);
```

## Example for checking transaction status
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number'    => "order_number",
    'currency_code'   => "currency_code"
    ];
$client->transaction->status($params);
```

## Example for checking PIS transaction status
```php
$config = ['store_id' => "store_id", 'secret_key' => "secret_key", 'environment' => "environment"];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number'    => "order_number",
    'currency_code'   => "currency_code"
    ];
$client->pisTransaction->status($params);
```

# Configuring a Logger
It can be configured with a PSR-3 compatible logger for example with MonologLogger:

```php
$logger = new Monolog\Logger('Test');
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/app.log', Monolog\Logger::DEBUG));
$config = [
    'store_id'    => "store_id", 
    'secret_key'  => "secret_key", 
    'environment' => "environment",
    'logger'      =>  $logger
    ];
$client = new CorvusPay\CorvusPayClient($config);
$fp = fopen("path/to/file", 'r');
$client->setCertificate($fp, "certificate_password");
$params = [
    'order_number' => "order_number"
    ];
$client->transaction->refund($params);
```
# Images
The images used in this library can be found on the [official CorvusPay website](https://cps.corvuspay.com/img/plugins/).

# Documentation
See the [integration manual](https://cps.corvus.hr/public/corvuspay/) for more information.
