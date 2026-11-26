<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressValidationProfile
{
    /**
     * @param  list<string>  $required  AddressData fields required for the country.
     * @param  list<string>  $upper  AddressData fields conventionally uppercased (informational).
     */
    public function __construct(
        public string $countryCode,
        public ?string $pattern = null,
        public array $required = [],
        public array $upper = [],
    ) {}
}
