<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Rahul, Noida',
                'star_rating' => 5,
                'short_description' => 'Mutton एकदम fresh था, बाजार से बेहतर!',
            ],
            [
                'name' => 'Priya, Greater Noida',
                'star_rating' => 5,
                'short_description' => 'Prawns jumbo size ke the, delivery bhi fast!',
            ],
            [
                'name' => 'Amit, Ghaziabad',
                'star_rating' => 5,
                'short_description' => 'Frozen नहीं, सच में ताज़ा चिकन मिला।',
            ],
        ];

        foreach ($items as $item) {
            Testimonial::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'star_rating' => $item['star_rating'],
                    'short_description' => $item['short_description'],
                    'status' => 1,
                ]
            );
        }
    }
}
