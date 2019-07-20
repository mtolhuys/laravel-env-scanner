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
| Files (1)    | Has value (4)  | Depending on default (1)  | No value (2)      |
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

Optionally you could specify a directory to run from:

```bash
php artisan env:scan -d app/Http/Controllers
```

Additionally you can use the `LaravelEnvScanner` from anywhere you want:
```php
(new LaravelEnvScanner(__DIR__))->scan()->results;

// Or

$this->scanner = new LaravelEnvScanner(__DIR__);
$this->scanner->scan();
$this->scanner->results;

// Example results
[
  "files" => 1
  "empty" => 0
  "has_value" => 1
  "depending_on_default" => 0
  "data" => [
    0 => [
      "filename" => "database.php"
      "has_value" => "DB_HOST"
      "depending_on_default" => "-"
      "empty" => "-"
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

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
