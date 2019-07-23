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
+--------------+----------------+---------------------------+-------------------+
| Files (1)    | Defined (4)    | Depending on default (1)  | Undefined (2)     |
+--------------+----------------+---------------------------+-------------------+
| database.php | DB_CONNECTION  | -                         | -                 |
| -            | -              | -                         | DATABASE_URL      |
| -            | DB_DATABASE    | -                         | -                 |
| -            | -              | DB_FOREIGN_KEYS           | -                 |
| -            | -              | -                         | DATABASE_URL      |
| -            | DB_HOST        | -                         | -                 |
| -            | DB_PORT        | -                         | -                 |
+--------------+----------------+---------------------------+-------------------+
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
2 used environmental variables are undefined:
DB_HOST
DB_PORT

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
  "files" => 1
  "defined" => 1
  "undefined" => 0
  "depending_on_default" => 0,
  "columns" => [
    0 => [
      "filename" => "database.php"
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
