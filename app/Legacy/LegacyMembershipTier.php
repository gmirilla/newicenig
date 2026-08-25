<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;

/**
 * Read-only view of the old app's `i_c_e_n_memberships` table (membership tiers).
 */
class LegacyMembershipTier extends Model
{
    protected $connection = 'legacy';

    protected $table = 'i_c_e_n_memberships';
}
