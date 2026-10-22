<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductWeight;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    /**
     * @var list<array{
     *     name: string,
     *     slug: string,
     *     category: string,
     *     featured: bool,
     *     description: string,
     *     meta_title: string,
     *     meta_description: string,
     *     meta_keywords: string,
     *     tags: list<string>,
     *     weights: list<array{label: string, price: float}>,
     *     images: list<int>
     * }>
     */
    private const PRODUCTS = [
        [
            'name' => 'Chicken Breast Boneless',
            'slug' => 'chicken-breast-boneless',
            'category' => 'Chicken',
            'featured' => true,
            'description' => '<p>Soft, lean boneless chicken breast — perfect for grills, salads and healthy meals.</p>',
            'meta_title' => 'Buy Fresh Chicken Breast Boneless Online',
            'meta_description' => 'Premium boneless chicken breast, cleaned and packed fresh.',
            'meta_keywords' => 'chicken breast, boneless chicken, fresh chicken',
            'tags' => ['Fresh', 'Bestseller', 'Premium'],
            'weights' => [['label' => '500 g', 'price' => 189], ['label' => '1 kg', 'price' => 359]],
            'images' => [6107734, 5769377, 6107720, 13422436, 6107719],
        ],
        [
            'name' => 'Chicken Curry Cut',
            'slug' => 'chicken-curry-cut',
            'category' => 'Chicken',
            'featured' => true,
            'description' => '<p>Classic curry cut with bone — ideal for home-style chicken curry and biryani.</p>',
            'meta_title' => 'Chicken Curry Cut Fresh',
            'meta_description' => 'Fresh chicken curry cut pieces for everyday cooking.',
            'meta_keywords' => 'chicken curry cut, desi chicken',
            'tags' => ['Fresh', 'Bestseller'],
            'weights' => [['label' => '500 g', 'price' => 129], ['label' => '1 kg', 'price' => 249]],
            'images' => [5769377, 6107720, 13422436, 6107719, 3611843],
        ],
        [
            'name' => 'Chicken Drumsticks',
            'slug' => 'chicken-drumsticks',
            'category' => 'Chicken',
            'featured' => false,
            'description' => '<p>Juicy drumsticks for fry, tandoori and party snacks.</p>',
            'meta_title' => 'Fresh Chicken Drumsticks',
            'meta_description' => 'Cleaned chicken drumsticks packed fresh.',
            'meta_keywords' => 'chicken drumstick, leg piece',
            'tags' => ['Fresh', 'New'],
            'weights' => [['label' => '500 g', 'price' => 149], ['label' => '1 kg', 'price' => 279]],
            'images' => [6107720, 13422436, 6107719, 3611843, 3298686],
        ],
        [
            'name' => 'Chicken Wings',
            'slug' => 'chicken-wings',
            'category' => 'Chicken',
            'featured' => false,
            'description' => '<p>Party-favourite wings — great for air-fryer and hot sauce recipes.</p>',
            'meta_title' => 'Chicken Wings Fresh Cut',
            'meta_description' => 'Fresh chicken wings for snacks and starters.',
            'meta_keywords' => 'chicken wings, snack chicken',
            'tags' => ['Fresh', 'Sale'],
            'weights' => [['label' => '500 g', 'price' => 169], ['label' => '1 kg', 'price' => 319]],
            'images' => [13422436, 6107719, 3611843, 3298686, 6107734],
        ],
        [
            'name' => 'Mutton Curry Cut',
            'slug' => 'mutton-curry-cut',
            'category' => 'Mutton',
            'featured' => true,
            'description' => '<p>Tender mutton curry cut with bone — slow-cook for rich gravies.</p>',
            'meta_title' => 'Fresh Mutton Curry Cut',
            'meta_description' => 'Goat mutton curry cut, cleaned and fresh.',
            'meta_keywords' => 'mutton curry cut, goat meat',
            'tags' => ['Fresh', 'Premium', 'Bestseller'],
            'weights' => [['label' => '500 g', 'price' => 449], ['label' => '1 kg', 'price' => 879]],
            'images' => [31732111, 31732115, 769289, 7282325, 3611843],
        ],
        [
            'name' => 'Mutton Leg Pieces',
            'slug' => 'mutton-leg-pieces',
            'category' => 'Mutton',
            'featured' => false,
            'description' => '<p>Leg cuts for roasts, paya-style dishes and special weekend meals.</p>',
            'meta_title' => 'Mutton Leg Pieces Online',
            'meta_description' => 'Fresh mutton leg pieces for traditional recipes.',
            'meta_keywords' => 'mutton leg, raan cut',
            'tags' => ['Fresh', 'Premium'],
            'weights' => [['label' => '500 g', 'price' => 499], ['label' => '1 kg', 'price' => 969]],
            'images' => [31732115, 769289, 7282325, 3611843, 31732111],
        ],
        [
            'name' => 'Rohu Fish Fillet',
            'slug' => 'rohu-fish-fillet',
            'category' => 'Fish',
            'featured' => true,
            'description' => '<p>Clean rohu fillets — less bone, quick weekday fish curry.</p>',
            'meta_title' => 'Fresh Rohu Fish Fillet',
            'meta_description' => 'Rohu fish fillet cuts, cleaned for cooking.',
            'meta_keywords' => 'rohu fish, fish fillet',
            'tags' => ['Fresh', 'Bestseller'],
            'weights' => [['label' => '500 g', 'price' => 279], ['label' => '1 kg', 'price' => 539]],
            'images' => [7269615, 7625055, 3298686, 2147842, 128401],
        ],
        [
            'name' => 'Basa Fish Steak',
            'slug' => 'basa-fish-steak',
            'category' => 'Fish',
            'featured' => false,
            'description' => '<p>Mild basa steaks — pan-fry friendly, kid-friendly taste.</p>',
            'meta_title' => 'Basa Fish Steak Fresh',
            'meta_description' => 'Boneless basa fish steak portions.',
            'meta_keywords' => 'basa fish, fish steak',
            'tags' => ['Fresh', 'New'],
            'weights' => [['label' => '500 g', 'price' => 299], ['label' => '1 kg', 'price' => 569]],
            'images' => [7625055, 3298686, 2147842, 128401, 7269615],
        ],
        [
            'name' => 'Pomfret Whole Cleaned',
            'slug' => 'pomfret-whole-cleaned',
            'category' => 'Fish',
            'featured' => false,
            'description' => '<p>Whole pomfret cleaned and scaled — fry or tawa masala ready.</p>',
            'meta_title' => 'Pomfret Fish Whole Cleaned',
            'meta_description' => 'Fresh pomfret fish cleaned for home cooking.',
            'meta_keywords' => 'pomfret fish, whole fish',
            'tags' => ['Fresh', 'Premium'],
            'weights' => [['label' => '500 g', 'price' => 449], ['label' => '1 kg', 'price' => 869]],
            'images' => [3298686, 2147842, 128401, 7269615, 7625055],
        ],
        [
            'name' => 'Jumbo Prawns',
            'slug' => 'jumbo-prawns',
            'category' => 'Prawns',
            'featured' => true,
            'description' => '<p>Large jumbo prawns — butter garlic, grill or curry.</p>',
            'meta_title' => 'Jumbo Prawns Fresh',
            'meta_description' => 'Fresh jumbo prawns with shell on.',
            'meta_keywords' => 'jumbo prawns, seafood',
            'tags' => ['Jumbo', 'Fresh', 'Bestseller'],
            'weights' => [['label' => '250 g', 'price' => 349], ['label' => '500 g', 'price' => 679]],
            'images' => [566345, 4518583, 3900662, 5665663, 143133],
        ],
        [
            'name' => 'Peeled & Deveined Prawns',
            'slug' => 'peeled-deveined-prawns',
            'category' => 'Prawns',
            'featured' => false,
            'description' => '<p>Ready-to-cook peeled prawns — saves prep time in the kitchen.</p>',
            'meta_title' => 'Peeled Deveined Prawns',
            'meta_description' => 'Clean peeled and deveined prawns.',
            'meta_keywords' => 'peeled prawns, deveined prawns',
            'tags' => ['Fresh', 'Premium'],
            'weights' => [['label' => '250 g', 'price' => 399], ['label' => '500 g', 'price' => 769]],
            'images' => [4518583, 3900662, 5665663, 143133, 566345],
        ],
        [
            'name' => 'Fresh Blue Crab',
            'slug' => 'fresh-blue-crab',
            'category' => 'Crab',
            'featured' => false,
            'description' => '<p>Live-fresh blue crab — perfect for coastal-style masala crab.</p>',
            'meta_title' => 'Fresh Blue Crab',
            'meta_description' => 'Whole blue crab cleaned for cooking.',
            'meta_keywords' => 'blue crab, crab seafood',
            'tags' => ['Fresh', 'Jumbo'],
            'weights' => [['label' => '500 g', 'price' => 499], ['label' => '1 kg', 'price' => 949]],
            'images' => [8352789, 10432614, 39035453, 11161344, 2807243],
        ],
        [
            'name' => 'Lobster Tail',
            'slug' => 'lobster-tail',
            'category' => 'Lobster',
            'featured' => true,
            'description' => '<p>Premium lobster tail portions — grill, bake or butter garlic.</p>',
            'meta_title' => 'Lobster Tail Fresh',
            'meta_description' => 'Fresh lobster tail cuts for gourmet meals.',
            'meta_keywords' => 'lobster tail, seafood premium',
            'tags' => ['Premium', 'Fresh', 'Bestseller'],
            'weights' => [['label' => '250 g', 'price' => 899], ['label' => '500 g', 'price' => 1749]],
            'images' => [566566, 1559707, 5665733, 39035453, 7269615],
        ],
        [
            'name' => 'Duck Breast Fillet',
            'slug' => 'duck-breast-fillet',
            'category' => 'Duck',
            'featured' => false,
            'description' => '<p>Rich duck breast fillet — restaurant-style pan sear at home.</p>',
            'meta_title' => 'Duck Breast Fillet Fresh',
            'meta_description' => 'Boneless duck breast fillet portions.',
            'meta_keywords' => 'duck breast, duck meat',
            'tags' => ['Premium', 'Fresh'],
            'weights' => [['label' => '400 g', 'price' => 549], ['label' => '800 g', 'price' => 1049]],
            'images' => [24038058, 29296209, 36061359, 29333561, 18510261],
        ],
        [
            'name' => 'Turkey Breast Slice',
            'slug' => 'turkey-breast-slice',
            'category' => 'Turkey',
            'featured' => false,
            'description' => '<p>Lean turkey breast slices — sandwiches, salads and high-protein meals.</p>',
            'meta_title' => 'Turkey Breast Slice Fresh',
            'meta_description' => 'Fresh turkey breast slices packed hygienically.',
            'meta_keywords' => 'turkey breast, lean meat',
            'tags' => ['Fresh', 'Premium'],
            'weights' => [['label' => '500 g', 'price' => 429], ['label' => '1 kg', 'price' => 829]],
            'images' => [2673353, 35703325, 5937501, 3611843, 3298686],
        ],
        [
            'name' => 'Farm Fresh Eggs (12 Pcs)',
            'slug' => 'farm-fresh-eggs-12',
            'category' => 'Eggs',
            'featured' => true,
            'description' => '<p>12 farm fresh eggs — daily breakfast and baking essential.</p>',
            'meta_title' => 'Farm Fresh Eggs 12 Pack',
            'meta_description' => 'Grade-A farm fresh eggs tray of 12.',
            'meta_keywords' => 'fresh eggs, farm eggs',
            'tags' => ['Fresh', 'Bestseller'],
            'weights' => [['label' => '12 pcs', 'price' => 89], ['label' => '30 pcs', 'price' => 219]],
            'images' => [769969, 1407347, 6999530, 931755, 2147842],
        ],
        [
            'name' => 'Chicken Sausages Pack',
            'slug' => 'chicken-sausages-pack',
            'category' => 'Sausages',
            'featured' => false,
            'description' => '<p>Juicy chicken sausages — pan fry, hot dogs and quick snacks.</p>',
            'meta_title' => 'Chicken Sausages Pack',
            'meta_description' => 'Ready chicken sausages pack for quick meals.',
            'meta_keywords' => 'chicken sausages, cold cuts',
            'tags' => ['Fresh', 'Sale'],
            'weights' => [['label' => '250 g', 'price' => 149], ['label' => '500 g', 'price' => 279]],
            'images' => [4495633, 4113612, 7225700, 5937501, 3611843],
        ],
        [
            'name' => 'Premium Mutton Keema',
            'slug' => 'premium-mutton-keema',
            'category' => 'Keema',
            'featured' => true,
            'description' => '<p>Fine minced mutton keema — samosa, keema matar and paratha stuffing.</p>',
            'meta_title' => 'Premium Mutton Keema',
            'meta_description' => 'Fresh minced mutton keema, double cleaned.',
            'meta_keywords' => 'mutton keema, minced mutton',
            'tags' => ['Premium', 'Bestseller', 'Fresh'],
            'weights' => [['label' => '500 g', 'price' => 399], ['label' => '1 kg', 'price' => 779]],
            'images' => [7225700, 7225384, 128401, 31732111, 769289],
        ],
        [
            'name' => 'Tandoori Chicken Marinated',
            'slug' => 'tandoori-chicken-marinated',
            'category' => 'Ready to Cook',
            'featured' => true,
            'description' => '<p>Secret-spice tandoori marinade — oven, air fryer or tawa ready.</p>',
            'meta_title' => 'Tandoori Chicken Marinated Ready to Cook',
            'meta_description' => 'Marinated tandoori chicken ready for cooking.',
            'meta_keywords' => 'tandoori chicken, marinated chicken',
            'tags' => ['Bestseller', 'New', 'Fresh'],
            'weights' => [['label' => '500 g', 'price' => 1], ['label' => '1 kg', 'price' => 1]],
            'images' => [5937501, 6107734, 5769377, 3611843, 3298686],
        ],
        [
            'name' => 'Fish Fry Marinated Pack',
            'slug' => 'fish-fry-marinated-pack',
            'category' => 'Ready to Cook',
            'featured' => false,
            'description' => '<p>Coastal-style fish fry marinade — shallow fry in minutes.</p>',
            'meta_title' => 'Fish Fry Marinated Pack',
            'meta_description' => 'Ready to cook marinated fish fry pack.',
            'meta_keywords' => 'fish fry, marinated fish',
            'tags' => ['Fresh', 'Sale'],
            'weights' => [['label' => '500 g', 'price' => 299], ['label' => '1 kg', 'price' => 569]],
            'images' => [5937501, 7269615, 7625055, 3298686, 2147842],
        ],
    ];

    public function run(): void
    {
        $this->call(TagSeeder::class);

        $categories = Category::query()->pluck('id', 'name');

        foreach (self::PRODUCTS as $item) {
            $categoryId = $categories[$item['category']] ?? null;
            if (! $categoryId) {
                $this->command?->warn('Skip '.$item['name'].' — category missing: '.$item['category']);

                continue;
            }

            $product = Product::query()->updateOrCreate(
                ['name' => $item['name']],
                [
                    'category_id' => $categoryId,
                    'banner_id' => null,
                    'description' => $item['description'],
                    'is_featured' => $item['featured'],
                    'status' => 1,
                    'meta_title' => $item['meta_title'],
                    'meta_description' => $item['meta_description'],
                    'meta_keywords' => $item['meta_keywords'],
                ]
            );

            $this->syncWeights($product, $item['weights']);
            $this->syncTags($product, $item['tags']);
            $this->syncImages($product, $item['slug'], $item['images']);

            $this->command?->info('Product: '.$item['name'].' ('.$product->images()->count().' images)');
        }
    }

    /**
     * @param  list<array{label: string, price: float}>  $weights
     */
    private function syncWeights(Product $product, array $weights): void
    {
        $product->weights()->delete();

        foreach ($weights as $index => $row) {
            ProductWeight::query()->create([
                'product_id' => $product->id,
                'weight_label' => $row['label'],
                'price' => $row['price'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * @param  list<string>  $tagNames
     */
    private function syncTags(Product $product, array $tagNames): void
    {
        $ids = [];
        foreach ($tagNames as $name) {
            $tag = Tag::query()->firstOrCreate(['name' => $name], ['status' => 1]);
            $ids[] = $tag->id;
        }
        $product->tags()->sync($ids);
    }

    /**
     * @param  list<int>  $pexelsIds
     */
    private function syncImages(Product $product, string $slug, array $pexelsIds): void
    {
        $product->images->each(function (ProductImage $img): void {
            $img->deleteFile();
            $img->delete();
        });

        Storage::disk('public')->makeDirectory('products/'.$slug);

        foreach (array_values($pexelsIds) as $index => $pexelsId) {
            $fileIndex = $index + 1;
            $relative = 'products/'.$slug.'/'.$fileIndex.'.jpg';
            $this->copyImageToStorage($slug, $fileIndex, $pexelsId, $relative);

            ProductImage::query()->create([
                'product_id' => $product->id,
                'path' => $relative,
                'sort_order' => $index,
            ]);
        }
    }

    private function copyImageToStorage(string $slug, int $fileIndex, int $pexelsId, string $relative): void
    {
        $bundled = database_path('seeders/assets/products/'.$slug.'/'.$fileIndex.'.jpg');

        if (File::isFile($bundled) && $this->isValidImage($body = File::get($bundled))) {
            Storage::disk('public')->put($relative, $body);

            return;
        }

        $downloaded = $this->downloadFromPexels($pexelsId);
        Storage::disk('public')->put($relative, $downloaded);

        File::ensureDirectoryExists(dirname($bundled));
        File::put($bundled, $downloaded);
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

        if (! $response->successful() || ! $this->isValidImage($body)) {
            throw new \RuntimeException("Pexels download failed for ID {$pexelsId} (HTTP {$response->status()}).");
        }

        return $body;
    }

    private function isValidImage(string $body): bool
    {
        if (strlen($body) < 20_000) {
            return false;
        }

        return str_starts_with($body, "\xFF\xD8\xFF") || str_starts_with($body, "\x89PNG\r\n\x1a\n");
    }
}
