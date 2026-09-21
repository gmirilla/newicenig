<?php

namespace App\Livewire\Portal;

use App\Enums\MembershipStatus;
use App\Livewire\Portal\Concerns\HandlesPaymentMethod;
use App\Models\MembershipRenewal;
use App\Models\MembershipTier;
use App\Models\UserMembership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Handles renewal for both logged-in members and members with no account (or
 * an unclaimed one — common for legacy-imported members). A logged-in member
 * goes straight to their fee/payment summary; a guest first verifies who
 * they are by membership number + email before seeing the same summary,
 * scoped to that specific membership record.
 */
class RenewalFlow extends Component
{
    use HandlesPaymentMethod, WithFileUploads;

    public ?int $membershipId = null;

    public string $lookupMembershipNumber = '';

    public string $lookupEmail = '';

    public bool $lookupFailed = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->membershipId = Auth::user()->currentMembership()?->id;
        }
    }

    public function getMembershipProperty(): ?UserMembership
    {
        return $this->membershipId ? UserMembership::find($this->membershipId) : null;
    }

    public function getIsEligibleForRenewalProperty(): bool
    {
        return $this->membership
            && in_array($this->membership->status, [MembershipStatus::Active, MembershipStatus::Expired], true);
    }

    public function getRenewalIneligibleMessageProperty(): ?string
    {
        return match ($this->membership?->status) {
            MembershipStatus::PendingPayment => 'Your original application payment was never completed. Please contact ICEN support to complete your application before renewing.',
            MembershipStatus::PendingReview => 'Your membership application is still under review. There is nothing to renew yet — you will be notified once it is approved.',
            default => 'This membership record is not currently eligible for renewal. Please contact ICEN support.',
        };
    }

    /**
     * Lets a member with no account (or an unclaimed one) verify who they
     * are without logging in, by matching their membership number against
     * the email on file. Rate-limited and deliberately vague on failure so
     * it can't be used to enumerate valid membership numbers or emails.
     */
    public function lookup(): void
    {
        if (Auth::check()) {
            return;
        }

        $this->lookupFailed = false;

        $this->ensureLookupIsNotRateLimited();

        $this->validate([
            'lookupMembershipNumber' => ['required', 'string', 'max:100'],
            'lookupEmail' => ['required', 'email', 'max:255'],
        ]);

        $membership = UserMembership::query()
            ->whereRaw('LOWER(membership_number) = ?', [Str::lower(trim($this->lookupMembershipNumber))])
            ->whereRaw('LOWER(email) = ?', [Str::lower(trim($this->lookupEmail))])
            ->first();

        if (! $membership) {
            RateLimiter::hit($this->lookupThrottleKey(), 600);
            $this->lookupFailed = true;

            return;
        }

        RateLimiter::clear($this->lookupThrottleKey());
        $this->membershipId = $membership->id;
    }

    protected function ensureLookupIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->lookupThrottleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->lookupThrottleKey());

        throw ValidationException::withMessages([
            'lookupEmail' => 'Too many attempts. Please try again in '.ceil($seconds / 60).' minute(s).',
        ]);
    }

    protected function lookupThrottleKey(): string
    {
        return 'renewal-lookup:'.request()->ip();
    }

    protected function paymentTier(): ?MembershipTier
    {
        return $this->membership?->membershipTier;
    }

    protected function paymentFeeType(): string
    {
        return 'renewal';
    }

    public function renew()
    {
        $membership = $this->membership;

        abort_unless($membership && $this->isEligibleForRenewal, 404);

        $renewal = MembershipRenewal::create([
            'user_membership_id' => $membership->id,
        ]);

        $payment = $this->createPayment($renewal);

        $renewal->update(['payment_id' => $payment->id]);

        $email = $membership->email ?: $membership->user?->email ?: Auth::user()?->email;

        abort_if(! $email, 422, 'No email is on file for this membership.');

        return $this->redirectForPayment($payment, $email, ['membership_renewal_id' => $renewal->id]);
    }

    public function render()
    {
        return view('livewire.portal.renewal-flow');
    }
}
