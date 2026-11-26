<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes\Api;

interface PostalAreaLookup
{
    /** @return list<string> */
    public function candidateAreaIds(string $countryCode, string $code, ?string $countryId = null): array;
}
