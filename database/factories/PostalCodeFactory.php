<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Factories;

use AIArmada\Addressing\Models\PostalCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostalCode>
 */
class PostalCodeFactory extends Factory
{
    protected $model = PostalCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_code' => $this->faker->countryCode(),
            'code' => (string) $this->faker->unique()->numberBetween(10000, 99999),
            'is_active' => true,
        ];
    }
}
