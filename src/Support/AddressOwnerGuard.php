<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerQuery;
use AIArmada\CommerceSupport\Support\OwnerScope;
use AIArmada\CommerceSupport\Support\OwnerScopeOverride;
use AIArmada\CommerceSupport\Support\OwnerWriteGuard;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        $addressClass = ModelResolver::addressClass();

        if (! $addressClass::ownerScopeConfig()->enabled) {
            $addressClass::query()->withoutGlobalScope(OwnerScope::class)->whereKey($addressId)->firstOrFail();

            return;
        }

        if (! is_int($addressId) && ! is_string($addressId)) {
            throw new AuthorizationException('A valid address is required.');
        }

        OwnerWriteGuard::findOrFailForOwner($addressClass, $addressId);
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
