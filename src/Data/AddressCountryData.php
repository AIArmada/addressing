<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

class AddressCountryData
{
    public function __construct(
        public readonly string $iso2,
        public readonly ?string $iso3 = null,
        public readonly ?string $numericCode = null,
        public readonly string $entityType = 'country',
        public readonly ?bool $isIndependent = null,
        public readonly string $name = '',
        public readonly ?string $officialName = null,
        public readonly ?string $commonName = null,
        public readonly ?string $nativeName = null,
        public readonly ?string $emoji = null,
        public readonly ?string $phoneCode = null,
        public readonly array $callingCodes = [],
        public readonly ?string $capital = null,
        public readonly ?float $capitalLatitude = null,
        public readonly ?float $capitalLongitude = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $region = null,
        public readonly ?string $subregion = null,
        public readonly array $currencyCodes = [],
        public readonly ?string $defaultCurrencyCode = null,
        public readonly array $languageCodes = [],
        public readonly array $timezones = [],
        public readonly array $topLevelDomains = [],
        public readonly array $metadata = [],
    ) {}
}
