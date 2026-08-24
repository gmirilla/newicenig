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
}
