<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Read-only view of the old app's `users` table. Never write through this model.
 */
class LegacyUser extends Model
{
    protected $connection = 'legacy';

    protected $table = 'users';

    public function fullName(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(LegacyUserMembership::class, 'user_id');
    }
}
