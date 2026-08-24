<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipRenewal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_membership_id',
        'payment_id',
        'renewed_at',
        'previous_expires_at',
        'new_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'renewed_at' => 'datetime',
            'previous_expires_at' => 'datetime',
            'new_expires_at' => 'datetime',
        ];
    }

    public function userMembership(): BelongsTo
    {
        return $this->belongsTo(UserMembership::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
