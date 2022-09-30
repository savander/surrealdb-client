<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient;

use Savander\SurrealdbClient\Exceptions\AuthenticationException;
use Savander\SurrealdbClient\Exceptions\ConnectionException;
use stdClass;
use Throwable;
use WebSocket\Client as WebsocketClient;

class Connection
{
    protected string $id;

    protected ?WebsocketClient $wsClient = null;

    protected string $host;

    protected int $port;

    protected ?string $username = null;

    protected ?string $password = null;

    protected ?string $namespace = null;

    protected ?string $database = null;

    protected ?string $scope = null;

    /**
     * Creates a SurrealDB Client instance representing a connection to a database
     */
    public function __construct(protected ConnectionOptions $connectionOptions)
    {
        $options = $this->connectionOptions->toArray();

        // Basic connection
        $this->host = $options['host'];
        $this->port = $options['port'];

        // Set namespace, database and scope
        $this->namespace = $options['namespace'];
        $this->database = $options['database'];
        $this->scope = $options['scope'];

        // Authorization
        $this->username = $options['username'];
        $this->password = $options['password'];

        $this->id = uniqid('auth_', true);

        $this->connect();

        if ($this->wsClient) {
            $this->signIn();
        }

        if ($this->namespace && $this->database) {
            $this->use($this->namespace, $this->database, $this->scope);
        }
    }

    /**
     * Use the namespace and database for queries.
     */
    public function use(string $namespace = null, string $database = null, ?string $scope = null): static
    {
        $params = array_merge(
            $namespace ? [$namespace] : [],
            $database ? [$database] : [],
            $scope ? [$scope] : [],
        );

        $this->request('use', $params);

        return $this;
    }

    public function raw(string $query): stdClass
    {
        return $this->request('query', [$query]);
    }

    protected function connect(): WebsocketClient
    {
        $this->wsClient = new WebsocketClient(
            $this->buildWebsocketUrl()
        );

        return $this->wsClient;
    }

    protected function signIn(): void
    {
        $params = array_merge(
            $this->username ? ['user' => $this->username] : [],
            $this->username ? ['pass' => $this->password] : [],
        );

        $this->request('signin', [$params]);
    }

    protected function request(string $method, string|array $params): stdClass
    {
        try {
            $this->wsClient()->text(
                $this->preparePayload($this->id, $method, $params)
            );

            $results = $this->parseResponse(
                $this->wsClient()->receive()
            );

            if (property_exists($results, 'error')) {
                throw match ($results->error->code) {
                    -32000 => new AuthenticationException($results->error->message),
                    default => new ConnectionException($results->error->message, $results->error->code),
                };
            }

            return $results;
        } catch (Throwable $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Creates WsClient that is used to connect to the database.
     */
    protected function wsClient(): WebsocketClient
    {
        return $this->wsClient ?: $this->connect();
    }

    /**
     * Build the Websocket URL that will be used to connect to database.
     */
    protected function buildWebsocketUrl(): string
    {
        return "ws://$this->host:$this->port/rpc";
    }

    protected function preparePayload(string|int $id, string $method, array|string $params = []): string
    {
        return json_encode([
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ], JSON_THROW_ON_ERROR);
    }

    protected function parseResponse(string $data): stdClass
    {
        return json_decode($data, false, 512, JSON_THROW_ON_ERROR);
    }

    public function __destruct()
    {
        $this->wsClient?->close();
    }
}
