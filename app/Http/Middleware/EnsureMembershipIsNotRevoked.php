<?php

namespace App\Http\Middleware;

use App\Enums\MembershipStatus;
use App\Settings\BrandingSettings;
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
        $brandShortName = app(BrandingSettings::class)->brand_short_name;

        abort_if($membership?->status === MembershipStatus::Revoked, 403, "Your {$brandShortName} membership has been revoked. Please contact {$brandShortName} support for assistance.");

        return $next($request);
    }
}
