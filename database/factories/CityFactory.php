<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Factories;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => AddressCountry::factory(),
            'name' => $this->faker->unique()->city(),
            'country_code' => $this->faker->countryCode(),
        ];
    }
}
