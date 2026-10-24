<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Settings\BrandingSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'payment_gateway_id',
        'payment_method',
        'bank_account_id',
        'payable_id',
        'payable_type',
        'amount',
        'currency',
        'reference',
        'gateway_reference',
        'status',
        'channel',
        'paid_at',
        'raw_gateway_response',
        'paystack_check_attempts',
        'next_paystack_check_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'paid_at' => 'datetime',
            'raw_gateway_response' => 'array',
            'next_paystack_check_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Proof-of-payment uploads (bank transfer only) are private — stored on
     * the 'local' disk rather than the public disk, same as MemberDocument.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('proof_of_payment')->useDisk('local')->singleFile();
    }

    public function hasProofOfPayment(): bool
    {
        return $this->getFirstMedia('proof_of_payment') !== null;
    }

    public static function generateReference(): string
    {
        // Sent to Paystack as the transaction reference, so keep it to safe
        // characters regardless of what's typed into Branding settings —
        // falls back to a generic prefix if the short name has nothing
        // alphanumeric in it at all.
        $shortName = app(BrandingSettings::class)->brand_short_name;
        $prefix = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $shortName)) ?: 'PAY';

        return "{$prefix}-".Str::upper(Str::random(12));
    }
}
