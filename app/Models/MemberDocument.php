<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MemberDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'description',
        'uploaded_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Stored on the private 'local' disk (storage/app/private) rather than the
     * public disk used by Posts/Downloads/MemberFile — these are per-member
     * documents and must only be reachable through an auth-checked route.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->useDisk('local')->singleFile();
    }

    public function fileName(): ?string
    {
        return $this->getFirstMedia('file')?->file_name;
    }
}
