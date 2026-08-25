<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class QueuedJob extends Model
{
    protected $table = 'jobs';

    public $timestamps = false;

    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
        ];
    }

    public function jobClass(): ?string
    {
        $decoded = json_decode($this->payload, true);

        $command = $decoded['data']['commandName'] ?? $decoded['displayName'] ?? null;

        return $command ? class_basename($command) : null;
    }

    public function createdAtDate(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_at);
    }

    public function availableAtDate(): Carbon
    {
        return Carbon::createFromTimestamp($this->available_at);
    }

    public function isReserved(): bool
    {
        return ! is_null($this->reserved_at);
    }
}
