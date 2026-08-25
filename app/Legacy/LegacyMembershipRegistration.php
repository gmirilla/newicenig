<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Read-only view of the old app's `i_c_e_n_membership_registrations` table —
 * one EAV value row per submitted form field per application.
 */
class LegacyMembershipRegistration extends Model
{
    protected $connection = 'legacy';

    protected $table = 'i_c_e_n_membership_registrations';

    public function membershipFormField(): BelongsTo
    {
        return $this->belongsTo(LegacyMembershipFormField::class, 'i_c_e_n_membership_form_field_id');
    }
}
