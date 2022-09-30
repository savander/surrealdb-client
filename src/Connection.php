<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient;

use JsonException;
use Savander\SurrealdbClient\Exceptions\ConnectionException;
use Savander\SurrealdbClient\Exceptions\DatabaseException;
use Throwable;
use WebSocket\Client as WebsocketClient;

class Connection
{
    /**
     * Every request that is sent to the database instance has an identity ID.
     * We don't generate it for every request since we are doing them synchronously.
     */
    protected string $id;

    /**
     * The Websocket Client that we use to create persistent connection to the SurrealDB database.
     */
    protected ?WebsocketClient $wsClient = null;

    /**
     * The host of the database.
     */
    protected string $host = 'localhost';

    /**
     * The port of the database.
     */
    protected int $port = 8000;

    /**
     * The username that will be used for authentication.
     */
    protected ?string $username = null;

    /**
     * The password that will be used for authentication.
     */
    protected ?string $password = null;

    /**
     * The name of the namespace you will be logged in to.
     */
    protected ?string $namespace = null;

    /**
     * The name of the database you will be logged in to.
     */
    protected ?string $database = null;

    /**
     * The name of the scope you will be logged in to.
     */
    protected ?string $scope = null;

    /**
     * Creates a SurrealDB Connection instance representing a connection to a database
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

        // Random ID, we don't need to generate new one for every query,
        // since we are not making any async operations.
        $this->id = uniqid('auth_', true);

        $this->connect();

        if ($this->wsClient) {
            $this->signIn($this->username, $this->password, $this->scope);
        }

        if ($this->namespace && $this->database) {
            $this->use($this->namespace, $this->database);
        }
    }

    /**
     * Send the untreated query to the database instance.
     */
    public function raw(string $query): Response
    {
        return $this->request('query', [$query]);
    }

    /**
     * It pings the database.
     */
    public function ping(): Response
    {
        return $this->request('ping');
    }

    /**
     * It switches to a specific namespace and database.
     */
    public function use(string $namespace = null, string $database = null): static
    {
        $params = array_merge(
            $namespace ? [$namespace] : [],
            $database ? [$database] : [],
        );

        $this->request('use', $params);

        return $this;
    }

    /**
     * It signs up to a specific authentication scope.
     */
    public function signUp(?string $username = null, ?string $password = null, ?string $scope = null): Response
    {
        $params = array_merge(
            $username ? ['user' => $username] : [],
            $username ? ['pass' => $password] : [],
            $scope ? ['SC' => $scope] : [],
        );

        return $this->request('signup', [$params]);
    }

    /**
     * It signs in to a specific authentication scope.
     */
    public function signIn(?string $username = null, ?string $password = null, ?string $scope = null): Response
    {
        $params = array_merge(
            $username ? ['user' => $username] : [],
            $username ? ['pass' => $password] : [],
            $scope ? ['SC' => $scope] : [],
        );

        return $this->request('signin', [$params]);
    }

    /**
     * It gets the current database instances information.
     */
    public function info(): Response
    {
        return $this->request('info');
    }

    /**
     * It closes the connection to the database
     */
    public function close(): void
    {
        $this->wsClient?->close();
    }

    /**
     * It connects to the database via Websockets.
     */
    protected function connect(): WebsocketClient
    {
        // If you don't have an instance of a Websocket Client, create one.
        if (! $this->wsClient) {
            $this->wsClient = new WebsocketClient(
                $this->buildWebsocketUrl()
            );
        }

        return $this->wsClient;
    }

    /**
     * It handles the request to the database.
     *
     * @throws \Savander\SurrealdbClient\Exceptions\ConnectionException
     * @throws \Savander\SurrealdbClient\Exceptions\DatabaseException
     */
    protected function request(string $method, string|array $params = []): Response
    {
        try {
            // Send the message to existing websocket.
            $this->wsClient()->text(
                $this->preparePayload($this->id, $method, $params)
            );

            // Parse the response
            $response = $this->parseResponse(
                $this->wsClient()->receive()
            );
        } catch (Throwable $e) {
            throw new ConnectionException($e->getMessage(), $e->getCode());
        }

        // Check if contains errors.
        if ($response->isFailed()) {
            $results = $response->results();
            throw new DatabaseException($results['message']);
        }

        return $response;
    }

    /**
     * It retrieves the websocket client that is used for database connection
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

    /**
     * It prepares the data that is sent to the database.
     * It tries to convert the data to the Json format
     */
    protected function preparePayload(string|int $id, string $method, array|string $params = []): string
    {
        return json_encode([
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ], JSON_THROW_ON_ERROR);
    }

    /**
     * It parses the response and create instance of our Response.
     *
     * @throws \Savander\SurrealdbClient\Exceptions\DatabaseException
     */
    protected function parseResponse(string $data): Response
    {
        try {
            return new Response(json_decode($data, true, 512, JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            throw new DatabaseException('The parse of the response has failed. ' . json_last_error_msg());
        }
    }

    /**
     * Disconnect from the websockets when the client is destroyed.
     */
    public function __destruct()
    {
        $this->close();
    }
}
