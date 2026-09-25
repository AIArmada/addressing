<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\UnitedStates;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class UnitedStatesAddressFormatter implements CountryAddressFormatter
{
    public static function countryCode(): string
    {
        return 'US';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // USPS Publication 28: locality, state abbreviation, and ZIP on one line.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        $stateAbbr = $state !== null ? self::stateAbbreviation($state) : null;
        $localityLine = implode(' ', array_filter([$city, $stateAbbr, $postcode], static fn (?string $v): bool => $v !== null && $v !== ''));

        if ($localityLine !== '') {
            $lines[] = $localityLine;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'US' ? 'United States' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    /**
     * USPS Publication 28 state, territory, and military abbreviations.
     *
     * @var array<string, string>
     */
    private const array STATE_ABBREVIATIONS = [
        'Alabama' => 'AL',
        'Alaska' => 'AK',
        'American Samoa' => 'AS',
        'Arizona' => 'AZ',
        'Arkansas' => 'AR',
        'California' => 'CA',
        'Colorado' => 'CO',
        'Connecticut' => 'CT',
        'Delaware' => 'DE',
        'District of Columbia' => 'DC',
        'Florida' => 'FL',
        'Georgia' => 'GA',
        'Guam' => 'GU',
        'Hawaii' => 'HI',
        'Idaho' => 'ID',
        'Illinois' => 'IL',
        'Indiana' => 'IN',
        'Iowa' => 'IA',
        'Kansas' => 'KS',
        'Kentucky' => 'KY',
        'Louisiana' => 'LA',
        'Maine' => 'ME',
        'Maryland' => 'MD',
        'Massachusetts' => 'MA',
        'Michigan' => 'MI',
        'Minnesota' => 'MN',
        'Mississippi' => 'MS',
        'Missouri' => 'MO',
        'Montana' => 'MT',
        'Nebraska' => 'NE',
        'Nevada' => 'NV',
        'New Hampshire' => 'NH',
        'New Jersey' => 'NJ',
        'New Mexico' => 'NM',
        'New York' => 'NY',
        'North Carolina' => 'NC',
        'North Dakota' => 'ND',
        'Northern Mariana Islands' => 'MP',
        'Ohio' => 'OH',
        'Oklahoma' => 'OK',
        'Oregon' => 'OR',
        'Pennsylvania' => 'PA',
        'Puerto Rico' => 'PR',
        'Rhode Island' => 'RI',
        'South Carolina' => 'SC',
        'South Dakota' => 'SD',
        'Tennessee' => 'TN',
        'Texas' => 'TX',
        'Utah' => 'UT',
        'Vermont' => 'VT',
        'Virgin Islands' => 'VI',
        'United States Virgin Islands' => 'VI',
        'Virginia' => 'VA',
        'Washington' => 'WA',
        'West Virginia' => 'WV',
        'Wisconsin' => 'WI',
        'Wyoming' => 'WY',
        'Armed Forces Americas' => 'AA',
        'Armed Forces of the Americas' => 'AA',
        'Armed Forces Europe' => 'AE',
        'Armed Forces Pacific' => 'AP',
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
