<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface CountryAddressFormatter extends AddressFormatter
{
    public static function countryCode(): string;
}
