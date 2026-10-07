<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressData;
use AIArmada\Addressing\Support\AddressCountryResolver;
use AIArmada\Addressing\Support\AddressValidationProfiles;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\Addressing\Support\SchemaTableCache;
use InvalidArgumentException;

final class ValidateAddressAction
{
    private const array FIELD_LABELS = [
        'line1' => 'address line 1',
        'city' => 'city',
        'state' => 'state',
        'postcode' => 'postcode',
    ];

    public function __construct(
        private readonly AddressCountryResolver $countryResolver,
    ) {}

    /**
     * Validate an address against its country's validation profile.
     *
     * Callers set countryCode first (NormalizeAddressDataAction keeps it
     * canonical); this action only reads AddressData as given. Known
     * countries without a profile are unconstrained and pass; unknown
     * codes fail only when countries are seeded (otherwise there is
     * nothing to distinguish a typo from an unseeded table). Country names
     * resolve through seeded rows and profile off the resolved iso2. Supplied
     * postcodes are pattern-checked even where not required.
     *
     * @return array<string, string> Field name to violation message; empty when valid.
     */
    public function execute(AddressData $address): array
    {
        $code = $address->countryCode !== null ? mb_strtoupper(mb_trim($address->countryCode)) : '';

        if ($code === '') {
            return [];
        }

        $country = $this->countryResolver->resolve($code);

        if ($country === null && $this->countriesSeeded()) {
            return ['countryCode' => "Unknown country code [{$code}]."];
        }

        $profileCode = mb_strtoupper((string) ($country?->getAttribute('iso2') ?? $code));
        $profile = AddressValidationProfiles::forCountry($profileCode);

        if ($profile === null) {
            return [];
        }

        $violations = [];

        foreach ($profile->required as $field) {
            if (! self::present($address, $field, $profileCode)) {
                $violations[$field] = 'The ' . self::FIELD_LABELS[$field] . " is required for {$profileCode} addresses.";
            }
        }

        $postcode = $address->postcode !== null ? mb_trim($address->postcode) : '';

        if ($postcode !== '' && $profile->pattern !== null && ! AddressValidationProfiles::matchesPattern($profile->pattern, $postcode)) {
            $violations['postcode'] = "The postcode [{$postcode}] is not a valid {$profileCode} postcode.";
        }

        return $violations;
    }

    private static function present(AddressData $address, string $field, string $code): bool
    {
        return match ($field) {
            'line1' => self::filled($address->line1),
            'city' => self::filled($address->city) || self::filled($address->cityId),
            'state' => self::filled($address->state) || self::filled($address->stateId),
            'postcode' => self::filled($address->postcode),
            default => throw new InvalidArgumentException("Unknown required field [{$field}] for [{$code}]."),
        };
    }

    private function countriesSeeded(): bool
    {
        $countryClass = ModelResolver::countryClass();
        $model = new $countryClass;

        return SchemaTableCache::exists($model->getTable()) && $countryClass::query()->exists();
    }

    private static function filled(?string $value): bool
    {
        return $value !== null && mb_trim($value) !== '';
    }
}
