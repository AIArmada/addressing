<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Canada;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class CanadaAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'CA';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: locality, province abbreviation, and ANA NAN postcode on one line.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        $stateAbbr = $state !== null ? self::stateAbbreviation($state) : null;
        $postalCode = $postcode !== null ? mb_strtoupper($postcode) : null;
        $localityLine = implode(' ', array_filter([$city, $stateAbbr, $postalCode], static fn (?string $v): bool => $v !== null && $v !== ''));

        if ($localityLine !== '') {
            $lines[] = $localityLine;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'CA' ? 'Canada' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    /**
     * @var array<string, string>
     */
    private const array STATE_ABBREVIATIONS = [
        'Alberta' => 'AB',
        'British Columbia' => 'BC',
        'Manitoba' => 'MB',
        'New Brunswick' => 'NB',
        'Newfoundland and Labrador' => 'NL',
        'Northwest Territories' => 'NT',
        'Nova Scotia' => 'NS',
        'Nunavut' => 'NU',
        'Ontario' => 'ON',
        'Prince Edward Island' => 'PE',
        'Quebec' => 'QC',
        'Québec' => 'QC',
        'Saskatchewan' => 'SK',
        'Yukon' => 'YT',
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
