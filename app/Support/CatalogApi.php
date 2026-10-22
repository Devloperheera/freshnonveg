<?php

namespace App\Support;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductWeight;
use App\Models\Tag;
use Illuminate\Support\Str;

class CatalogApi
{
    /**
     * @return array<string, mixed>
     */
    public static function banner(Banner $banner): array
    {
        return [
            'id' => $banner->id,
            'name' => $banner->name,
            'image' => $banner->imageUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function category(Category $category, bool $withProductCount = false): array
    {
        $payload = [
            'id' => $category->id,
            'name' => $category->name,
            'image' => $category->imageUrl(),
        ];

        if ($withProductCount && isset($category->products_count)) {
            $payload['products_count'] = (int) $category->products_count;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function tag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function weight(ProductWeight $weight): array
    {
        return [
            'id' => $weight->id,
            'weight_label' => $weight->weight_label,
            'price' => (float) $weight->price,
            'sort_order' => (int) $weight->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function productImage(ProductImage $image): array
    {
        return [
            'id' => $image->id,
            'url' => $image->url(),
            'sort_order' => (int) $image->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function productSummary(Product $product): array
    {
        $product->loadMissing(['images', 'weights', 'tags', 'category']);

        $fromPrice = $product->weights->min('price');
        $image = $product->images->first();
        $excerptSource = $product->meta_description ?: strip_tags((string) $product->description);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'food_type' => 'non_veg',
            'food_mark' => [
                'type' => 'non_veg',
                'color' => '#C62828',
                'label' => 'Non-Vegetarian',
                'label_hi' => 'नॉन-वेज',
            ],
            'is_featured' => (bool) $product->is_featured,
            'excerpt' => Str::limit(trim($excerptSource), 120) ?: null,
            'from_price' => $fromPrice !== null ? (float) $fromPrice : null,
            'image' => $image?->url(),
            'category' => $product->category
                ? self::category($product->category)
                : null,
            'tags' => $product->tags->map(fn (Tag $tag) => self::tag($tag))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function productDetail(Product $product): array
    {
        $product->loadMissing(['images', 'weights', 'tags', 'category']);

        $summary = self::productSummary($product);

        return array_merge($summary, [
            'description' => $product->description,
            'video_url' => $product->videoUrl(),
            'banner_id' => $product->banner_id,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'meta_keywords' => $product->meta_keywords,
            'images' => $product->images->map(fn (ProductImage $img) => self::productImage($img))->values()->all(),
            'weights' => $product->weights->map(fn (ProductWeight $w) => self::weight($w))->values()->all(),
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ]);
    }
}
