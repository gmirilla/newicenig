<?php

namespace App\Services\LegacyMigration;

use App\Legacy\LegacyUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Imports the old app's `users` table. Existing bcrypt password hashes are
 * carried over as-is (Laravel's `hashed` cast detects an already-hashed value
 * via `Hash::isHashed()` and leaves it alone) so members keep their password.
 */
class UserImporter
{
    /** @var array<int, int> Legacy user id => new user id. */
    protected array $idMap = [];

    public function __construct(protected ImportReport $report, protected bool $dryRun) {}

    public function run(): void
    {
        LegacyUser::query()->orderBy('id')->chunk(200, function ($legacyUsers): void {
            foreach ($legacyUsers as $legacyUser) {
                $this->importOne($legacyUser);
            }
        });
    }

    protected function importOne(LegacyUser $legacyUser): void
    {
        $email = strtolower(trim((string) $legacyUser->email));

        if ($email === '') {
            $this->report->skipped('users', "Legacy user #{$legacyUser->id} has no email address.");

            return;
        }

        $name = $legacyUser->fullName() ?: $email;

        $attributes = [
            'name' => $name,
            'phone' => $legacyUser->telephone,
            'password' => Hash::isHashed((string) $legacyUser->password) ? $legacyUser->password : Hash::make(Str::random(40)),
        ];

        if ($this->dryRun) {
            $existing = User::where('email', $email)->first();
            $this->idMap[$legacyUser->id] = $existing?->id ?? 0;
            $this->report->imported('users');

            return;
        }

        $user = DB::transaction(function () use ($email, $attributes, $legacyUser) {
            $user = User::updateOrCreate(['email' => $email], $attributes);
            $user->forceFill(['email_verified_at' => $legacyUser->email_verified_at])->save();

            return $user;
        });

        $this->idMap[$legacyUser->id] = $user->id;
        $this->report->imported('users');
    }

    public function newUserIdFor(int $legacyUserId): ?int
    {
        $id = $this->idMap[$legacyUserId] ?? null;

        return $id ?: null;
    }
}
