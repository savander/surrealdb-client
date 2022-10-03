<?php

declare(strict_types=1);

namespace Savander\SurrealdbClient;

class Response
{
    /**
     * The original attributes.
     */
    protected array $original;

    /**
     * The id of the response.
     */
    protected ?string $id = null;

    /**
     * The status of the response.
     */
    protected ?string $status = null;

    /**
     * The time of the response.
     */
    protected ?string $time = null;

    /**
     * The results from the response.
     */
    protected mixed $results = null;

    /**
     * The error message.
     */
    protected ?string $errorMessage = null;

    /**
     * Whether the response contains error.
     */
    protected bool $hasError = false;

    public function __construct(array $attributes)
    {
        $this->original = $attributes;

        $this->id = $attributes['id'] ?? null;

        array_key_exists('error', $attributes)
            ? $this->parseErrorResponse($attributes)
            : $this->parseSuccessResponse($attributes);
    }

    /**
     * Get the ID of the response.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Get the results of the response.
     */
    public function results(): mixed
    {
        return $this->results;
    }

    /**
     * Get the status of the response.
     */
    public function status(): ?string
    {
        return $this->status;
    }

    /**
     * Get the request time of the response.
     */
    public function time(): ?string
    {
        return $this->time;
    }

    /**
     * Get the original response.
     */
    public function original(): array
    {
        return $this->original;
    }

    /**
     * Check whether the response is an error one.
     */
    public function isSuccessful(): bool
    {
        return ! $this->hasError;
    }

    /**
     * Check whether the response is an error one.
     */
    public function isFailed(): bool
    {
        return $this->hasError;
    }

    /**
     * Get the error message.
     */
    public function errorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Transform the response to the array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'results' => $this->results,
            'status' => $this->status,
            'time' => $this->time,
        ];
    }

    protected function parseSuccessResponse(array $attributes): void
    {
        // Whatever it can be...
        $this->results = $attributes['result'];

        if (! is_array($attributes['result'])) {
            return;
        }

        $result = $attributes['result'][0];

        $this->status = $result['status'];
        $this->time = $result['time'];
        $this->results = $result['result'] ?? [];

        if ($result['status'] !== 'OK') {
            $this->setError($result['detail']);
        }
    }

    protected function parseErrorResponse(array $attributes): void
    {
        $this->setError(
            is_array($attributes['error'])
                ? $attributes['error']['message']
                : $attributes['error']
        );
    }

    /**
     * The method that will transform the response to the error one.
     */
    protected function setError(string $message): void
    {
        $this->hasError = true;
        $this->errorMessage = $message;
        $this->status = 'error';
    }
}

