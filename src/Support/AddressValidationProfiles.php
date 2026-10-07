<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Data\AddressValidationProfile;
use RuntimeException;

final class AddressValidationProfiles
{
    /** @var array<string, AddressValidationProfile>|null */
    private static ?array $profiles = null;

    public static function forCountry(string $countryCode): ?AddressValidationProfile
    {
        $profiles = self::all();
        $code = mb_strtoupper(mb_trim($countryCode));

        return $profiles[$code] ?? null;
    }

    /**
     * @return array<string, AddressValidationProfile>
     */
    public static function all(?string $path = null): array
    {
        if ($path !== null) {
            return self::load($path);
        }

        if (self::$profiles === null) {
            self::$profiles = self::load(__DIR__ . '/../../resources/data/address-validation.json');
        }

        return self::$profiles;
    }

    /**
     * Case-insensitive full-string match (commerceguys semantics).
     */
    public static function matchesPattern(?string $pattern, string $value): bool
    {
        if ($pattern === null || $pattern === '') {
            return true;
        }

        $matched = @preg_match('/' . $pattern . '/i', $value, $matches);

        return $matched === 1 && ($matches[0] ?? null) === $value;
    }

    public static function flush(): void
    {
        self::$profiles = null;
    }

    /**
     * @return array<string, AddressValidationProfile>
     */
    private static function load(string $path): array
    {
        $decoded = json_decode(
            file_get_contents($path),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $profiles = [];

        foreach ($decoded as $code => $entry) {
            $pattern = $entry['pattern'] ?? null;

            if ($pattern !== null && @preg_match('/' . $pattern . '/i', '') === false) {
                throw new RuntimeException("Invalid postcode pattern for [{$code}].");
            }

            $profiles[mb_strtoupper((string) $code)] = new AddressValidationProfile(
                countryCode: mb_strtoupper((string) $code),
                pattern: $pattern,
                required: array_values($entry['required'] ?? []),
                upper: array_values($entry['upper'] ?? []),
            );
        }

        return $profiles;
    }
}
