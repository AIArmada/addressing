<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

final class UsZipCodeKeys
{
    /** @return list<string> */
    public static function expand(string $code): array
    {
        $code = mb_strtoupper(mb_trim($code));

        // Bundled codes are 5-digit ZIPs; ZIP+4 suffixes strip to base.
        if (preg_match('/^(\d{5})-?(\d{4})$/', $code, $matches) === 1) {
            return [$code, $matches[1]];
        }

        return [$code];
    }
}
