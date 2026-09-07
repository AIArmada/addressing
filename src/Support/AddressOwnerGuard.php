<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\Addressable;
use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerQuery;
use AIArmada\CommerceSupport\Support\OwnerWriteGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;

final class AddressOwnerGuard
{
    public static function applyToRelation(MorphToMany $relation): MorphToMany
    {
        $config = Addressable::ownerScopeConfig();

        if (! $config->enabled) {
            return $relation;
        }

        $owner = OwnerContext::resolve();

        OwnerContext::assertResolvedOrExplicitGlobal(
            $owner,
            sprintf('%s requires an owner context or explicit global context.', Address::class),
        );

        OwnerQuery::applyToQueryBuilder(
            $relation->getQuery()->getQuery(),
            $owner,
            $config->includeGlobal,
            $relation->getTable() . '.' . $config->ownerTypeColumn,
            $relation->getTable() . '.' . $config->ownerIdColumn,
        );

        return $relation;
    }

    public static function assertAddressIsWritable(mixed $addressId): void
    {
        if (! Address::ownerScopeConfig()->enabled) {
            Address::query()->whereKey($addressId)->firstOrFail();

            return;
        }

        if (! is_int($addressId) && ! is_string($addressId)) {
            throw new AuthorizationException('A valid address is required.');
        }

        OwnerWriteGuard::findOrFailForOwner(Address::class, $addressId);
    }

    public static function assertAddressableIsWritable(mixed $addressableType, mixed $addressableId): void
    {
        if (! Addressable::ownerScopeConfig()->enabled) {
            return;
        }

        OwnerContext::assertResolvedOrExplicitGlobal(
            OwnerContext::resolve(),
            'An owner context or explicit global context is required to write an addressable record.',
        );

        if (! is_string($addressableType)
            || $addressableType === ''
            || (! is_int($addressableId) && ! is_string($addressableId))) {
            throw new AuthorizationException('A valid addressable model is required.');
        }

        $modelClass = Relation::getMorphedModel($addressableType) ?? $addressableType;

        if (! class_exists($modelClass) || ! is_a($modelClass, Model::class, true)) {
            throw new AuthorizationException('The addressable model could not be resolved.');
        }

        if (self::hasOwnerScope($modelClass)) {
            OwnerWriteGuard::findOrFailForOwner($modelClass, $addressableId);

            return;
        }

        $eventBoundaryClass = 'AIArmada\\Events\\Support\\EventTenantBoundary';
        $eventOwnerScopeClass = 'AIArmada\\Events\\Support\\EventOwnerScope';

        if (class_exists($eventBoundaryClass)
            && class_exists($eventOwnerScopeClass)
            && is_callable([$eventOwnerScopeClass, 'supports'])
            && $eventOwnerScopeClass::supports($modelClass)) {
            $addressable = $modelClass::query()->whereKey($addressableId)->first();

            if (! $addressable instanceof Model) {
                throw new AuthorizationException('The addressable model is not accessible to the current owner.');
            }

            $eventBoundaryClass::assertWritable($addressable);

            return;
        }

        if (! $modelClass::query()->whereKey($addressableId)->exists()) {
            throw new AuthorizationException('The addressable model could not be found.');
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function hasOwnerScope(string $modelClass): bool
    {
        if (method_exists($modelClass, 'ownerScopeConfig')) {
            return $modelClass::ownerScopeConfig()->enabled;
        }

        return method_exists($modelClass, 'scopeForOwner');
    }
}
