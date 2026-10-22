<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    /**
     * @var list<array{name: string, slug: string, pexels_id: int}>
     */
    private const CATEGORIES = [
        ['name' => 'Chicken', 'slug' => 'chicken', 'pexels_id' => 6107734],
        ['name' => 'Mutton', 'slug' => 'mutton', 'pexels_id' => 31732111],
        ['name' => 'Fish', 'slug' => 'fish', 'pexels_id' => 7269615],
        ['name' => 'Prawns', 'slug' => 'prawns', 'pexels_id' => 566345],
        ['name' => 'Crab', 'slug' => 'crab', 'pexels_id' => 8352789],
        ['name' => 'Lobster', 'slug' => 'lobster', 'pexels_id' => 566566],
        ['name' => 'Duck', 'slug' => 'duck', 'pexels_id' => 24038058],
        ['name' => 'Turkey', 'slug' => 'turkey', 'pexels_id' => 2673353],
        ['name' => 'Eggs', 'slug' => 'eggs', 'pexels_id' => 769969],
        ['name' => 'Sausages', 'slug' => 'sausages', 'pexels_id' => 4495633],
        ['name' => 'Keema', 'slug' => 'keema', 'pexels_id' => 7225700],
        ['name' => 'Ready to Cook', 'slug' => 'ready-to-cook', 'pexels_id' => 5937501],
    ];

    public function run(): void
    {
        Storage::disk('public')->makeDirectory('categories');

        foreach (self::CATEGORIES as $item) {
            $relativePath = $this->resolveImage($item['slug'], $item['pexels_id']);

            $category = Category::query()->firstOrNew(['name' => $item['name']]);

            if ($category->exists && $category->image && $category->image !== $relativePath) {
                $category->deleteImageFile();
            }

            $category->fill([
                'image' => $relativePath,
                'status' => 1,
            ]);
            $category->save();

            $bytes = Storage::disk('public')->size($relativePath);
            $this->command?->info(sprintf('Category: %s → %s (%d KB)', $item['name'], $relativePath, (int) round($bytes / 1024)));
        }
    }

    private function resolveImage(string $slug, int $pexelsId): string
    {
        $filename = 'categories/'.$slug.'.jpg';
        $bundled = database_path('seeders/assets/categories/'.$slug.'.jpg');

        if (File::isFile($bundled) && $this->isValidImageBinary(File::get($bundled))) {
            Storage::disk('public')->put($filename, File::get($bundled));

            return $filename;
        }

        $body = $this->downloadFromPexels($pexelsId);
        Storage::disk('public')->put($filename, $body);

        return $filename;
    }

    private function downloadFromPexels(int $pexelsId): string
    {
        $url = sprintf(
            'https://images.pexels.com/photos/%1$d/pexels-photo-%1$d.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop',
            $pexelsId
        );

        $response = Http::connectTimeout(30)
            ->timeout(120)
            ->retry(3, 1000)
            ->withHeaders([
                'User-Agent' => 'FreshNonVegSeeder/1.0',
                'Accept' => 'image/jpeg,image/png,image/*',
                'Referer' => 'https://www.pexels.com/',
            ])
            ->get($url);

        $body = $response->body();

        if (! $response->successful() || ! $this->isValidImageBinary($body)) {
            throw new \RuntimeException("Image download failed for Pexels ID {$pexelsId} (HTTP {$response->status()}).");
        }

        return $body;
    }

    private function isValidImageBinary(string $body): bool
    {
        if (strlen($body) < 20_000) {
            return false;
        }

        if (str_starts_with($body, "\xFF\xD8\xFF")) {
            return true;
        }

        return str_starts_with($body, "\x89PNG\r\n\x1a\n");
    }
}
