<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\HandleSuccessfulPayment;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\Paystack\PaystackClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaystackClient $paystack,
        protected HandleSuccessfulPayment $handleSuccessfulPayment,
    ) {}

    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        $payment = Payment::where('reference', $reference)->firstOrFail();

        $newUser = $this->verifyAndApply($payment);

        // $newUser is only ever set for an account created fresh by this
        // payment (never one matched by email to a pre-existing account —
        // see HandleSuccessfulPayment::linkAccount()), so it's safe to log
        // straight in. Guarded on ! Auth::check() so we never clobber an
        // already-authenticated browser session (e.g. a logged-in member
        // paying in one tab while signed in as someone else in another).
        if ($newUser && ! Auth::check()) {
            return $this->loginAndRedirectToDashboard($newUser);
        }

        return redirect()->route('payments.success', ['payment' => $payment->id]);
    }

    protected function loginAndRedirectToDashboard(User $user): RedirectResponse
    {
        Auth::login($user);
        Session::regenerate();

        // They proved control of this email by completing payment on an
        // application/renewal submitted with it — the same trust level the
        // set-password claim link already relies on (see SetPasswordController).
        $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

        return redirect()->to($user->defaultDashboardUrl());
    }

    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $reference = $request->input('data.reference');

        $payment = Payment::where('reference', $reference)->first();

        if ($payment) {
            $this->verifyAndApply($payment);
        }

        return response('', 200);
    }

    public function success(Request $request): View
    {
        $payment = Payment::findOrFail($request->query('payment'));

        return view('payments.success', ['payment' => $payment]);
    }

    protected function verifyAndApply(Payment $payment): ?User
    {
        $result = $this->paystack->verifyTransaction($payment->reference);

        $data = $result['data'] ?? [];

        if (($data['status'] ?? null) === 'success') {
            return $this->handleSuccessfulPayment->handle($payment, $data);
        }

        return null;
    }
}
