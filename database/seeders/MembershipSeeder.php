<?php

namespace Database\Seeders;

use App\Models\MembershipTier;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        PaymentGateway::updateOrCreate(['slug' => 'paystack'], [
            'name' => 'Paystack',
            'is_active' => true,
        ]);

        collect([
            [
                'name' => 'Associate Member',
                'abbreviation' => 'AICEN',
                'description' => 'For early-career economists building toward full certification.',
                'registration_fee' => 15000,
                'renewal_fee' => 8000,
                'requires_employer_info' => false,
                'requires_qualification_upload' => true,
                'min_years_experience' => 0,
                'benefits' => ['CPD event access', 'Member newsletter', 'Discounted conference rates'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Member',
                'abbreviation' => 'MICEN',
                'description' => 'For practising economists with at least 3 years of professional experience.',
                'registration_fee' => 35000,
                'renewal_fee' => 15000,
                'requires_employer_info' => true,
                'requires_qualification_upload' => true,
                'min_years_experience' => 3,
                'benefits' => ['Full voting rights', 'CPD event access', 'Member directory listing', 'Certification'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Fellow Chartered Economist',
                'abbreviation' => 'FICEN',
                'description' => 'For senior economists with 10+ years of distinguished professional practice.',
                'registration_fee' => 75000,
                'renewal_fee' => 25000,
                'requires_employer_info' => true,
                'requires_qualification_upload' => true,
                'min_years_experience' => 10,
                'benefits' => ['Highest professional designation', 'Council eligibility', 'All Member benefits'],
                'sort_order' => 3,
            ],
        ])->each(fn (array $tier) => MembershipTier::updateOrCreate(
            ['name' => $tier['name']],
            [...$tier, 'currency' => 'NGN', 'is_active' => true],
        ));
    }
}
