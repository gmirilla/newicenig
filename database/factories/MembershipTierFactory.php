<?php

namespace Database\Factories;

use App\Models\MembershipTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipTier>
 */
class MembershipTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Associate Member', 'Member', 'Fellow']).' '.$this->faker->unique()->numerify('##'),
            'abbreviation' => strtoupper($this->faker->lexify('????')),
            'description' => $this->faker->sentence(),
            'registration_fee' => $this->faker->randomElement([15000, 35000, 75000]),
            'renewal_fee' => $this->faker->randomElement([8000, 15000, 25000]),
            'currency' => 'NGN',
            'requires_qualification_upload' => false,
            'requires_employer_info' => false,
            'min_years_experience' => 0,
            'benefits' => ['CPD access', 'Member newsletter'],
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
