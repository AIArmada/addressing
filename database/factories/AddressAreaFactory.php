<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Factories;

use AIArmada\Addressing\Models\AddressArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AddressArea>
 */
class AddressAreaFactory extends Factory
{
    protected $model = AddressArea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->city();

        return [
            'country_code' => $this->faker->countryCode(),
            'type' => 'district',
            'level' => 2,
            'name' => $name,
            'slug' => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 999999)),
            'source' => 'factory',
            'source_id' => (string) Str::orderedUuid(),
            'is_active' => true,
        ];
    }
}
