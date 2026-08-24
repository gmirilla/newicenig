<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMembershipIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $membership = $request->user()?->currentMembership();

        abort_unless($membership?->isActive(), 403, 'An active membership is required to access this page.');

        return $next($request);
    }
}
