<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class FathomException extends Exception
{
    /** @var array<string, mixed> */
    protected array $body = [];

    /** @var array<string, string> */
    protected array $headers = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        ?Response $response = null,
    ) {
        parent::__construct($message, $code, $previous);

        if ($response !== null) {
            $this->body = $response->json() ?? [];
            $this->headers = $this->sanitizeHeaders($response->headers());
        }
    }

    /** @return array<string, mixed> */
    public function body(): array
    {
        return $this->body;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * @param  array<string, array<int, string>>  $headers
     * @return array<string, string>
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        $sensitive = ['authorization', 'x-api-key', 'cookie'];

        foreach ($headers as $key => $values) {
            $lowerKey = strtolower($key);

            if (in_array($lowerKey, $sensitive, true)) {
                continue;
            }

            $sanitized[$key] = $values[0] ?? '';
        }

        return $sanitized;
    }
}
