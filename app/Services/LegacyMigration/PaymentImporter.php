<?php

namespace App\Services\LegacyMigration;

use App\Enums\PaymentStatus;
use App\Legacy\LegacyPayment;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\UserMembership;

/**
 * Imports the old app's `payments` table. The old app only ever charged
 * through Paystack, so every payment is remapped onto the new app's single
 * seeded `paystack` {@see PaymentGateway} row rather than migrating the old
 * `payment_gateways` table (which holds live gateway credentials that must
 * never be copied). Every old payment's `paymentable` also always resolves to
 * a membership application, so `payable_type` is hard-set to {@see UserMembership}.
 */
class PaymentImporter
{
    public function __construct(
        protected ImportReport $report,
        protected bool $dryRun,
        protected UserImporter $users,
        protected UserMembershipImporter $memberships,
    ) {}

    public function run(): void
    {
        $gatewayId = $this->dryRun ? 0 : PaymentGateway::where('slug', 'paystack')->value('id');

        LegacyPayment::query()->orderBy('id')->chunk(200, function ($legacyPayments) use ($gatewayId): void {
            foreach ($legacyPayments as $legacyPayment) {
                $this->importOne($legacyPayment, $gatewayId);
            }
        });
    }

    protected function importOne(LegacyPayment $legacy, ?int $gatewayId): void
    {
        $reference = trim((string) $legacy->transaction_reference);

        if ($reference === '') {
            $this->report->skipped('payments', "Legacy payment #{$legacy->id} has no transaction reference.");

            return;
        }

        $newUserId = $this->users->newUserIdFor((int) $legacy->user_id);
        $newMembershipId = $this->memberships->newMembershipIdFor((int) $legacy->paymentable_id);

        if (! $this->dryRun && ! $newMembershipId) {
            $this->report->skipped('payments', "Legacy payment #{$legacy->id} (ref {$reference}): membership #{$legacy->paymentable_id} was not imported.");

            return;
        }

        $attributes = [
            'user_id' => $newUserId,
            'payment_gateway_id' => $gatewayId,
            'payable_id' => $newMembershipId,
            'payable_type' => UserMembership::class,
            'amount' => $legacy->amount,
            'currency' => $legacy->currency ?: 'NGN',
            'reference' => $reference,
            'status' => $this->mapStatus($legacy->transaction_status),
            'channel' => $legacy->method,
            'paid_at' => $legacy->paid_at,
            'raw_gateway_response' => $legacy->metadata,
        ];

        if ($this->dryRun) {
            $this->report->imported('payments');

            return;
        }

        Payment::updateOrCreate(['reference' => $reference], $attributes);

        $this->report->imported('payments');
    }

    protected function mapStatus(?string $oldStatus): PaymentStatus
    {
        return match (strtolower((string) $oldStatus)) {
            'successful' => PaymentStatus::Successful,
            'failed' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };
    }
}
