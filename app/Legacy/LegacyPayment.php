<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Read-only view of the old app's `payments` table.
 */
class LegacyPayment extends Model
{
    protected $connection = 'legacy';

    protected $table = 'payments';

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(LegacyUser::class, 'user_id');
    }

    public function paymentable(): MorphTo
    {
        return $this->morphTo();
    }
}
