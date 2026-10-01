<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Owner scope for area assignments through their parent address.
 *
 * Assignment rows carry no owner tuple of their own; visibility follows the
 * owner-scoped parent Address row. Reads require a resolved owner or explicit
 * global context, mirroring the Address boundary.
 *
 * Visibility is resolved through the configured `address` relation so custom
 * address tables, keys, and additional visibility scopes apply. The booted
 * owner snapshot is discarded in favour of the current config, which also
 * keeps runtime `enabled`/`include_global` flips effective.
 */
final class AddressAreaAssignmentOwnerScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $addressClass = ModelResolver::addressClass();
        $config = $addressClass::ownerScopeConfig();

        if (! $config->enabled) {
            $builder->whereHas('address', function (Builder $addressQuery): void {
                $addressQuery->withoutGlobalScope(OwnerScope::class);
            });

            return;
        }

        $owner = $config->owner ?? OwnerContext::resolve();

        OwnerContext::assertResolvedOrExplicitGlobal(
            $owner,
            sprintf('%s requires an owner context or explicit global context.', $model::class),
        );

        $builder->whereHas('address', function (Builder $addressQuery) use ($config): void {
            $addressQuery->withoutGlobalScope(OwnerScope::class);

            (new OwnerScope($config))->apply($addressQuery, $addressQuery->getModel());
        });
    }
}
