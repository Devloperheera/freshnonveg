<?php

namespace App\Support;

use App\Models\AdvantageItem;
use App\Models\HomeSection;
use App\Models\OrderStep;
use App\Models\SiteStat;
use App\Models\Testimonial;

class HomeContentApi
{
    /**
     * @return array<string, mixed>
     */
    public static function sectionHeading(?HomeSection $section): ?array
    {
        if ($section === null) {
            return null;
        }

        return [
            'title' => $section->title,
            'subtitle' => $section->subtitle,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function testimonial(Testimonial $testimonial): array
    {
        return [
            'id' => $testimonial->id,
            'name' => $testimonial->name,
            'star_rating' => (int) $testimonial->star_rating,
            'short_description' => $testimonial->short_description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function siteStat(SiteStat $stat): array
    {
        return [
            'id' => $stat->id,
            'value' => (int) $stat->value,
            'label' => $stat->label,
            'sort_order' => (int) $stat->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderStep(OrderStep $step): array
    {
        return [
            'id' => $step->id,
            'title' => $step->title,
            'description' => $step->description,
            'sort_order' => (int) $step->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function advantageItem(AdvantageItem $item): array
    {
        return [
            'id' => $item->id,
            'icon' => $item->icon,
            'title' => $item->title,
            'description' => $item->description,
            'sort_order' => (int) $item->sort_order,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function homePayload(): array
    {
        $testimonials = Testimonial::query()
            ->where('status', 1)
            ->orderByDesc('id')
            ->get();

        $siteStats = SiteStat::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $orderSteps = OrderStep::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $advantageItems = AdvantageItem::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'testimonials' => $testimonials
                ->map(fn (Testimonial $t) => self::testimonial($t))
                ->values()
                ->all(),
            'site_stats' => $siteStats
                ->map(fn (SiteStat $s) => self::siteStat($s))
                ->values()
                ->all(),
            'order_way' => [
                'section' => self::sectionHeading(HomeSection::forKey(HomeSection::KEY_ORDER_WAY)),
                'steps' => $orderSteps
                    ->map(fn (OrderStep $s) => self::orderStep($s))
                    ->values()
                    ->all(),
            ],
            'advantages' => [
                'section' => self::sectionHeading(HomeSection::forKey(HomeSection::KEY_ADVANTAGES)),
                'items' => $advantageItems
                    ->map(fn (AdvantageItem $a) => self::advantageItem($a))
                    ->values()
                    ->all(),
            ],
        ];
    }
}
