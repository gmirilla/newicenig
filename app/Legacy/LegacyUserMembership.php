<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Read-only view of the old app's `i_c_e_n_user_memberships` table — a member's
 * application/instance of a tier. The applicant's actual submitted form data
 * lives in the EAV `registrations()` relation, not on this row.
 */
class LegacyUserMembership extends Model
{
    protected $connection = 'legacy';

    protected $table = 'i_c_e_n_user_memberships';

    protected $casts = [
        'verified_at' => 'datetime',
        'expired_at' => 'datetime',
        'upgraded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(LegacyUser::class, 'user_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(LegacyMembershipTier::class, 'i_c_e_n_membership_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(LegacyMembershipRegistration::class, 'i_c_e_n_user_membership_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(LegacyPayment::class, 'paymentable');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(LegacyFile::class, 'fileable');
    }

    /**
     * Reconstruct the applicant's submitted form data as [field_name => value],
     * resolving the two-hop EAV join (registration -> membership_form_field
     * pivot -> html_form_field) to the field's real `name`.
     *
     * @return array<string, string>
     */
    public function formData(): array
    {
        return $this->registrations()
            ->with('membershipFormField.htmlFormField')
            ->get()
            ->filter(fn (LegacyMembershipRegistration $registration) => $registration->membershipFormField?->htmlFormField)
            ->mapWithKeys(fn (LegacyMembershipRegistration $registration) => [
                $registration->membershipFormField->htmlFormField->name => $registration->value,
            ])
            ->all();
    }
}
