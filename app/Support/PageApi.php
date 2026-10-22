<?php

namespace App\Support;

use App\Models\PageContent;

class PageApi
{
    /**
     * @return array<string, mixed>
     */
    public static function summary(string $slug, PageContent $content): array
    {
        $content->loadMissing('page');

        return [
            'slug' => $slug,
            'page_name' => SitePage::pageNameForSlug($slug),
            'title' => $content->title,
            'meta_title' => $content->meta_title,
            'meta_description' => $content->meta_description,
            'updated_at' => $content->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function detail(string $slug, PageContent $content): array
    {
        return array_merge(self::summary($slug, $content), [
            'content' => $content->content,
            'page_id' => $content->page_id,
            'content_id' => $content->id,
        ]);
    }
}
