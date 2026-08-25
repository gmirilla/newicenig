<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FailedJob extends Model
{
    protected $table = 'failed_jobs';

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
        ];
    }

    public function jobClass(): ?string
    {
        $decoded = json_decode($this->payload, true);

        $command = $decoded['data']['commandName'] ?? $decoded['displayName'] ?? null;

        return $command ? class_basename($command) : null;
    }

    public function exceptionSummary(): string
    {
        return Str::of($this->exception)->before("\n")->limit(300);
    }
}
