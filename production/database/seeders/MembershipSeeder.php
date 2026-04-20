<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Membership;


class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 1. Individual Membership
         */
        $individual = Membership::updateOrCreate(
            ['name' => 'Individual Membership'],
            ['description' => 'For students and professionals at different career stages.']
        );

        $this->seedTiers($individual, [
            [
                'name' => 'Student Member',
                'annual_fee' => 50,
                'target_audience' => 'University students or recent graduates',
                'benefits' => [
                    'Access to entry-level GRC and Financial Crime Prevention resources',
                    'Internships/job board',
                    'Career support'
                ],
            ],
            [
                'name' => 'Associate Member (Early Career)',
                'annual_fee' => 75,
                'target_audience' => 'Early career professionals (0–5 years’ experience)',
                'benefits' => [
                    'Monthly webinars',
                    'WGRCFP newsletter',
                    'Member badge',
                    'Discounted events'
                ],
            ],
            [
                'name' => 'Associate Member (Mid-Career)',
                'annual_fee' => 150,
                'target_audience' => 'Mid-career professionals & specialists',
                'benefits' => [
                    'Full event access',
                    'Member directory listing',
                    'Speaker opportunities'
                ],
            ],
            [
                'name' => 'Associate Member (Senior Executive)',
                'annual_fee' => 250,
                'target_audience' => 'Senior executives, directors, partners',
                'benefits' => [
                    'Priority speaking roles',
                    'Leadership roundtables',
                    'Mentor opportunities'
                ],
            ],
            [
                'name' => 'Associate Member (Accredited Expert)',
                'annual_fee' => 300,
                'target_audience' => 'Accredited senior experts & contributors',
                'benefits' => [
                    'Voting rights',
                    'Governance roles',
                    'Advisory participation',
                    'Advanced credentials'
                ],
                'invitation_only' => 'By invitation or assessment'
            ],
        ]);

        /**
         * 2. Corporate & Institutional Membership
         */
        $corporate = Membership::updateOrCreate(
            ['name' => 'Corporate & Institutional Membership'],
            ['description' => 'For organizations and institutions supporting women in GRC & Financial Crime.']
        );

        $this->seedTiers($corporate, [
            [
                'name' => 'SME Partner',
                'annual_fee' => 750,
                'target_audience' => 'Small–medium enterprises (up to 250 employees)',
                'benefits' => [
                    '2 staff accounts',
                    'Company logo on partner page',
                    'Discounted training bundles'
                ],
            ],
            [
                'name' => 'Corporate Member',
                'annual_fee' => 2000,
                'target_audience' => 'Large organisations or regional offices',
                'benefits' => [
                    '5 staff accounts',
                    'Free event passes',
                    'Internal workshop session',
                    'DEI support tools'
                ],
            ],
            [
                'name' => 'Strategic Partner',
                'annual_fee' => 5000,
                'target_audience' => 'Multinationals, government, academic or regulatory institutions',
                'benefits' => [
                    'Co-branded initiatives',
                    '10 accounts',
                    'Policy roundtable access',
                    'Joint publications'
                ],
            ],
        ]);

        /**
         * 3. Mentor Membership
         */
        $mentor = Membership::updateOrCreate(
            ['name' => 'Mentor Membership'],
            ['description' => 'For experienced practitioners ready to mentor and shape the next generation.']
        );

        $this->seedTiers($mentor, [
            [
                'name' => 'Community Mentor',
                'annual_fee' => 100,
                'target_audience' => 'First-time mentors',
                'benefits' => [
                    'Mentor onboarding',
                    'Mentee matching',
                    'Recognition badge'
                ],
            ],
            [
                'name' => 'Senior Mentor',
                'annual_fee' => 180,
                'target_audience' => 'Experienced mentors and team leads',
                'benefits' => [
                    'Priority mentee matching',
                    'Mentor roundtables',
                    'Speaking opportunities'
                ],
            ],
            [
                'name' => 'Lead Mentor',
                'annual_fee' => 260,
                'target_audience' => 'Senior experts and executives',
                'benefits' => [
                    'Governance participation',
                    'Ambassador visibility',
                    'Advanced mentor tools'
                ],
            ],
        ]);

        /**
         * 4. Affiliate Membership
         */
        $affiliate = Membership::updateOrCreate(
            ['name' => 'Affiliate Membership'],
            ['description' => 'For partners and supporters collaborating with the WGRCFP ecosystem.']
        );

        $this->seedTiers($affiliate, [
            [
                'name' => 'Affiliate Basic',
                'annual_fee' => 90,
                'target_audience' => 'Independent supporters and professionals',
                'benefits' => [
                    'Community access',
                    'Newsletter',
                    'Selected event discounts'
                ],
            ],
            [
                'name' => 'Affiliate Plus',
                'annual_fee' => 160,
                'target_audience' => 'Partner consultants and small firms',
                'benefits' => [
                    'Profile listing',
                    'Collaboration invites',
                    'Campaign visibility'
                ],
            ],
            [
                'name' => 'Affiliate Premium',
                'annual_fee' => 280,
                'target_audience' => 'Strategic partner organizations',
                'benefits' => [
                    'Priority partner features',
                    'Speaking spots',
                    'Co-branded initiatives'
                ],
            ],
        ]);
    }

    private function seedTiers($membership, array $tiers): void
    {
        foreach ($tiers as $tier) {
            $membership->tiers()->updateOrCreate(
                ['name' => $tier['name']],
                $tier
            );
        }
    }
}