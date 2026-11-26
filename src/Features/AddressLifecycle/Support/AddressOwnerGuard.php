<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Support;

use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerQuery;
use AIArmada\CommerceSupport\Support\OwnerScope;
use AIArmada\CommerceSupport\Support\OwnerScopeOverride;
use AIArmada\CommerceSupport\Support\OwnerWriteGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;

final class AddressOwnerGuard
{
    public static function applyToRelation(MorphToMany $relation): MorphToMany
    {
        $config = ModelResolver::addressableClass()::ownerScopeConfig();

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
            OwnerScopeOverride::suppressIncludeGlobal() ? false : $config->includeGlobal,
            $relation->getTable() . '.' . $config->ownerTypeColumn,
            $relation->getTable() . '.' . $config->ownerIdColumn,
        );

        return $relation;
    }

    public static function assertAddressIsWritable(mixed $addressId): void
    {
        self::findWritableAddress($addressId, ModelResolver::addressClass(), false);
    }

    /** @param class-string<Address>|null $modelClass */
    public static function lockAddressForWrite(string $addressId, ?string $modelClass = null): Address
    {
        return self::findWritableAddress($addressId, $modelClass ?? ModelResolver::addressClass(), true);
    }

    /** @param class-string<Address> $modelClass */
    private static function findWritableAddress(mixed $addressId, string $modelClass, bool $lock): Address
    {
        if (! is_int($addressId) && ! is_string($addressId)) {
            throw new AuthorizationException('A valid address is required.');
        }

        $config = $modelClass::ownerScopeConfig();
        $query = $modelClass::query()->withoutGlobalScope(OwnerScope::class)->whereKey($addressId);

        if ($lock) {
            $query->lockForUpdate();
        }

        if (! $config->enabled) {
            return $query->firstOrFail();
        }

        $owner = OwnerContext::resolveConfigured($config);
        OwnerContext::assertResolvedOrExplicitGlobal(
            $owner,
            sprintf('%s requires an owner context or explicit global context.', $modelClass),
        );

        OwnerQuery::applyToEloquentBuilder(
            $query,
            $owner,
            false,
            $config->ownerTypeColumn,
            $config->ownerIdColumn,
        );

        $address = $query->first();

        if (! $address instanceof Address) {
            throw new AuthorizationException('Referenced record is not accessible in the current owner scope.');
        }

        return $address;
    }

    public static function assertAddressableIsWritable(mixed $addressableType, mixed $addressableId): void
    {
        if (! ModelResolver::addressableClass()::ownerScopeConfig()->enabled) {
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
            throw new AuthorizationException(self::inaccessibleMessage());
        }

        if (self::hasOwnerScope($modelClass)) {
            OwnerWriteGuard::findOrFailForOwner($modelClass, $addressableId);

            return;
        }

        // Models without HasOwner enforce their own boundary through global
        // scopes (for example owner-via-parent scopes in consumer packages).
        // A normally scoped existence check therefore proves writability for
        // the resolved owner without addressing knowing the consumer's scope.
        // Explicit global context is privileged system work and may attach
        // to any existing model.
        $query = OwnerContext::isExplicitGlobal()
            ? $modelClass::query()->withoutGlobalScopes()
            : $modelClass::query();

        if (! $query->whereKey($addressableId)->exists()) {
            throw new AuthorizationException(self::inaccessibleMessage());
        }
    }

    public static function lockAddressableForWrite(string $addressableType, string | int $addressableId): void
    {
        $class = Relation::getMorphedModel($addressableType) ?? $addressableType;

        if (! is_a($class, Model::class, true)) {
            throw new InvalidArgumentException('The addressable type must identify an Eloquent model.');
        }

        $query = $class::query();

        if (OwnerContext::isExplicitGlobal() && ! method_exists($class, 'scopeForOwner')) {
            $query->withoutGlobalScopes();
        }

        if ($query->whereKey($addressableId)->lockForUpdate()->first() === null) {
            throw new AuthorizationException(self::inaccessibleMessage());
        }

        self::assertAddressableIsWritable($addressableType, $addressableId);
    }

    private static function inaccessibleMessage(): string
    {
        return 'The addressable model is not accessible to the current owner.';
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
