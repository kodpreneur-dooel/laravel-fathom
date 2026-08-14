<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Authentication;

use Illuminate\Http\Client\PendingRequest;

class ApiKeyAuthenticator implements Authenticator
{
    public function __construct(
        protected string $apiKey,
    ) {}

    public function authenticate(PendingRequest $request): PendingRequest
    {
        return $request->withHeaders([
            'X-Api-Key' => $this->apiKey,
        ]);
    }
}
