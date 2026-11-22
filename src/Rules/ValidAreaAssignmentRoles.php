<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Rules;

use AIArmada\Addressing\Support\AddressCountryResolver;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ValidAreaAssignmentRoles implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || $value === []) {
            return;
        }

        $address = $this->data['address'] ?? null;
        $country = is_array($address) ? ($address['country_id'] ?? null) : null;

        if (! is_string($country) || $country === '') {
            return;
        }

        if (app(AddressCountryResolver::class)->resolve($country) === null) {
            return;
        }

        $resolver = app(CountryAddressProfileResolver::class);

        foreach ($value as $role => $areaId) {
            if (! is_string($role) || $areaId === null || $areaId === '') {
                continue;
            }

            $definition = $resolver->definitionForRole($country, $role);

            if ($definition === null || $definition['level']->kind !== 'area') {
                $fail(__('The :role address area role is not defined for the selected country.', ['role' => $role]));
            }
        }
    }
}
