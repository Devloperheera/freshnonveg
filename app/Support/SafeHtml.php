<?php

namespace App\Support;

class SafeHtml
{
    public static function display(?string $html): string
    {
        $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('#<(script|iframe|object|embed|link|meta|style|form|svg)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = strip_tags($html, '<p><br><br/><strong><b><em><i><u><ul><ol><li><h2><h3><h4><h5><a><span><div><blockquote>');
        $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? '';
        $html = preg_replace('/data\s*:/i', '', $html) ?? '';

        return $html;
    }
}
