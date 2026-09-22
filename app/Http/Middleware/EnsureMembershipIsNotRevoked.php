<?php

namespace App\Http\Middleware;

use App\Enums\MembershipStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a revoked member from the notice board and document library —
 * unlike an expired or pending membership, revocation means access to
 * member-only content itself was withdrawn, not just renewal-gated.
 */
class EnsureMembershipIsNotRevoked
{
    public function handle(Request $request, Closure $next): Response
    {
        $membership = $request->user()?->currentMembership();

        abort_if($membership?->status === MembershipStatus::Revoked, 403, 'Your ICEN membership has been revoked. Please contact ICEN support for assistance.');

        return $next($request);
    }
}
