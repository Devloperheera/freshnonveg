<?php

namespace Database\Seeders;

use App\Models\AdvantageItem;
use App\Models\HomeSection;
use App\Models\OrderStep;
use App\Models\SiteStat;
use Illuminate\Database\Seeder;

class HomePageContentSeeder extends Seeder
{
    public function run(): void
    {
        HomeSection::query()->updateOrCreate(
            ['section_key' => HomeSection::KEY_ORDER_WAY],
            [
                'title' => 'Order करना है <u>आसान</u>',
                'subtitle' => 'बस 4 steps में ताज़ा गोश्त आपके दरवाज़े पर 🚪',
            ]
        );

        HomeSection::query()->updateOrCreate(
            ['section_key' => HomeSection::KEY_DELIVERY],
            [
                'title' => 'हम <u>Deliver</u> करते हैं 🛵',
                'subtitle' => 'आपके शहर में भी!',
            ]
        );

        HomeSection::query()->updateOrCreate(
            ['section_key' => HomeSection::KEY_ADVANTAGES],
            [
                'title' => 'Fresh खाने के <u>फायदे!</u>',
                'subtitle' => 'ताज़ा गोश्त क्यों है बेहतर? 🤔',
            ]
        );

        $advantages = [
            ['icon' => '💪', 'title' => 'ज्यादा प्रोटीन', 'description' => 'Fresh meat में protein और nutrients ज्यादा होते हैं', 'sort_order' => 1],
            ['icon' => '😋', 'title' => 'बेहतर स्वाद', 'description' => 'Frozen नहीं, इसलिए taste असली और लाजवाब!', 'sort_order' => 2],
            ['icon' => '🩺', 'title' => 'सेहत के लिए अच्छा', 'description' => 'कोई chemicals नहीं, बस pure freshness!', 'sort_order' => 3],
            ['icon' => '⚡', 'title' => 'तुरंत एनर्जी', 'description' => 'Fresh nutrients से instant energy मिलती है', 'sort_order' => 4],
        ];

        foreach ($advantages as $advantage) {
            AdvantageItem::query()->updateOrCreate(
                ['title' => $advantage['title']],
                [
                    'icon' => $advantage['icon'],
                    'description' => $advantage['description'],
                    'sort_order' => $advantage['sort_order'],
                    'status' => 1,
                ]
            );
        }

        $steps = [
            ['title' => 'चुनो 🛍️', 'description' => 'अपना पसंदीदा item चुनें', 'sort_order' => 1],
            ['title' => 'कटवाओ 🔪', 'description' => 'हमारे कसाई ताज़ा काटते हैं', 'sort_order' => 2],
            ['title' => 'पैक करो 📦', 'description' => 'Hygienic पैकिंग', 'sort_order' => 3],
            ['title' => 'खाओ 😋', 'description' => 'घर पर फटाफट delivery', 'sort_order' => 4],
        ];

        foreach ($steps as $step) {
            OrderStep::query()->updateOrCreate(
                ['title' => $step['title']],
                [
                    'description' => $step['description'],
                    'sort_order' => $step['sort_order'],
                    'status' => 1,
                ]
            );
        }

        $stats = [
            ['value' => 5000, 'label' => 'Happy Customers 😍', 'sort_order' => 1],
            ['value' => 25, 'label' => 'Items Fresh Daily 🥩', 'sort_order' => 2],
            ['value' => 5, 'label' => 'Cities Covered 📍', 'sort_order' => 3],
            ['value' => 100, 'label' => '% Fresh, 0% Frozen ❄️', 'sort_order' => 4],
        ];

        foreach ($stats as $stat) {
            SiteStat::query()->updateOrCreate(
                ['label' => $stat['label']],
                [
                    'value' => $stat['value'],
                    'sort_order' => $stat['sort_order'],
                    'status' => 1,
                ]
            );
        }
    }
}
