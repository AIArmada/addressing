<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Factories;

use AIArmada\Addressing\Models\ResolutionGap;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResolutionGap>
 */
class ResolutionGapFactory extends Factory
{
    protected $model = ResolutionGap::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = $this->faker->unique()->city();

        return [
            'source' => 'factory',
            'country_code' => $this->faker->countryCode(),
            'role' => 'area',
            'value' => $value,
            'normalized' => mb_strtolower($value),
            'reason' => 'unmatched',
            'status' => 'open',
        ];
    }
}
