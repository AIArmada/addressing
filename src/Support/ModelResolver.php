<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;

/**
 * Resolves host-application geography model subclasses configured for the package.
 */
final class ModelResolver
{
    /**
     * @return class-string<State>
     */
    public static function stateClass(): string
    {
        /** @var class-string<State> $modelClass */
        $modelClass = config('addressing.models.state', State::class);

        return $modelClass;
    }

    /**
     * @return class-string<City>
     */
    public static function cityClass(): string
    {
        /** @var class-string<City> $modelClass */
        $modelClass = config('addressing.models.city', City::class);

        return $modelClass;
    }
}
