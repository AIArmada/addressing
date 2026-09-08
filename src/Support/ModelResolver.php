<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\AddressSnapshot;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Resolves host-application geography model subclasses configured for the package.
 */
final class ModelResolver
{
    /**
     * @return class-string<AddressCountry>
     */
    public static function countryClass(): string
    {
        /** @var class-string<AddressCountry> $modelClass */
        $modelClass = self::resolve('country', AddressCountry::class);

        return $modelClass;
    }

    /**
     * @return class-string<State>
     */
    public static function stateClass(): string
    {
        /** @var class-string<State> $modelClass */
        $modelClass = self::resolve('state', State::class);

        return $modelClass;
    }

    /**
     * @return class-string<City>
     */
    public static function cityClass(): string
    {
        /** @var class-string<City> $modelClass */
        $modelClass = self::resolve('city', City::class);

        return $modelClass;
    }

    /**
     * @return class-string<AddressArea>
     */
    public static function areaClass(): string
    {
        /** @var class-string<AddressArea> $modelClass */
        $modelClass = self::resolve('area', AddressArea::class);

        return $modelClass;
    }

    /**
     * @return class-string<Address>
     */
    public static function addressClass(): string
    {
        /** @var class-string<Address> $modelClass */
        $modelClass = self::resolve('address', Address::class);

        return $modelClass;
    }

    /**
     * @return class-string<AddressSnapshot>
     */
    public static function snapshotClass(): string
    {
        /** @var class-string<AddressSnapshot> $modelClass */
        $modelClass = self::resolve('snapshot', AddressSnapshot::class);

        return $modelClass;
    }

    /**
     * @param  class-string<Model>  $default
     * @return class-string<Model>
     */
    private static function resolve(string $key, string $default): string
    {
        $modelClass = config("addressing.models.{$key}", $default);

        if (! is_string($modelClass) || ! is_a($modelClass, $default, true)) {
            throw new LogicException(sprintf(
                'Addressing model [%s] must extend [%s] at [addressing.models.%s].',
                is_string($modelClass) ? $modelClass : get_debug_type($modelClass),
                $default,
                $key,
            ));
        }

        return $modelClass;
    }
}
