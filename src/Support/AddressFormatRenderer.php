<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Data\AddressData;
use InvalidArgumentException;

/**
 * Data-driven address renderer.
 *
 * Country formatters delegate here with their definitions from
 * address-formats.json. Line specs:
 * - {"each": [...]} spread each present field onto its own line.
 * - {"join": [...], "sep": " "} join present fields onto one line.
 * - {"country": true} the resolved country line.
 * - "if"/"unless"/"unlessAll" gate a line on fields' presence
 *   (string, list, or nested spec): every "if" present, no "unless"
 *   present, and not every "unlessAll" present activates the line.
 *
 * Field grammar "base" or "base:op[:arg]" with chainable ops: abbr
 * (state map lookup), upper, components:KEY, !literal (skip-if-equal),
 * !dup:FIELD (skip-if-equal-to-field), in:A,B (keep-if-member,
 * case-insensitive). "country" resolves through the standard chain:
 * supplied country, display name for the own code, raw code.
 * Nested {"join": ...} groups, {"alt": [...]} first-present picks,
 * and {"lit": "..."} literals compose inside joins. Unknown fields, ops,
 * and malformed lines throw InvalidArgumentException instead of
 * silently dropping output.
 */
final class AddressFormatRenderer
{
    private static ?array $definitions = null;

    /**
     * @param  list<int>|null  $firedLines  @internal testing seam: receives the
     *                                      indexes of the definition lines that emitted output, feeding the
     *                                      format line-coverage guard. Omitting it changes nothing.
     *
     * @param-out list<int> $firedLines
     */
    public static function format(string $countryCode, AddressData $address, ?array &$firedLines = null): string
    {
        $code = mb_strtoupper(mb_trim($countryCode));
        $definitions = self::definitions();

        if (! isset($definitions[$code])) {
            throw new InvalidArgumentException("No address format definition for [{$code}].");
        }

        $definition = $definitions[$code];
        $lines = [];
        /** @var list<int> $fired */
        $fired = [];

        foreach ($definition['lines'] as $index => $line) {
            if (isset($line['if']) && ! self::allPresent((array) $line['if'], $address, $definition, $code)) {
                continue;
            }

            if (isset($line['unless']) && self::anyPresent((array) $line['unless'], $address, $definition, $code)) {
                continue;
            }

            if (isset($line['unlessAll']) && self::allPresent((array) $line['unlessAll'], $address, $definition, $code)) {
                continue;
            }

            if (isset($line['country'])) {
                $country = self::country($address, $code, $definition);

                if ($country !== null) {
                    $lines[] = $country;
                    $fired[] = (int) $index;
                }

                continue;
            }

            if (isset($line['each'])) {
                if (! is_array($line['each'])) {
                    throw new InvalidArgumentException("Address format line [{$index}] for [{$code}] needs each as a field list.");
                }

                foreach ($line['each'] as $field) {
                    $value = self::resolve($field, $address, $definition, $code);

                    if ($value !== null) {
                        $lines[] = $value;
                        $fired[] = (int) $index;
                    }
                }

                continue;
            }

            if (! isset($line['join']) || ! is_array($line['join'])) {
                throw new InvalidArgumentException("Address format line [{$index}] for [{$code}] needs each, join, or country.");
            }

            $parts = [];

            foreach ($line['join'] as $field) {
                $value = self::resolve($field, $address, $definition, $code);

                if ($value !== null) {
                    $parts[] = $value;
                }
            }

            if ($parts !== []) {
                $lines[] = implode($line['sep'] ?? ' ', $parts);
                $fired[] = (int) $index;
            }
        }

        $firedLines = $fired;

        return implode("\n", $lines);
    }

    /**
     * @param  list<string|array>  $fields
     */
    private static function allPresent(array $fields, AddressData $address, array $definition, string $code): bool
    {
        foreach ($fields as $field) {
            if (self::resolve($field, $address, $definition, $code) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string|array>  $fields
     */
    private static function anyPresent(array $fields, AddressData $address, array $definition, string $code): bool
    {
        foreach ($fields as $field) {
            if (self::resolve($field, $address, $definition, $code) !== null) {
                return true;
            }
        }

        return false;
    }

    private static function definitions(): array
    {
        if (self::$definitions === null) {
            self::$definitions = json_decode(
                file_get_contents(__DIR__ . '/../../resources/data/address-formats.json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        }

        return self::$definitions;
    }

    private static function resolve(string | array $field, AddressData $address, array $definition, string $code): ?string
    {
        if (is_array($field)) {
            if (isset($field['lit'])) {
                return $field['lit'] === '' ? null : $field['lit'];
            }

            if (isset($field['alt'])) {
                if (! is_array($field['alt'])) {
                    throw new InvalidArgumentException('Address format group needs alt as an option list.');
                }

                foreach ($field['alt'] as $option) {
                    $value = self::resolve($option, $address, $definition, $code);

                    if ($value !== null) {
                        return $value;
                    }
                }

                return null;
            }

            if (! isset($field['join']) || ! is_array($field['join'])) {
                throw new InvalidArgumentException('Address format group needs lit, alt, or join.');
            }

            $parts = [];

            foreach ($field['join'] as $nested) {
                $value = self::resolve($nested, $address, $definition, $code);

                if ($value !== null) {
                    $parts[] = $value;
                }
            }

            return $parts === [] ? null : implode($field['sep'] ?? ' ', $parts);
        }

        $segments = explode(':', $field);
        $base = array_shift($segments);

        if ($base === 'components') {
            $value = self::component(array_shift($segments), $address);
        } elseif ($base === 'country') {
            $value = self::country($address, $code, $definition);
        } else {
            $value = self::base($base, $address);
        }

        while ($value !== null && $segments !== []) {
            $value = self::applyOp(array_shift($segments), $segments, $value, $address, $definition, $code);
        }

        return $value;
    }

    /**
     * @param  list<string>  $rest
     */
    private static function applyOp(string $op, array &$rest, string $value, AddressData $address, array $definition, string $code): ?string
    {
        if ($op === 'abbr') {
            return self::abbreviate($value, $definition['abbreviations'] ?? []);
        }

        if ($op === 'upper') {
            return mb_strtoupper($value);
        }

        if ($op === 'in') {
            $allowed = array_shift($rest);

            if ($allowed === null) {
                throw new InvalidArgumentException('Address format :in needs a comma-separated value list.');
            }

            $folded = mb_strtolower($value);

            foreach (explode(',', $allowed) as $candidate) {
                if (mb_strtolower(mb_trim($candidate)) === $folded) {
                    return $value;
                }
            }

            return null;
        }

        if ($op === '!dup') {
            $ref = array_shift($rest);

            if ($ref === null) {
                throw new InvalidArgumentException('Address format !dup needs a field reference.');
            }

            $other = self::resolve($ref, $address, $definition, $code);

            return $other !== null && mb_strtolower($value) === mb_strtolower($other) ? null : $value;
        }

        if (str_starts_with($op, '!')) {
            $literal = mb_substr($op, 1);

            return mb_strtolower($value) === mb_strtolower($literal) ? null : $value;
        }

        throw new InvalidArgumentException("Unknown address format op [{$op}].");
    }

    private static function component(?string $key, AddressData $address): ?string
    {
        $value = $key === null ? null : ($address->components[$key] ?? null);

        if (! is_scalar($value)) {
            return null;
        }

        $value = mb_trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function base(string $base, AddressData $address): ?string
    {
        $value = match ($base) {
            'line1' => $address->line1,
            'line2' => $address->line2,
            'line3' => $address->line3,
            'city' => $address->city,
            'state' => $address->state,
            'postcode' => $address->postcode,
            default => throw new InvalidArgumentException("Unknown address format field [{$base}]."),
        };

        if ($value === null) {
            return null;
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    private static function country(AddressData $address, string $code, array $definition): ?string
    {
        if ($address->country !== null && $address->country !== '') {
            return $address->country;
        }

        if ($address->countryCode !== null && $address->countryCode !== '') {
            return mb_strtoupper($address->countryCode) === $code
                ? $definition['display']
                : $address->countryCode;
        }

        return null;
    }

    private static function abbreviate(string $value, array $map): string
    {
        $upper = mb_strtoupper($value);

        if (in_array($upper, $map, true)) {
            return $upper;
        }

        if (isset($map[$value])) {
            return $map[$value];
        }

        $folded = mb_strtolower($value);

        foreach ($map as $name => $abbreviation) {
            if (mb_strtolower($name) === $folded) {
                return $abbreviation;
            }
        }

        return $value;
    }
}
