<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class AddressCountryData
{
    public function __construct(
        public string $iso2,
        public ?string $iso3 = null,
        public ?string $numericCode = null,
        public string $name = '',
        public ?string $nativeName = null,
        public ?string $emoji = null,
        public ?string $phoneCode = null,
        public ?string $capital = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $region = null,
        public ?string $subregion = null,
        public array $translations = [],
    ) {}
}
