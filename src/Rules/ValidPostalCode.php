<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Rules;

use AIArmada\Addressing\Support\AddressValidationProfiles;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidPostalCode implements ValidationRule
{
    public function __construct(
        private readonly string $countryCode,
    ) {}

    /**
     * Format check only; presence is the `required` rule's job, so blank
     * strings pass. Countries without a profile are unconstrained.
     * Non-string scalars other than int/float fail.
     */
    public static function isValid(string $countryCode, mixed $value): bool
    {
        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value)) {
            return false;
        }

        $code = mb_trim($value);

        if ($code === '') {
            return true;
        }

        $profile = AddressValidationProfiles::forCountry($countryCode);

        if ($profile === null) {
            return true;
        }

        return AddressValidationProfiles::matchesPattern($profile->pattern, $code);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid($this->countryCode, $value)) {
            $code = mb_strtoupper(mb_trim($this->countryCode));
            $fail("The :attribute is not a valid {$code} postcode.");
        }
    }
}
