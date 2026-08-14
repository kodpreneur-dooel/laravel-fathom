<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Http\Middleware;

use Closure;
use Codepreneur\Fathom\Facades\Fathom;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFathomWebhook
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Fathom::webhooks()->verify($request)) {
            abort(403, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
