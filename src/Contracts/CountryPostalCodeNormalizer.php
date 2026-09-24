<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface CountryPostalCodeNormalizer
{
    /**
     * Expand a user-supplied postcode into stored-code lookup keys.
     *
     * Bundled postcodes are stored at base level; suffixed formats
     * (Argentina CPA block faces) resolve through their base here.
     *
     * @return list<string> most-specific first.
     */
    public function postalCodeLookupKeys(string $code): array;
}
