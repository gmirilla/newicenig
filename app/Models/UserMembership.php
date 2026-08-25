<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class UserMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'membership_tier_id',
        'membership_number',
        'status',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'employer_name',
        'job_title',
        'years_of_experience',
        'state_of_origin',
        'qualification',
        'date_of_birth',
        'place_of_birth',
        'nationality',
        'local_government_area',
        'marital_status',
        'residential_address',
        'postal_address',
        'next_of_kin_name',
        'next_of_kin_address',
        'primary_school',
        'primary_school_year',
        'secondary_school',
        'secondary_school_year',
        'higher_institution',
        'higher_institution_course',
        'higher_institution_year',
        'higher_institution_grade',
        'higher_institution_second_degree',
        'belongs_to_other_institute',
        'other_institute_name',
        'other_institute_status',
        'other_institute_membership_number',
        'year_of_qualification',
        'declaration_name',
        'declaration_accepted_at',
        'extra_fields',
        'directory_opt_in',
        'submitted_at',
        'verified_at',
        'verified_by',
        'expires_at',
        'previous_membership_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => MembershipStatus::class,
            'extra_fields' => 'array',
            'directory_opt_in' => 'boolean',
            'belongs_to_other_institute' => 'boolean',
            'date_of_birth' => 'date',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'declaration_accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membershipTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function previousMembership(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_membership_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(MembershipRenewal::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MemberFile::class);
    }

    public function fullName(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    public function isActive(): bool
    {
        return $this->status === MembershipStatus::Active
            && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public static function generateMembershipNumber(): string
    {
        $year = now()->year;
        $sequence = static::whereYear('created_at', $year)->whereNotNull('membership_number')->count() + 1;

        return sprintf('ICEN/%d/%05d', $year, $sequence);
    }
}
