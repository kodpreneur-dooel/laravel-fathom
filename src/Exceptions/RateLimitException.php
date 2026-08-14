<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Exceptions;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;

class RateLimitException extends FathomException
{
    protected ?int $limit = null;

    protected ?int $remaining = null;

    protected ?int $reset = null;

    protected ?int $retryAfter = null;

    public function __construct(
        string $message = 'Rate limit exceeded.',
        int $code = 429,
        ?Response $response = null,
    ) {
        parent::__construct($message, $code, null, $response);

        if ($response !== null) {
            $this->limit = $this->parseHeaderInt($response, 'RateLimit-Limit');
            $this->remaining = $this->parseHeaderInt($response, 'RateLimit-Remaining');
            $this->reset = $this->parseHeaderInt($response, 'RateLimit-Reset');
            $this->retryAfter = $this->parseHeaderInt($response, 'Retry-After');
        }
    }

    public function limit(): ?int
    {
        return $this->limit;
    }

    public function remaining(): ?int
    {
        return $this->remaining;
    }

    public function reset(): ?int
    {
        return $this->reset;
    }

    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function resetAt(): ?CarbonImmutable
    {
        if ($this->reset === null) {
            return null;
        }

        return CarbonImmutable::now()->addSeconds($this->reset);
    }

    protected function parseHeaderInt(Response $response, string $header): ?int
    {
        $value = $response->header($header);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
