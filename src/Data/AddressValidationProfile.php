<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

class AddressValidationProfile
{
    /**
     * @param  list<string>  $required  AddressData fields required for the country.
     * @param  list<string>  $upper  AddressData fields conventionally uppercased (informational).
     */
    public function __construct(
        public readonly string $countryCode,
        public readonly ?string $pattern = null,
        public readonly array $required = [],
        public readonly array $upper = [],
    ) {}
}
