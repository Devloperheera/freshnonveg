<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageContent;
use App\Support\SitePage;
use Illuminate\Database\Seeder;

class LegalPagesSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'terms-conditions' => [
                'title' => 'Terms & Conditions',
                'content' => '<p>Welcome to Fresh Non Veg. By using our website and placing orders, you agree to these terms.</p><p>We supply fresh non-vegetarian products subject to availability. Prices and offers may change without prior notice.</p>',
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'content' => '<p>We respect your privacy. Personal information collected during registration or checkout is used only to process orders and improve our service.</p><p>We do not sell your data to third parties.</p>',
            ],
            'refund-policy' => [
                'title' => 'Refund Policy',
                'content' => '<p>If you receive damaged or incorrect items, contact us within 24 hours with photos. Eligible cases may receive replacement or refund as per our review.</p><p>Perishable goods must be reported immediately upon delivery.</p>',
            ],
            'contact' => [
                'title' => 'Contact Us',
                'content' => '<p><strong>Fresh Non Veg</strong></p><p>📍 A35, Behind HDFC Bank, Shramik Kunj, Sector 110, Noida 201304</p><p>📞 9717156362</p><p>✉️ eatfreshnonveg@gmail.com</p><p>WhatsApp ya call par order / query ke liye sampark karein.</p>',
            ],
            'blog' => [
                'title' => 'Blog',
                'content' => '<p>Fresh recipes, offers aur meat care tips — jald yahan update honge.</p>',
            ],
        ];

        foreach (SitePage::SLUGS as $slug => $pageName) {
            $page = Page::query()->firstOrCreate(
                ['name' => $pageName],
                ['status' => 1]
            );
            $page->update(['status' => 1]);

            $preset = $defaults[$slug];

            PageContent::query()->updateOrCreate(
                [
                    'page_id' => $page->id,
                    'title' => $preset['title'],
                ],
                [
                    'content' => $preset['content'],
                    'meta_title' => $preset['title'].' | FreshNonVeg',
                    'meta_description' => strip_tags($preset['content']),
                    'status' => 1,
                ]
            );
        }
    }
}
