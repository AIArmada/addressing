<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Traits;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\Addressable;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerScopeConfig;
use AIArmada\CommerceSupport\Support\OwnerScopeOverride;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait HasAddresses
{
    /**
     * @return MorphToMany<Address, $this>
     */
    public function addresses(): MorphToMany
    {
        $pivotTable = AddressingTableResolver::resolve('addressables');

        $relation = $this->morphToMany(
            ModelResolver::addressClass(),
            'addressable',
            AddressingTableResolver::resolve('addressables'),
        )
            ->using(Addressable::class)
            ->withPivot(['id', 'type', 'label', 'is_primary', 'valid_from', 'valid_until', 'owner_type', 'owner_id'])
            ->withTimestamps()
            ->orderBy("{$pivotTable}.is_primary", 'desc')
            ->orderBy("{$pivotTable}.created_at", 'desc');

        return AddressOwnerGuard::applyToRelation($relation);
    }

    public function primaryAddress(?string $type = null): ?Address
    {
        if ($this->relationLoaded('addresses')) {
            $cached = $this->primaryFromLoadedAddresses($type);

            if ($cached instanceof Address) {
                return $cached;
            }
        }

        $pivotTable = AddressingTableResolver::resolve('addressables');
        $query = $this->validAddressQuery(
            $this->addresses()->where("{$pivotTable}.is_primary", true),
        );

        if ($type !== null) {
            $query->where("{$pivotTable}.type", $type);
        }

        /** @var Address|null */
        return $query->first();
    }

    private function primaryFromLoadedAddresses(?string $type): ?Address
    {
        /** @var Collection<int, Address> $addresses */
        $addresses = $this->authorizeLoadedAddresses($this->getRelation('addresses'));
        $now = CarbonImmutable::now();

        return $addresses->first(function (Address $address) use ($type, $now): bool {
            $pivot = $address->pivot;

            return (bool) $pivot?->is_primary
                && ($type === null || $pivot?->type === $type)
                && ($pivot?->valid_from === null || $pivot->valid_from <= $now)
                && ($pivot?->valid_until === null || $pivot->valid_until >= $now);
        });
    }

    /**
     * Reapply the current owner boundary to an already-loaded collection.
     *
     * Loaded rows may predate an OwnerContext switch, so they are filtered
     * against the current context instead of trusted. Rows without owner
     * state fail closed and fall back to a fresh query.
     *
     * Both the address and its pivot must be visible: absent owner columns
     * (for example a constrained `select('addresses.id', ...)` preload)
     * abandon the cache entirely so the caller runs a fresh scoped query
     * instead of trusting a null-looking tuple.
     *
     * @param  Collection<int, Address>  $addresses
     * @return Collection<int, Address>
     */
    private function authorizeLoadedAddresses(Collection $addresses): Collection
    {
        $addressClass = ModelResolver::addressClass();
        $config = $addressClass::ownerScopeConfig();

        if (! $config->enabled) {
            return $addresses;
        }

        $owner = OwnerContext::resolve();

        OwnerContext::assertResolvedOrExplicitGlobal(
            $owner,
            sprintf('%s requires an owner context or explicit global context.', $addressClass),
        );

        $includeGlobal = OwnerScopeOverride::suppressIncludeGlobal() ? false : $config->includeGlobal;
        $pivotConfig = Addressable::ownerScopeConfig();
        $pivotIncludeGlobal = OwnerScopeOverride::suppressIncludeGlobal() ? false : $pivotConfig->includeGlobal;

        foreach ($addresses as $address) {
            if ($this->trustedOwnerTuple($address, $config) === null) {
                return new Collection;
            }

            if (! $pivotConfig->enabled) {
                continue;
            }

            $pivot = $address->pivot;

            if (! $pivot instanceof Model || $this->trustedOwnerTuple($pivot, $pivotConfig) === null) {
                return new Collection;
            }
        }

        return $addresses->filter(fn (Address $address): bool => $this->loadedRowIsVisible(
            $address,
            $owner,
            $includeGlobal,
            $config,
            $pivotIncludeGlobal,
            $pivotConfig,
        ));
    }

    private function loadedRowIsVisible(
        Address $address,
        ?Model $owner,
        bool $includeGlobal,
        OwnerScopeConfig $config,
        bool $pivotIncludeGlobal,
        OwnerScopeConfig $pivotConfig,
    ): bool {
        $addressTuple = $this->trustedOwnerTuple($address, $config);

        if ($addressTuple === null || ! $this->tupleIsVisible($addressTuple, $owner, $includeGlobal)) {
            return false;
        }

        if (! $pivotConfig->enabled) {
            return true;
        }

        $pivot = $address->pivot;

        if (! $pivot instanceof Model) {
            return false;
        }

        $pivotTuple = $this->trustedOwnerTuple($pivot, $pivotConfig);

        return $pivotTuple !== null && $this->tupleIsVisible($pivotTuple, $owner, $pivotIncludeGlobal);
    }

    /**
     * Prefer persisted owner state over caller-mutated attributes.
     *
     * Returns null when neither the original nor the current attributes
     * contain both configured owner columns (for example a partial select),
     * forcing the caller to fail closed.
     *
     * @return array{0: mixed, 1: mixed}|null
     */
    private function trustedOwnerTuple(Model $model, OwnerScopeConfig $config): ?array
    {
        $typeColumn = $config->ownerTypeColumn;
        $idColumn = $config->ownerIdColumn;
        $original = $model->getOriginal();

        if (is_array($original)
            && array_key_exists($typeColumn, $original)
            && array_key_exists($idColumn, $original)) {
            return [$original[$typeColumn], $original[$idColumn]];
        }

        $attributes = $model->getAttributes();

        if (array_key_exists($typeColumn, $attributes) && array_key_exists($idColumn, $attributes)) {
            return [$attributes[$typeColumn], $attributes[$idColumn]];
        }

        return null;
    }

    /**
     * @param  array{0: mixed, 1: mixed}  $tuple
     */
    private function tupleIsVisible(array $tuple, ?Model $owner, bool $includeGlobal): bool
    {
        [$type, $id] = $tuple;
        $isGlobal = $type === null && $id === null;

        if ($owner === null) {
            return $isGlobal;
        }

        if ($isGlobal) {
            return $includeGlobal;
        }

        return $type === $owner->getMorphClass() && (string) $id === (string) $owner->getKey();
    }

    /**
     * @return Collection<int, Address>
     */
    public function addressesOfType(string $type): Collection
    {
        /** @var Collection<int, Address> */
        return $this->validAddressQuery(
            $this->addresses()->where(
                AddressingTableResolver::resolve('addressables') . '.type',
                $type,
            ),
        )->get();
    }

    public function attachAddress(
        Address $address,
        string $type = 'primary',
        bool $isPrimary = false,
        ?string $label = null,
    ): Addressable {
        return DB::transaction(function () use ($address, $type, $isPrimary, $label): Addressable {
            AddressOwnerGuard::assertAddressIsWritable($address->getKey());
            AddressOwnerGuard::assertAddressableIsWritable($this->getMorphClass(), $this->getKey());
            $this->lockForAddressMutation();

            $existing = Addressable::query()
                ->where('address_id', $address->getKey())
                ->where('addressable_type', $this->getMorphClass())
                ->where('addressable_id', $this->getKey())
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            if ($existing instanceof Addressable) {
                $updates = [];

                if ($isPrimary) {
                    $this->demotePrimaryAddressPivots($type);
                    $updates['is_primary'] = true;
                }

                if ($label !== null && $existing->label !== $label) {
                    $updates['label'] = $label;
                }

                if ($updates !== []) {
                    $existing->update($updates);
                }

                $this->unsetRelation('addresses');

                return $existing->fresh() ?? $existing;
            }

            if ($isPrimary) {
                $this->demotePrimaryAddressPivots($type);
            }

            $pivot = Addressable::query()->create([
                'id' => (string) Str::orderedUuid(),
                'address_id' => $address->id,
                'addressable_type' => $this->getMorphClass(),
                'addressable_id' => $this->getKey(),
                'type' => $type,
                'is_primary' => $isPrimary,
                'label' => $label,
            ]);

            $this->unsetRelation('addresses');

            return $pivot->fresh() ?? $pivot;
        });
    }

    public function setPrimaryAddress(Address $address, string $type = 'primary'): Addressable
    {
        return DB::transaction(function () use ($address, $type): Addressable {
            AddressOwnerGuard::assertAddressIsWritable($address->getKey());
            AddressOwnerGuard::assertAddressableIsWritable($this->getMorphClass(), $this->getKey());
            $this->lockForAddressMutation();

            $pivotTable = AddressingTableResolver::resolve('addressables');

            /** @var Addressable|null $pivot */
            $pivot = $this->addresses()
                ->whereKey($address->id)
                ->where("{$pivotTable}.type", $type)
                ->first()
                ?->pivot;

            if (! $pivot instanceof Addressable) {
                throw new InvalidArgumentException('The address must be attached with the requested type before it can be primary.');
            }

            $this->demotePrimaryAddressPivots($type);
            Addressable::query()
                ->whereKey($pivot->getKey())
                ->update(['is_primary' => true]);
            $pivot->is_primary = true;

            $this->unsetRelation('addresses');

            return $pivot;
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeWithPrimaryAddress(Builder $query, ?string $type = null): void
    {
        $query->with(['addresses' => function (MorphToMany $q) use ($type): void {
            $pivotTable = AddressingTableResolver::resolve('addressables');
            $this->validAddressQuery(
                $q->where("{$pivotTable}.is_primary", true),
            );

            if ($type !== null) {
                $q->where("{$pivotTable}.type", $type);
            }
        }]);
    }

    /**
     * @param  Builder<Address>|MorphToMany<Address, $this>  $query
     * @return Builder<Address>|MorphToMany<Address, $this>
     */
    private function validAddressQuery(Builder | MorphToMany $query): Builder | MorphToMany
    {
        $now = CarbonImmutable::now();

        return $query
            ->where(function (Builder $q) use ($now): void {
                $pivotTable = AddressingTableResolver::resolve('addressables');
                $q->whereNull("{$pivotTable}.valid_from")
                    ->orWhere("{$pivotTable}.valid_from", '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $pivotTable = AddressingTableResolver::resolve('addressables');
                $q->whereNull("{$pivotTable}.valid_until")
                    ->orWhere("{$pivotTable}.valid_until", '>=', $now);
            });
    }

    private function demotePrimaryAddressPivots(string $type): void
    {
        Addressable::query()
            ->where('addressable_type', $this->getMorphClass())
            ->where('addressable_id', $this->getKey())
            ->where('type', $type)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }

    private function lockForAddressMutation(): void
    {
        $this->newQuery()
            ->whereKey($this->getKey())
            ->lockForUpdate()
            ->first();
    }
}
