<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Bestseller', 'Fresh', 'Jumbo', 'New', 'Sale', 'Premium'] as $name) {
            Tag::query()->firstOrCreate(['name' => $name], ['status' => 1]);
        }
    }
}
