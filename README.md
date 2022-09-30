# SurealDB Client

<!--
[![Latest Version on Packagist](https://img.shields.io/packagist/v/savander/surrealdb-client.svg?style=flat-square)](https://packagist.org/packages/savander/surrealdb-client)
[![Total Downloads](https://img.shields.io/packagist/dt/savander/surrealdb-client.svg?style=flat-square)](https://packagist.org/packages/savander/surrealdb-client)
![GitHub Actions](https://github.com/savander/surrealdb-client/actions/workflows/main.yml/badge.svg)
-->

> # ⚠️ Caution! 
> The package is in the very early development phase and is not yet useful for anything other than testing.

<br>

The client allows you to connect to SurrealDB and perform queries.


## Installation

<!--
You can install the package via composer:

```bash
composer require savander/surrealdb-client
```
-->

## Usage

```php
$connectionOptions = new ConnectionOptions();

$connectionOptions
    ->setUsername('root')
    ->setPassword('root')
    ->setNamespace('test')
    ->setDatabase('test');

$connection = new Connection($connectionOptions);

$results = $connection->raw('SELECT * FROM account;');
```

<!--
### Testing

```bash
composer test
```
-->

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
