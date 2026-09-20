<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Geography\Mexico;

use AIArmada\Addressing\Contracts\CountryAddressFormatter;
use AIArmada\Addressing\Data\AddressData;

final class MexicoAddressFormatter implements CountryAddressFormatter
{
    public function countryCode(): string
    {
        return 'MX';
    }

    public function format(AddressData $address): string
    {
        $lines = array_filter([
            $address->line1,
            $address->line2,
            $address->line3,
        ]);

        // UPU: 5-digit postcode left of locality, state abbreviation after.
        $city = self::textOrNull($address->city);
        $state = self::textOrNull($address->state);
        $postcode = self::textOrNull($address->postcode);

        $stateAbbr = $state !== null ? self::stateAbbreviation($state) : null;

        if ($postcode !== null) {
            if ($city !== null) {
                $lines[] = $postcode . ' ' . $city . ($stateAbbr !== null ? ', ' . $stateAbbr : '');
            } elseif ($stateAbbr !== null) {
                $lines[] = $postcode . ' ' . $stateAbbr;
            } else {
                $lines[] = $postcode;
            }
        } else {
            if ($city !== null) {
                $lines[] = $city;
            }

            if ($state !== null) {
                $lines[] = $state;
            }
        }

        if ($address->country !== null && $address->country !== '') {
            $lines[] = $address->country;
        } elseif ($address->countryCode !== null && $address->countryCode !== '') {
            $lines[] = mb_strtoupper($address->countryCode) === 'MX' ? 'Mexico' : $address->countryCode;
        }

        return implode("\n", $lines);
    }

    /**
     * @var array<string, string>
     */
    private const array STATE_ABBREVIATIONS = [
        'Aguascalientes' => 'AGS',
        'Baja California' => 'BC',
        'Baja California Sur' => 'BCS',
        'Campeche' => 'CAMP',
        'Chiapas' => 'CHIS',
        'Chihuahua' => 'CHIH',
        'Ciudad de México' => 'CDMX',
        'Coahuila de Zaragoza' => 'COAH',
        'Colima' => 'COL',
        'Durango' => 'DGO',
        'Estado de México' => 'EDOMEX',
        'Guanajuato' => 'GTO',
        'Guerrero' => 'GRO',
        'Hidalgo' => 'HGO',
        'Jalisco' => 'JAL',
        'Michoacán de Ocampo' => 'MICH',
        'Morelos' => 'MOR',
        'Nayarit' => 'NAY',
        'Nuevo León' => 'NL',
        'Oaxaca' => 'OAX',
        'Puebla' => 'PUE',
        'Querétaro' => 'QRO',
        'Quintana Roo' => 'Q. ROO',
        'San Luis Potosí' => 'SLP',
        'Sinaloa' => 'SIN',
        'Sonora' => 'SON',
        'Tabasco' => 'TAB',
        'Tamaulipas' => 'TAMPS',
        'Tlaxcala' => 'TLAX',
        'Veracruz de Ignacio de la Llave' => 'VER',
        'Yucatán' => 'YUC',
        'Zacatecas' => 'ZAC',
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
