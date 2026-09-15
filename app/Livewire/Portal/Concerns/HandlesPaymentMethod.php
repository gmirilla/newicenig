<?php

namespace App\Livewire\Portal\Concerns;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Paystack\PaystackClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared by every Livewire flow that collects a payment (registration wizard,
 * renewal, change-level application) so the Paystack vs. bank-transfer choice
 * — and the bank-transfer proof-of-payment upload — is implemented once.
 */
trait HandlesPaymentMethod
{
    public string $paymentMethod = 'paystack';

    public ?int $bankAccountId = null;

    public $proofOfPayment = null;

    public function getBankAccountsProperty()
    {
        return BankAccount::where('is_active', true)->orderBy('sort_order')->get();
    }

    protected function validatePaymentMethodFields(): void
    {
        $this->validate([
            'paymentMethod' => ['required', 'in:paystack,bank_transfer'],
            'bankAccountId' => ['required_if:paymentMethod,bank_transfer', 'nullable', 'exists:bank_accounts,id'],
            'proofOfPayment' => ['required_if:paymentMethod,bank_transfer', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
    }

    /**
     * Create the Payment record for the chosen method. For bank transfer this
     * immediately attaches the uploaded proof and marks it pending staff
     * verification; for Paystack it's left pending until the gateway redirect
     * completes.
     */
    protected function createPayment(Model $payable, float $amount, string $currency): Payment
    {
        $this->validatePaymentMethodFields();

        $isBankTransfer = $this->paymentMethod === 'bank_transfer';

        $gateway = PaymentGateway::firstOrCreate(
            ['slug' => $isBankTransfer ? 'bank_transfer' : 'paystack'],
            ['name' => $isBankTransfer ? 'Bank Transfer' : 'Paystack', 'is_active' => true],
        );

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'payment_gateway_id' => $gateway->id,
            'payment_method' => $isBankTransfer ? PaymentMethod::BankTransfer : PaymentMethod::Paystack,
            'bank_account_id' => $isBankTransfer ? $this->bankAccountId : null,
            'payable_id' => $payable->id,
            'payable_type' => $payable::class,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => Payment::generateReference(),
            'status' => $isBankTransfer ? PaymentStatus::PendingVerification : PaymentStatus::Pending,
        ]);

        if ($isBankTransfer) {
            $payment->addMedia($this->proofOfPayment->getRealPath())
                ->usingFileName($this->proofOfPayment->getClientOriginalName())
                ->toMediaCollection('proof_of_payment');
        }

        return $payment;
    }

    /**
     * Send the member to the right place after a payment is created: a
     * Paystack checkout redirect, or straight to the "pending verification"
     * confirmation page for a bank transfer.
     *
     * @param  array<string, mixed>  $paystackMetadata
     */
    protected function redirectForPayment(Payment $payment, string $email, array $paystackMetadata = [])
    {
        if ($payment->payment_method === PaymentMethod::BankTransfer) {
            return $this->redirect(route('payments.success', ['payment' => $payment->id]), navigate: false);
        }

        $paystack = app(PaystackClient::class);

        $response = $paystack->initializeTransaction(
            email: $email,
            amountInKobo: (int) round((float) $payment->amount * 100),
            reference: $payment->reference,
            callbackUrl: route('payments.callback'),
            metadata: $paystackMetadata,
        );

        return $this->redirect($response['data']['authorization_url'], navigate: false);
    }
}
