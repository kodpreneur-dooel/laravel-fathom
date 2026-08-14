<?php

declare(strict_types=1);

namespace Codepreneur\Fathom;

use Codepreneur\Fathom\Authentication\Authenticator;
use Codepreneur\Fathom\Exceptions\AuthenticationException;
use Codepreneur\Fathom\Exceptions\FathomException;
use Codepreneur\Fathom\Exceptions\NotFoundException;
use Codepreneur\Fathom\Exceptions\RateLimitException;
use Codepreneur\Fathom\Exceptions\ValidationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class FathomClient
{
    public function __construct(
        protected Authenticator $authenticator,
        protected string $baseUrl,
        protected int $timeout = 30,
        protected int $retryTimes = 3,
        protected int $retrySleep = 500,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $uri, array $query = []): array
    {
        $response = $this->request()
            ->get($uri, $this->formatQuery($query));

        return $this->handleResponse($response);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function post(string $uri, array $data = []): array
    {
        $response = $this->request()
            ->post($uri, $data);

        return $this->handleResponse($response);
    }

    public function delete(string $uri): void
    {
        $response = $this->request()
            ->delete($uri);

        if ($response->status() === 204) {
            return;
        }

        $this->handleResponse($response);
    }

    protected function request(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->retry(
                $this->retryTimes,
                $this->retrySleep,
                fn (?Throwable $exception): bool => $this->shouldRetry($exception),
                throw: false
            );

        return $this->authenticator->authenticate($request);
    }

    protected function shouldRetry(?Throwable $exception): bool
    {
        if ($exception instanceof RequestException) {
            $status = $exception->response?->status();

            return $status !== null && $status >= 500;
        }

        return $exception instanceof ConnectionException;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    protected function formatQuery(array $query): array
    {
        $formatted = [];

        foreach ($query as $key => $value) {
            if (is_array($value) && str_ends_with($key, '[]') === false && ! str_contains($key, '[')) {
                $formatted[$key.'[]'] = $value;
            } else {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }

    /**
     * @return array<string, mixed>
     */
    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        throw $this->mapException($response);
    }

    protected function mapException(Response $response): FathomException
    {
        $status = $response->status();
        $message = $this->extractErrorMessage($response);

        return match (true) {
            $status === 401, $status === 403 => new AuthenticationException($message, $status, null, $response),
            $status === 404 => new NotFoundException($message, $status, null, $response),
            $status === 422 => new ValidationException($message, $status, null, $response),
            $status === 429 => new RateLimitException($message, $status, $response),
            default => new FathomException($message, $status, null, $response),
        };
    }

    protected function extractErrorMessage(Response $response): string
    {
        $body = $response->json();

        if (is_array($body) && isset($body['message'])) {
            return (string) $body['message'];
        }

        if (is_array($body) && isset($body['error'])) {
            return (string) $body['error'];
        }

        return match ($response->status()) {
            401 => 'Authentication failed. Check your API key.',
            403 => 'Access forbidden. Check your permissions.',
            404 => 'Resource not found.',
            422 => 'Validation failed.',
            429 => 'Rate limit exceeded.',
            default => 'An error occurred while communicating with the Fathom API.',
        };
    }
}
