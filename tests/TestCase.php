<?php

namespace Savander\SurrealdbClient\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Savander\SurrealdbClient\Connection;
use Savander\SurrealdbClient\ConnectionOptions;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Clear database before test.
        $this->clearDatabase();
    }

    /**
     * It makes a database connection.
     */
    protected function makeDatabaseConnection(?ConnectionOptions $options = null): Connection
    {
        $options = $options ?: (new ConnectionOptions())
            ->setHost(getenv('DB_HOST'))
            ->setPort(getenv('DB_PORT'))
            ->setNamespace(getenv('DB_NAMESPACE'))
            ->setDatabase(getenv('DB_NAME'))
            ->setUsername(getenv('DB_USER'))
            ->setPassword(getenv('DB_PASS'));

        return new Connection($options);
    }

    /**
     * Since we want to make new connection for each test,
     * we will connect here, clear database and close connection.
     */
    protected function clearDatabase(): void
    {
        $connection = $this->makeDatabaseConnection();

        $database = getenv('DB_NAME');

        $connection->raw("REMOVE DATABASE $database");

        $connection->close();
    }
}
