<?php

namespace TelegramBot\Http;

use Exception;
use stdClass;

final readonly class Response
{

    public function __construct(
        private int    $statusCode,
        private array  $headers,
        private string $body
    )
    {}

    public function status(): int
    {
        return $this->statusCode;
    }

    public function successful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function ok(): bool
    {
        return $this->statusCode === 200;
    }

    public function failed(): bool
    {
        return $this->statusCode >= 400;
    }

    public function clientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    public function serverError(): bool
    {
        return $this->statusCode >= 500;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $key): ?string
    {
        return $this->headers[strtolower($key)] ?? null;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function json(): array
    {
        return json_decode($this->body, true) ?? [];
    }

    public function object(): ?stdClass
    {
        return json_decode($this->body);
    }

    /**
     * @throws Exception
     */
    public function throw(): self
    {
        if ($this->failed()) {
            throw new Exception("HTTP request failed with status {$this->statusCode}: {$this->body}");
        }
        return $this;
    }

    public function onError(callable $callback): self
    {
        if ($this->failed()) {
            $callback($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->body;
    }
}