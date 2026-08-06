<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class SafePublicUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $url = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($url === '') {
            return;
        }

        $decoded = rawurldecode($url);
        if (preg_match('/[\x00-\x1F\x7F]/', $decoded) || str_starts_with($decoded, '//') || str_starts_with($decoded, '\\')) {
            $fail('URL tidak valid.');

            return;
        }

        if (str_starts_with($decoded, '/')) {
            return;
        }

        $parts = parse_url($decoded);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            $fail('URL harus berupa path lokal atau HTTP(S) yang valid.');
        }
    }
}
