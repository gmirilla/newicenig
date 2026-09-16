<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipTierPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_tier_id',
        'currency',
        'registration_fee',
        'renewal_fee',
    ];

    protected function casts(): array
    {
        return [
            'registration_fee' => 'decimal:2',
            'renewal_fee' => 'decimal:2',
        ];
    }

    public function membershipTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class);
    }
}
