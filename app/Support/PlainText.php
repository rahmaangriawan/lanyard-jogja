<?php

namespace App\Support;

final class PlainText
{
    public static function normalize(?string $value): string
    {
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        $value = preg_replace("/\\r\\n?|\\x{2028}|\\x{2029}/u", "\n", $value) ?? '';
        $value = preg_replace('/[^\S\n]+/u', ' ', $value) ?? '';
        $value = preg_replace('/\n{3,}/', "\n\n", $value) ?? '';

        return trim($value);
    }
}
