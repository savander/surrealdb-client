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
// The connection options.
$connectionOptions = (new ConnectionOptions())
    ->setUsername(getenv('DB_USER'))
    ->setPassword(getenv('DB_PASS'));

// The persistent connection to the SurrealDB Websocket server.
$connection = new Connection($connectionOptions);

// The results of the query. It returns the Johnny :)
$createdJohnny = $connection
    ->use('test', 'test')
    ->raw("CREATE author SET name.first = 'Johnny'")
    ->results();

// THe results of the selection query, it returns the previously created Johnny.
$selectedJohnny = $connection
    ->use('test', 'test')
    ->raw("SELECT * FROM $createdJohnny[id]")
    ->results();
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
