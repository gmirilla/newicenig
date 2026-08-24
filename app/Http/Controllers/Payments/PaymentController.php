<?php

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\HandleSuccessfulPayment;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Paystack\PaystackClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        $this->verifyAndApply($payment);

        return redirect()->route('payments.success', ['payment' => $payment->id]);
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

    protected function verifyAndApply(Payment $payment): void
    {
        $result = $this->paystack->verifyTransaction($payment->reference);

        $data = $result['data'] ?? [];

        if (($data['status'] ?? null) === 'success') {
            $this->handleSuccessfulPayment->handle($payment, $data);
        }
    }
}
