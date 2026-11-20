<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Purana setup: product form me ek "banner_id". Naya: banner_product (ek banner → kai products, ek product → kai banners).
 * Purane links copy karo taaki live banners par products turant dikhein.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'banner_id')) {
            return;
        }

        $now = now();
        $rows = DB::table('products')
            ->whereNotNull('banner_id')
            ->whereIn('banner_id', DB::table('banners')->select('id'))
            ->orderBy('id')
            ->get(['id', 'banner_id'])
            ->map(fn ($p, $i) => [
                'banner_id' => $p->banner_id,
                'product_id' => $p->id,
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('banner_product')->insertOrIgnore($chunk);
        }
    }

    public function down(): void
    {
        // Data copy — wapas karne ki zarurat nahi (banner_product table drop hone par sab hat jata hai).
    }
};
