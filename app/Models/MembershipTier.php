<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class MembershipTier extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'abbreviation',
        'description',
        'registration_fee',
        'renewal_fee',
        'currency',
        'requires_qualification_upload',
        'requires_employer_info',
        'min_years_experience',
        'benefits',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'registration_fee' => 'decimal:2',
            'renewal_fee' => 'decimal:2',
            'requires_qualification_upload' => 'boolean',
            'requires_employer_info' => 'boolean',
            'benefits' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function userMemberships(): HasMany
    {
        return $this->hasMany(UserMembership::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(MembershipTierPrice::class);
    }

    /**
     * The registration fee in the given currency, or null if this tier isn't
     * priced in it (neither as the base currency nor an additional price).
     */
    public function registrationFeeFor(string $currency): ?float
    {
        $currency = strtoupper($currency);

        if ($currency === strtoupper($this->currency)) {
            return (float) $this->registration_fee;
        }

        $fee = $this->prices->firstWhere('currency', $currency)?->registration_fee;

        return $fee !== null ? (float) $fee : null;
    }

    /**
     * The renewal fee in the given currency, or null if this tier isn't
     * priced in it.
     */
    public function renewalFeeFor(string $currency): ?float
    {
        $currency = strtoupper($currency);

        if ($currency === strtoupper($this->currency)) {
            return (float) $this->renewal_fee;
        }

        $fee = $this->prices->firstWhere('currency', $currency)?->renewal_fee;

        return $fee !== null ? (float) $fee : null;
    }

    /**
     * Every currency this tier has a registration fee configured for,
     * base currency first.
     */
    public function currenciesWithRegistrationFee(): \Illuminate\Support\Collection
    {
        return collect([$this->currency])
            ->merge($this->prices->whereNotNull('registration_fee')->pluck('currency'))
            ->unique()
            ->values();
    }
}
