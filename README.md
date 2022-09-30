# SurealDB Client

<!--
[![Latest Version on Packagist](https://img.shields.io/packagist/v/savander/surrealdb-client.svg?style=flat-square)](https://packagist.org/packages/savander/surrealdb-client)
[![Total Downloads](https://img.shields.io/packagist/dt/savander/surrealdb-client.svg?style=flat-square)](https://packagist.org/packages/savander/surrealdb-client)
![GitHub Actions](https://github.com/savander/surrealdb-client/actions/workflows/main.yml/badge.svg)
-->

> # ⚠️ Warning! 
> The package is in the very early stages of development!

<br>

The client allows you to connect to SurrealDB and perform queries.


## Installation

You can install the package via composer:

```bash
composer require savander/surrealdb-client
```

## Usage

```php
// The connection options.
$connectionOptions = (new ConnectionOptions())
    ->setNamespace('test')
    ->setDatabase('test')
    ->setUsername(getenv('DB_USER'))
    ->setPassword(getenv('DB_PASS'));

// The persistent connection to the SurrealDB Websocket server.
$connection = new Connection($connectionOptions);

// The results of the query. It returns the Johnny :)
$createdJohnny = $connection
    ->raw("CREATE author SET name.first = 'Johnny'")
    ->results();

// The results of the selection query, it returns the previously created Johnny.
//  > Please note that, we used `prepare` method here. It's much safer to do this that way.
//  > In this case, we are sending attributes as a separate array of arguments.
//  > You could use that method in previous step as well.
$selectedJohnny = $connection
    ->prepare('SELECT * FROM $author', ['author' => $createdJohnny['id']])
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
