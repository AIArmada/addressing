<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Factories;

use AIArmada\Addressing\Models\AddressCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AddressCountry>
 */
class AddressCountryFactory extends Factory
{
    protected $model = AddressCountry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'iso2' => $this->faker->unique()->countryCode(),
            'name' => $this->faker->country(),
            'iso3' => $this->faker->unique()->lexify('???'),
        ];
    }
}
