<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Factories;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => AddressCountry::factory(),
            'name' => $this->faker->state(),
            'country_code' => $this->faker->countryCode(),
            'code' => $this->faker->unique()->lexify('??'),
        ];
    }
}
