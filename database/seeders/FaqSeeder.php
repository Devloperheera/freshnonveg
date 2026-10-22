<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'question' => 'क्या सब कुछ fresh होता है?',
                'answer' => 'हाँ, हम सीधे meat shop से ताज़ा सामान देते हैं, frozen नहीं।',
                'sort_order' => 1,
            ],
            [
                'question' => 'Free delivery कब मिलती है?',
                'answer' => '₹999 या उससे ऊपर के order पर delivery free है।',
                'sort_order' => 2,
            ],
            [
                'question' => 'किन शहरों में delivery है?',
                'answer' => 'Noida, Greater Noida, Greater Noida West, Ghaziabad और Gautam Buddha Nagar।',
                'sort_order' => 3,
            ],
            [
                'question' => 'Order कैसे करें?',
                'answer' => 'Website से या WhatsApp पर सीधे order करें।',
                'sort_order' => 4,
            ],
        ];

        foreach ($items as $item) {
            Faq::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'sort_order' => $item['sort_order'],
                    'status' => 1,
                ]
            );
        }
    }
}
