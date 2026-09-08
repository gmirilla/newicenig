<?php

namespace App\Models;

use App\Enums\EmailLogStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per outgoing email (mailable or notification), recorded by
 * {@see \App\Listeners\LogOutgoingMail}. `failed_job_uuid` links back to the
 * matching `failed_jobs` row so a failed send can be resent via `queue:retry`.
 */
class EmailLog extends Model
{
    protected $fillable = [
        'type',
        'to',
        'subject',
        'status',
        'error',
        'failed_job_uuid',
        'sent_at',
        'failed_at',
    ];

    protected function casts(): array
    {
        return [
            'to' => 'array',
            'status' => EmailLogStatus::class,
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function recipients(): string
    {
        return implode(', ', $this->to ?? []);
    }

    public function typeLabel(): string
    {
        return class_basename($this->type);
    }
}
