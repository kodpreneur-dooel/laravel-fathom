<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Authentication;

use Illuminate\Http\Client\PendingRequest;

interface Authenticator
{
    public function authenticate(PendingRequest $request): PendingRequest;
}
