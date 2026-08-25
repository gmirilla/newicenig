<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Roles that gate access to the admin panel by name. Protected from being
     * renamed or deleted (see AppServiceProvider) since canAccessPanel() checks
     * these names directly — renaming one would silently lock out everyone who has it.
     *
     * @var array<int, string>
     */
    public const PANEL_ROLES = ['super-admin', 'admin', 'registrar'];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(self::PANEL_ROLES);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(UserMembership::class);
    }

    public function currentMembership(): ?UserMembership
    {
        return $this->memberships()->latest('id')->first();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Where to send this user after login/registration/etc. Staff accounts with no
     * membership of their own skip the (otherwise empty) member portal and go
     * straight to the admin panel; everyone else lands on the member dashboard.
     */
    public function defaultDashboardUrl(): string
    {
        if ($this->hasAnyRole(self::PANEL_ROLES) && ! $this->currentMembership()) {
            return '/admin';
        }

        return route('member.dashboard', absolute: false);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
