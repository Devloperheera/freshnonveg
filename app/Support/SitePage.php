<?php

namespace App\Support;

use App\Models\Page;
use App\Models\PageContent;
use Illuminate\Support\Str;

final class SitePage
{
    /** @var array<string, string> slug => canonical page name in admin (Pages) */
    public const SLUGS = [
        'terms-conditions' => 'Terms & Conditions',
        'privacy-policy' => 'Privacy Policy',
        'refund-policy' => 'Refund Policy',
        'contact' => 'Contact',
        'blog' => 'Blog',
    ];

    public static function slugPattern(): string
    {
        return implode('|', array_keys(self::SLUGS));
    }

    public static function slugForPageName(string $name): ?string
    {
        foreach (self::SLUGS as $slug => $canonical) {
            if (strcasecmp($name, $canonical) === 0) {
                return $slug;
            }
        }

        $normalized = Str::slug($name);

        return array_key_exists($normalized, self::SLUGS) ? $normalized : null;
    }

    public static function pageNameForSlug(string $slug): ?string
    {
        return self::SLUGS[$slug] ?? null;
    }

    public static function findPublishedContent(string $slug): ?PageContent
    {
        $canonical = self::pageNameForSlug($slug);
        if ($canonical === null) {
            return null;
        }

        $page = Page::query()
            ->where('status', 1)
            ->where(function ($query) use ($canonical, $slug): void {
                $query->where('name', $canonical);

                if ($slug === 'terms-conditions') {
                    $query->orWhere('name', 'like', '%term%');
                }

                if ($slug === 'contact') {
                    $query->orWhere('name', 'like', '%contact%');
                }
            })
            ->orderByRaw('CASE WHEN name = ? THEN 0 ELSE 1 END', [$canonical])
            ->first();

        if (! $page) {
            return null;
        }

        return PageContent::query()
            ->where('page_id', $page->id)
            ->where('status', 1)
            ->orderByDesc('id')
            ->first();
    }
}
