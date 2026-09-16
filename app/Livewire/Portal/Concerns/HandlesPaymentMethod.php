<?php

namespace App\Livewire\Portal\Concerns;

use App\Actions\Payments\HandleBankTransferSubmission;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\BankAccount;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\Paystack\PaystackClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared by every Livewire flow that collects a payment (registration wizard,
 * renewal, change-level application) so the Paystack vs. bank-transfer choice
 * — and the bank-transfer proof-of-payment upload — is implemented once.
 *
 * Using components must implement `paymentTier()` (the tier whose fee is
 * being charged) and `paymentFeeType()` ('registration' or 'renewal'), so
 * this trait can resolve the correct amount for whichever currency the
 * member ends up paying in.
 */
trait HandlesPaymentMethod
{
    public string $paymentMethod = 'paystack';

    public ?int $bankAccountId = null;

    public $proofOfPayment = null;

    abstract protected function paymentTier(): ?MembershipTier;

    abstract protected function paymentFeeType(): string;

    /**
     * Only bank accounts whose currency this tier actually has a fee
     * configured for — so a member can never select an account with no
     * valid price behind it.
     */
    public function getBankAccountsProperty()
    {
        $tier = $this->paymentTier();

        if (! $tier) {
            return collect();
        }

        return BankAccount::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (BankAccount $account) => $this->feeForCurrency($tier, $account->currency) !== null)
            ->values();
    }

    /**
     * The amount actually due right now, reactive to the chosen payment
     * method and (for bank transfer) the selected account's currency.
     */
    public function getDisplayAmountProperty(): ?float
    {
        $tier = $this->paymentTier();

        if (! $tier) {
            return null;
        }

        if ($this->paymentMethod === 'bank_transfer') {
            $account = $this->bankAccounts->firstWhere('id', (int) $this->bankAccountId);

            return $account ? $this->feeForCurrency($tier, $account->currency) : null;
        }

        return $this->feeForCurrency($tier, $tier->currency);
    }

    /**
     * The currency the display amount above is in.
     */
    public function getDisplayCurrencyProperty(): ?string
    {
        $tier = $this->paymentTier();

        if (! $tier) {
            return null;
        }

        if ($this->paymentMethod === 'bank_transfer') {
            return $this->bankAccounts->firstWhere('id', (int) $this->bankAccountId)?->currency;
        }

        return $tier->currency;
    }

    protected function feeForCurrency(MembershipTier $tier, string $currency): ?float
    {
        return $this->paymentFeeType() === 'renewal'
            ? $tier->renewalFeeFor($currency)
            : $tier->registrationFeeFor($currency);
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
     * Create the Payment record for the chosen method, at the amount/currency
     * resolved above. For bank transfer this immediately attaches the
     * uploaded proof and marks it pending staff verification; for Paystack
     * it's left pending until the gateway redirect completes.
     */
    protected function createPayment(Model $payable): Payment
    {
        $this->validatePaymentMethodFields();

        $amount = $this->displayAmount;
        $currency = $this->displayCurrency;

        abort_unless($amount !== null && $currency !== null, 422, 'No price is configured for the selected payment option.');

        $isBankTransfer = $this->paymentMethod === 'bank_transfer';

        // Paystack only ever processes NGN on this account — never let a
        // mis-configured tier (or a future currency added to the base
        // currency field) route a non-NGN amount through it.
        abort_if(! $isBankTransfer && $currency !== 'NGN', 422, 'Paystack payments must be in NGN.');

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

            app(HandleBankTransferSubmission::class)->handle($payment);
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

        abort_if($payment->currency !== 'NGN', 422, 'Paystack payments must be in NGN.');

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
