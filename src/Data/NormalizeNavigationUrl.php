<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class NormalizeNavigationUrl
{
    public function normalize(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = mb_trim($url);

        if ($url === '') {
            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        $scheme = mb_strtolower((string) ($parts['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }
}
