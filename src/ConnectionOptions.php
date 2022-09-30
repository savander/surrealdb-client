<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient;

use JetBrains\PhpStorm\ArrayShape;

class ConnectionOptions
{
    /**
     * The available options for this Data Transfer Object.
     */
    protected array $options = [
        'host' => 'localhost',
        'port' => 8000,
        'username' => '',
        'password' => '',
        'namespace' => null,
        'database' => null,
        'scope' => null,
    ];

    #[ArrayShape([
        'host' => 'string',
        'port' => 'int',
        'username' => 'string',
        'password' => 'string',
        'namespace' => 'string|null',
        'database' => 'string|null',
        'scope' => 'string|null',
    ])]
    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Set the host of the database.
     */
    public function setHost(string $host): static
    {
        $this->options['host'] = $host;

        return $this;
    }

    /**
     * Set the port of the database.
     */
    public function setPort(int $port): static
    {
        $this->options['port'] = $port;

        return $this;
    }

    /**
     * Set the username that will be used for authentication.
     */
    public function setUsername(?string $username): static
    {
        $this->options['username'] = $username;

        return $this;
    }

    /**
     * Set the password that will be used for authentication.
     */
    public function setPassword(?string $password): static
    {
        $this->options['password'] = $password;

        return $this;
    }

    /**
     * Set the name of the scope you will be logged in to.
     */
    public function setScope(string $scope): static
    {
        $this->options['scope'] = $scope;

        return $this;
    }
}
