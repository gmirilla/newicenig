<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentProofController extends Controller
{
    public function show(Request $request, Payment $payment): BinaryFileResponse
    {
        abort_unless($request->user()?->hasAnyRole(User::PANEL_ROLES), 403);

        $media = $payment->getFirstMedia('proof_of_payment');

        abort_unless($media, 404);

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
