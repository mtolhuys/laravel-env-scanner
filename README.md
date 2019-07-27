# Laravel environmental variable scanner

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mtolhuys/laravel-env-scanner.svg?style=flat-square)](https://packagist.org/packages/mtolhuys/laravel-env-scanner)
[![Build Status](https://img.shields.io/travis/mtolhuys/laravel-env-scanner/master.svg?style=flat-square)](https://travis-ci.org/mtolhuys/laravel-env-scanner)
[![Quality Score](https://img.shields.io/scrutinizer/g/mtolhuys/laravel-env-scanner.svg?style=flat-square)](https://scrutinizer-ci.com/g/mtolhuys/laravel-env-scanner)
[![Total Downloads](https://img.shields.io/packagist/dt/mtolhuys/laravel-env-scanner.svg?style=flat-square)](https://packagist.org/packages/mtolhuys/laravel-env-scanner)

This package comes with a `LaravelEnvScanner` class and artisan command which you can use to scan any folder in your app for potential .env related problems. 

Example output of the command:

```bash
$ php artisan env:scan         
Scanning: laravel-app/config...
+------------------------------------+----------------+---------------------------+-------------------+
| Locations (2)                      | Defined (1)    | Depending on default (1)  | Undefined (0)     |
+------------------------------------+----------------+---------------------------+-------------------+
| laravel-app/config/database.php:36 | DB_CONNECTION  | -                         | -                 |
| laravel-app/config/database.php:42 | -              | DB_HOST                   | -                 |
+------------------------------------+----------------+---------------------------+-------------------+
```

## Installation

You can install the package via composer:

```bash
composer require mtolhuys/laravel-env-scanner
```

## Usage
You can call the artisan command to start the scan:

```bash
php artisan env:scan
```

Optionally you could specify a directory to run from (defaults to `config_path()`):

```bash
php artisan env:scan -d app/Http/Controllers
Scanning: app/Http/Controllers...
```

Or only look for undefined variables:

```bash
php artisan env:scan -u
Scanning: laravel-app/config...
+-------------------------------+----------+
| laravel-app/config/app.php:16 | APP_NAME |
| laravel-app/config/app.php:29 | APP_ENV  |
+-------------------------------+----------+

php artisan env:scan -u -d app
Scanning: app...
Warning: env("RISKY_".$behavior) found in app/Http/Middleware/Authenticate.php

php artisan env:scan -u -d storage
Scanning: storage...
Looking good!
```

Aside from the command you can use the `LaravelEnvScanner` from anywhere you want:
```php
(new LaravelEnvScanner(__DIR__))->scan()->results;

// Or

$this->scanner = new LaravelEnvScanner(__DIR__);
$this->scanner->scan();
$this->scanner->results;

// Example results
[
  "locations" => 1
  "defined" => 1
  "undefined" => 0
  "depending_on_default" => 0,
  "columns" => [
    0 => [
      "location" => "laravel-app/config/database.php:36"
      "defined" => "DB_HOST"
      "depending_on_default" => "-"
      "undefined" => "-"
    ]
  ]
]
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mtolhuys@protonmail.com instead of using the issue tracker.

## Credits

- [Maarten Tolhuijs](https://github.com/mtolhuys)
- [All Contributors](../../contributors)
- [Beyond Code](https://github.com/beyondcode) For the [boilerplate](https://laravelpackageboilerplate.com/) and having me come up with the idea for this package.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
