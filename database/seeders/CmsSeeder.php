<?php

namespace Database\Seeders;

use App\Models\Download;
use App\Models\Event;
use App\Models\Page;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        Page::updateOrCreate(['slug' => 'about'], [
            'title' => 'About ICEN',
            'body' => '<p>The Institute of Chartered Economists of Nigeria (ICEN) is the professional body responsible for certifying, developing, and representing economists across Nigeria.</p><p>Founded to raise the standard of economic practice nationwide, ICEN certifies professionals, runs continuing education programmes, and advocates for the profession across government, industry, and academia.</p>',
            'meta_description' => 'Learn about the Institute of Chartered Economists of Nigeria.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        collect([
            ['name' => 'Dr. Amina Bello', 'role_title' => 'President', 'order' => 1],
            ['name' => 'Chief Emeka Nwosu', 'role_title' => 'Registrar-General', 'order' => 2],
            ['name' => 'Mrs. Folake Adeyemi', 'role_title' => 'Vice President', 'order' => 3],
            ['name' => 'Dr. Ibrahim Yusuf', 'role_title' => 'Financial Secretary', 'order' => 4],
        ])->each(fn (array $member) => TeamMember::updateOrCreate(
            ['name' => $member['name']],
            [
                'role_title' => $member['role_title'],
                'bio' => '<p>A senior economist with extensive experience across public and private sector economic practice.</p>',
                'display_order' => $member['order'],
                'is_current' => true,
            ]
        ));

        collect([
            [
                'title' => 'ICEN Announces 2026 CPD Calendar',
                'category' => 'news',
                'excerpt' => 'The Institute has released its continuing professional development calendar for the year, covering macroeconomic policy, data analysis, and public finance.',
            ],
            [
                'title' => 'New Guidelines for Chartered Economist Certification',
                'category' => 'publication',
                'excerpt' => 'Updated certification guidelines clarify the experience and examination requirements for the Chartered Economist designation.',
            ],
            [
                'title' => 'ICEN Statement on National Economic Policy',
                'category' => 'press-release',
                'excerpt' => 'The Institute issues a statement on proposed fiscal policy changes, drawing on member expertise across sectors.',
            ],
        ])->each(fn (array $post) => Post::updateOrCreate(
            ['title' => $post['title']],
            [
                'excerpt' => $post['excerpt'],
                'body' => '<p>'.$post['excerpt'].'</p><p>Full details will be published here. This is placeholder content — edit it from the admin panel under Content → Posts.</p>',
                'category' => $post['category'],
                'is_published' => true,
                'published_at' => now()->subDays(random_int(1, 30)),
            ]
        ));

        collect([
            [
                'title' => 'Annual Economic Outlook Conference',
                'starts_at' => now()->addDays(21)->setTime(9, 0),
                'location' => 'Abuja, Nigeria',
                'is_virtual' => false,
                'cpd_points' => 6,
            ],
            [
                'title' => 'Webinar: Data Analysis for Economists',
                'starts_at' => now()->addDays(10)->setTime(14, 0),
                'location' => null,
                'is_virtual' => true,
                'cpd_points' => 2,
            ],
            [
                'title' => 'Regional Chapter Meetup — Lagos',
                'starts_at' => now()->subDays(15)->setTime(10, 0),
                'location' => 'Lagos, Nigeria',
                'is_virtual' => false,
                'cpd_points' => 1,
            ],
        ])->each(fn (array $event) => Event::updateOrCreate(
            ['title' => $event['title']],
            [
                'description' => '<p>Join fellow members for this CPD-accredited session. Placeholder content — edit it from the admin panel under Content → Events.</p>',
                'starts_at' => $event['starts_at'],
                'location' => $event['location'],
                'is_virtual' => $event['is_virtual'],
                'cpd_points' => $event['cpd_points'],
                'is_published' => true,
            ]
        ));

        collect([
            ['title' => 'Membership Application Form', 'category' => 'Forms'],
            ['title' => 'Code of Professional Conduct', 'category' => 'Guidelines'],
            ['title' => 'ICEN Byelaws', 'category' => 'Governance'],
        ])->each(fn (array $download) => Download::updateOrCreate(
            ['title' => $download['title']],
            [
                'description' => 'Placeholder entry — attach the real file from the admin panel under Content → Downloads.',
                'category' => $download['category'],
                'is_public' => true,
            ]
        ));

        Testimonial::updateOrCreate(
            ['author_name' => 'Dr. Chidinma Okafor'],
            [
                'quote' => 'ICEN certification gave my career the credibility it needed — the CPD programme keeps me current in a fast-moving field.',
                'author_role' => 'Senior Economist, Central Bank',
                'is_featured' => true,
                'display_order' => 1,
            ]
        );
    }
}
