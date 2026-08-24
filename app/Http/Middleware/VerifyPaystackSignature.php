<?php

namespace App\Http\Middleware;

use App\Services\Paystack\PaystackClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPaystackSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = app(PaystackClient::class);

        $signature = $request->header('x-paystack-signature');

        if (! $client->verifyWebhookSignature($request->getContent(), $signature)) {
            abort(400, 'Invalid Paystack webhook signature.');
        }

        return $next($request);
    }
}
