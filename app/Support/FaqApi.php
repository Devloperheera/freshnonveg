<?php

namespace App\Support;

use App\Models\Faq;

class FaqApi
{
    /**
     * Website home FAQ block (`Website.Components.faq`).
     *
     * @return array{section: array{title: string, subtitle: string}, items: list<array<string, mixed>>}
     */
    public static function payload(): array
    {
        $items = Faq::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Faq $faq) => self::item($faq))
            ->values()
            ->all();

        return [
            'section' => [
                'title' => 'अक्सर पूछे गए <u>सवाल</u> ❓',
                'subtitle' => 'FAQ',
            ],
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function item(Faq $faq): array
    {
        return [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
            'sort_order' => (int) $faq->sort_order,
        ];
    }
}
