<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient;

use Savander\SurrealdbClient\Exceptions\AuthenticationException;
use Savander\SurrealdbClient\Exceptions\ConnectionException;
use Savander\SurrealdbClient\Exceptions\InvalidQueryException;
use Savander\SurrealdbClient\Exceptions\NotFoundException;
use Savander\SurrealdbClient\Exceptions\PayloadToLargeException;
use Savander\SurrealdbClient\Exceptions\RequiredOptionArgumentMissingException;
use Savander\SurrealdbClient\Exceptions\UnsupportedMediaTypeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Connection
{
    protected ?HttpClientInterface $httpClient = null;

    protected string $host;

    protected int $port;

    protected ?string $username;

    protected ?string $password;

    protected string $namespace;

    protected string $database;

    /**
     * Creates a SurrealDB Client instance representing a connection to a database
     */
    public function __construct(protected ConnectionOptions $connectionOptions)
    {
        $options = $this->connectionOptions->toArray();

        // Basic connection
        $this->host = $options['host'];
        $this->port = $options['port'];

        // Setting initial namespace and database
        $this->namespace = $options['namespace'] ?: throw new RequiredOptionArgumentMissingException('namespace');
        $this->database = $options['database'] ?: throw new RequiredOptionArgumentMissingException('database');

        // Authorization
        $this->username = $options['username'];
        $this->password = $options['password'];
    }

    /**
     * Execute the query on SurrealDB Database.
     */
    public function raw(string $query): array
    {
        return $this->request($query);
    }

    /**
     * Make a request to the SurrealDB Database and handle the outcome.
     */
    protected function request(string $data, array $headers = []): array
    {
        $options = new HttpOptions();

        $options
            ->setAuthBasic($this->username, $this->password)
            ->setHeaders(
                array_merge(
                    [
                        'Content-Type: application/json',
                        'Accept: application/json',
                        "NS: $this->namespace",
                        "DB: $this->database",
                    ],
                    $headers
                )
            )
            ->setBody($data);

        try {
            return $this
                ->httpClient()
                ->request('POST', $this->buildRequestUrl(), $options->toArray())
                ->toArray();
        } catch (ClientExceptionInterface $e) {
            $this->handleHttpClientException($e);
        } catch (\Throwable $e) {
            throw new ConnectionException($e->getMessage());
        }
    }

    /**
     * Creates HttpClient that is used to connect to the database.
     */
    protected function httpClient(): HttpClientInterface
    {
        if (! $this->httpClient) {
            $this->httpClient = HttpClient::create(
                $this->connectionOptions->toArray()['http_client_options']
            );
        }

        return $this->httpClient;
    }

    /**
     * Build the request URL that will be used to connect to database.
     */
    protected function buildRequestUrl(string $uri = 'sql'): string
    {
        return "http://$this->host:$this->port/$uri";
    }

    protected function handleHttpClientException(ClientExceptionInterface $exception): never
    {
        throw match ($exception->getResponse()->getInfo()['http_code']) {
            400, 501 => new InvalidQueryException($exception->getMessage()),
            403 => new AuthenticationException($exception->getMessage()),
            404 => new NotFoundException($exception->getMessage()),
            413 => new PayloadToLargeException($exception->getMessage()),
            415 => new UnsupportedMediaTypeException($exception->getMessage()),
            default => new ConnectionException($exception->getMessage())
        };
    }
}
