<?php

namespace App\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Read-only view of the old app's `files` table. `path` is relative to the old
 * app's `storage/app/public` disk.
 */
class LegacyFile extends Model
{
    protected $connection = 'legacy';

    protected $table = 'files';

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
