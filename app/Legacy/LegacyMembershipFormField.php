<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Read-only view of the old app's `i_c_e_n_membership_form_fields` table — the
 * pivot linking a `LegacyHtmlFormField` to a `LegacyMembershipTier`.
 */
class LegacyMembershipFormField extends Model
{
    protected $connection = 'legacy';

    protected $table = 'i_c_e_n_membership_form_fields';

    public function htmlFormField(): BelongsTo
    {
        return $this->belongsTo(LegacyHtmlFormField::class, 'h_t_m_l_form_field_id');
    }
}
