<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Models\Addressable;
use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerQuery;
use AIArmada\CommerceSupport\Support\OwnerScope;
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
            sprintf('%s requires an owner context or explicit global context.', ModelResolver::addressClass()),
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
        $addressClass = ModelResolver::addressClass();

        if (! $addressClass::ownerScopeConfig()->enabled) {
            $addressClass::query()->whereKey($addressId)->firstOrFail();

            return;
        }

        if (! is_int($addressId) && ! is_string($addressId)) {
            throw new AuthorizationException('A valid address is required.');
        }

        OwnerWriteGuard::findOrFailForOwner($addressClass, $addressId);
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

        if (method_exists($modelClass, 'eventOwnerRelation')) {
            $addressable = $modelClass::query()
                ->withoutGlobalScope('event_owner')
                ->whereKey($addressableId)
                ->first();

            if (! $addressable instanceof Model) {
                throw new AuthorizationException('The addressable model is not accessible to the current owner.');
            }

            if (! method_exists($addressable, 'event')) {
                throw new AuthorizationException('The addressable model does not expose an event owner relation.');
            }

            $event = $addressable->event()
                ->getQuery()
                ->withoutGlobalScope(OwnerScope::class)
                ->first();

            if (! $event instanceof Model) {
                throw new AuthorizationException('The addressable model is not accessible to the current owner.');
            }

            $owner = OwnerContext::resolve();

            if ($owner === null) {
                if (! OwnerContext::isExplicitGlobal()) {
                    throw new AuthorizationException(sprintf('Cross-owner write blocked for %s.', $modelClass));
                }

                return;
            }

            if ($event->getAttribute('owner_type') !== $owner->getMorphClass()
                || (string) $event->getAttribute('owner_id') !== (string) $owner->getKey()) {
                throw new AuthorizationException(sprintf('Cross-owner write blocked for %s.', $modelClass));
            }

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
