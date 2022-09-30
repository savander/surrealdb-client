<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient;

use JetBrains\PhpStorm\ArrayShape;

class ConnectionOptions
{
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

    public function setHost(string $host): static
    {
        $this->options['host'] = $host;

        return $this;
    }

    public function setPort(int $port): static
    {
        $this->options['port'] = $port;

        return $this;
    }

    public function setUsername(?string $username): static
    {
        $this->options['username'] = $username;

        return $this;
    }

    public function setPassword(?string $password): static
    {
        $this->options['password'] = $password;

        return $this;
    }

    public function setNamespace(string $namespace): static
    {
        $this->options['namespace'] = $namespace;

        return $this;
    }

    public function setDatabase(string $database): static
    {
        $this->options['database'] = $database;

        return $this;
    }

    public function setScope(string $scope): static
    {
        $this->options['scope'] = $scope;

        return $this;
    }
}
