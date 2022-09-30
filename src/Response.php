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
    protected array $results = [];

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
    public function results(): array
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
        $this->results = [
            'message' => $attributes['result'],
        ];

        if (! is_array($attributes['result'])) {
            return;
        }

        $result = $attributes['result'][0];

        if ($result['status'] !== 'OK') {
            $this->hasError = true;
        }

        $this->status = $result['status'];
        $this->time = $result['time'];
        $this->results = $result['result'] ?? ['message' => $result['detail']];
    }

    protected function parseErrorResponse(array $attributes): void
    {
        $this->hasError = true;
        $this->results = is_array($attributes['error'])
            ? $attributes['error']
            : [$attributes['error']];
        $this->status = 'error';
    }
}

