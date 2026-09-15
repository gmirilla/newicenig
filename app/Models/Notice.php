<?php

namespace App\Models;

use App\Notifications\NewNotice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Notification;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'is_pinned',
        'is_published',
        'published_at',
        'expires_at',
        'notified_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function membershipTiers(): BelongsToMany
    {
        return $this->belongsToMany(MembershipTier::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isVisibleToTier(?int $membershipTierId): bool
    {
        $tierIds = $this->membershipTiers->pluck('id');

        return $tierIds->isEmpty() || $tierIds->contains($membershipTierId);
    }

    /**
     * Send an in-app notification to every member eligible to see this notice,
     * once, the first time it's saved in a published state.
     */
    public function notifyEligibleMembers(): void
    {
        if ($this->notified_at || ! $this->is_published || ($this->published_at && $this->published_at->isFuture())) {
            return;
        }

        $users = User::whereHas('memberships')
            ->with('memberships.membershipTier')
            ->get()
            ->filter(fn (User $user) => $this->isVisibleToTier($user->currentMembership()?->membership_tier_id));

        if ($users->isNotEmpty()) {
            Notification::send($users, new NewNotice($this));
        }

        $this->forceFill(['notified_at' => now()])->save();
    }
}
