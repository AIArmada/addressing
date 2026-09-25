<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Australia;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class AustraliaAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'AU';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: locality, state abbreviation, and postcode separated by two spaces.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        $stateAbbr = $state !== null ? self::stateAbbreviation($state) : null;
        $localityLine = implode('  ', array_filter([$city, $stateAbbr, $postcode], static fn (?string $v): bool => $v !== null && $v !== ''));

        if ($localityLine !== '') {
            $lines[] = $localityLine;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'AU' ? 'Australia' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    /**
     * @var array<string, string>
     */
    private const array STATE_ABBREVIATIONS = [
        'Australian Capital Territory' => 'ACT',
        'New South Wales' => 'NSW',
        'Northern Territory' => 'NT',
        'Queensland' => 'QLD',
        'South Australia' => 'SA',
        'Tasmania' => 'TAS',
        'Victoria' => 'VIC',
        'Western Australia' => 'WA',
    ];

    private static function stateAbbreviation(string $state): string
    {
        $upper = mb_strtoupper($state);

        if (in_array($upper, self::STATE_ABBREVIATIONS, true)) {
            return $upper;
        }

        return self::STATE_ABBREVIATIONS[$state] ?? $state;
    }

    private static function textOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }
}
