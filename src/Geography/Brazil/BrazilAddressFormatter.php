<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Brazil;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressLineFilter;

final class BrazilAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'BR';
    }

    public function format(AddressData $address): string
    {
        $lines = AddressLineFilter::present([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: locality and state abbreviation, 8-digit NNNNN-NNN postcode below.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        $stateAbbr = $state !== null ? self::stateAbbreviation($state) : null;

        if ($city !== null && $stateAbbr !== null) {
            $lines[] = $city . ' - ' . $stateAbbr;
        } elseif ($city !== null) {
            $lines[] = $city;
        } elseif ($stateAbbr !== null) {
            $lines[] = $stateAbbr;
        }

        if ($postcode !== null) {
            $lines[] = $postcode;
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'BR' ? 'Brazil' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    /**
     * @var array<string, string>
     */
    private const array STATE_ABBREVIATIONS = [
        'Acre' => 'AC',
        'Alagoas' => 'AL',
        'Amapá' => 'AP',
        'Amazonas' => 'AM',
        'Bahia' => 'BA',
        'Ceará' => 'CE',
        'Distrito Federal' => 'DF',
        'Espírito Santo' => 'ES',
        'Goiás' => 'GO',
        'Maranhão' => 'MA',
        'Mato Grosso' => 'MT',
        'Mato Grosso do Sul' => 'MS',
        'Minas Gerais' => 'MG',
        'Pará' => 'PA',
        'Paraíba' => 'PB',
        'Paraná' => 'PR',
        'Pernambuco' => 'PE',
        'Piauí' => 'PI',
        'Rio de Janeiro' => 'RJ',
        'Rio Grande do Norte' => 'RN',
        'Rio Grande do Sul' => 'RS',
        'Rondônia' => 'RO',
        'Roraima' => 'RR',
        'Santa Catarina' => 'SC',
        'São Paulo' => 'SP',
        'Sergipe' => 'SE',
        'Tocantins' => 'TO',
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
