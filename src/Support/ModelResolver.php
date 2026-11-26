<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Features\AddressLifecycle\Models\Addressable;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressAreaAssignment;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaName;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaRelationship;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaRole;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaStateLink;
use AIArmada\Addressing\Features\Geography\Models\AddressCountry;
use AIArmada\Addressing\Features\Geography\Models\City;
use AIArmada\Addressing\Features\Geography\Models\State;
use AIArmada\Addressing\Features\PostalCodes\Models\AddressAreaPostalCode;
use AIArmada\Addressing\Features\PostalCodes\Models\PostalCode;
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
        /** @var class-string<AddressCountry> $default */
        $default = app('addressing.model-default.country');
        /** @var class-string<AddressCountry> $modelClass */
        $modelClass = self::resolve('country', $default);

        return $modelClass;
    }

    /**
     * @return class-string<State>
     */
    public static function stateClass(): string
    {
        /** @var class-string<State> $default */
        $default = app('addressing.model-default.state');
        /** @var class-string<State> $modelClass */
        $modelClass = self::resolve('state', $default);

        return $modelClass;
    }

    /**
     * @return class-string<City>
     */
    public static function cityClass(): string
    {
        /** @var class-string<City> $default */
        $default = app('addressing.model-default.city');
        /** @var class-string<City> $modelClass */
        $modelClass = self::resolve('city', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressArea>
     */
    public static function areaClass(): string
    {
        /** @var class-string<AddressArea> $default */
        $default = app('addressing.model-default.area');
        /** @var class-string<AddressArea> $modelClass */
        $modelClass = self::resolve('area', $default);

        return $modelClass;
    }

    /**
     * @return class-string<Address>
     */
    public static function addressClass(): string
    {
        /** @var class-string<Address> $default */
        $default = app('addressing.model-default.address');
        /** @var class-string<Address> $modelClass */
        $modelClass = self::resolve('address', $default);

        return $modelClass;
    }

    /**
     * @return class-string<Addressable>
     */
    public static function addressableClass(): string
    {
        /** @var class-string<Addressable> $default */
        $default = app('addressing.model-default.addressable');
        /** @var class-string<Addressable> $modelClass */
        $modelClass = self::resolve('addressable', $default);

        return $modelClass;
    }

    /**
     * @return class-string<PostalCode>
     */
    public static function postalCodeClass(): string
    {
        /** @var class-string<PostalCode> $default */
        $default = app('addressing.model-default.postal_code');
        /** @var class-string<PostalCode> $modelClass */
        $modelClass = self::resolve('postal_code', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressAreaAssignment>
     */
    public static function areaAssignmentClass(): string
    {
        /** @var class-string<AddressAreaAssignment> $default */
        $default = app('addressing.model-default.area_assignment');
        /** @var class-string<AddressAreaAssignment> $modelClass */
        $modelClass = self::resolve('area_assignment', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressAreaName>
     */
    public static function areaNameClass(): string
    {
        /** @var class-string<AddressAreaName> $default */
        $default = app('addressing.model-default.area_name');
        /** @var class-string<AddressAreaName> $modelClass */
        $modelClass = self::resolve('area_name', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressAreaRole>
     */
    public static function areaRoleClass(): string
    {
        /** @var class-string<AddressAreaRole> $default */
        $default = app('addressing.model-default.area_role');
        /** @var class-string<AddressAreaRole> $modelClass */
        $modelClass = self::resolve('area_role', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressAreaStateLink>
     */
    public static function areaStateLinkClass(): string
    {
        /** @var class-string<AddressAreaStateLink> $default */
        $default = app('addressing.model-default.area_state_link');
        /** @var class-string<AddressAreaStateLink> $modelClass */
        $modelClass = self::resolve('area_state_link', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressAreaPostalCode>
     */
    public static function areaPostalCodeClass(): string
    {
        /** @var class-string<AddressAreaPostalCode> $default */
        $default = app('addressing.model-default.area_postal_code');
        /** @var class-string<AddressAreaPostalCode> $modelClass */
        $modelClass = self::resolve('area_postal_code', $default);

        return $modelClass;
    }

    /**
     * @return class-string<AddressAreaRelationship>
     */
    public static function areaRelationshipClass(): string
    {
        /** @var class-string<AddressAreaRelationship> $default */
        $default = app('addressing.model-default.area_relationship');
        /** @var class-string<AddressAreaRelationship> $modelClass */
        $modelClass = self::resolve('area_relationship', $default);

        return $modelClass;
    }

    /**
     * @param  class-string<Model>  $default
     * @return class-string<Model>
     */
    private static function resolve(string $key, string $default): string
    {
        $modelClass = config("addressing.models.{$key}") ?? $default;

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
