<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Traits;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\Addressable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        $pivotTable = config('addressing.tables.addressables', 'addressables');

        return $this->morphToMany(
            Address::class,
            'addressable',
            config('addressing.tables.addressables', 'addressables'),
        )
            ->using(Addressable::class)
            ->withPivot(['id', 'type', 'label', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps()
            ->orderBy("{$pivotTable}.is_primary", 'desc')
            ->orderBy("{$pivotTable}.created_at", 'desc');
    }

    public function primaryAddress(?string $type = null): ?Address
    {
        if ($this->relationLoaded('addresses')) {
            /** @var Collection<int, Address> $addresses */
            $addresses = $this->getRelation('addresses');
            $now = now();

            return $addresses->first(function (Address $address) use ($type, $now): bool {
                $pivot = $address->pivot;

                return (bool) $pivot?->is_primary
                    && ($type === null || $pivot?->type === $type)
                    && ($pivot?->valid_from === null || $pivot->valid_from <= $now)
                    && ($pivot?->valid_until === null || $pivot->valid_until >= $now);
            });
        }

        $pivotTable = config('addressing.tables.addressables', 'addressables');
        $query = $this->validAddressQuery(
            $this->addresses()->where("{$pivotTable}.is_primary", true),
        );

        if ($type !== null) {
            $query->where("{$pivotTable}.type", $type);
        }

        /** @var Address|null */
        return $query->first();
    }

    /**
     * @return Collection<int, Address>
     */
    public function addressesOfType(string $type): Collection
    {
        /** @var Collection<int, Address> */
        return $this->validAddressQuery(
            $this->addresses()->where(
                config('addressing.tables.addressables', 'addressables') . '.type',
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
            $this->lockForAddressMutation();

            $existing = Addressable::query()
                ->where('address_id', $address->getKey())
                ->where('addressable_type', $this->getMorphClass())
                ->where('addressable_id', $this->getKey())
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            if ($existing instanceof Addressable) {
                if ($isPrimary) {
                    $this->demotePrimaryAddressPivots($type);
                    $existing->update(['is_primary' => true]);
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
            $this->lockForAddressMutation();

            $pivotTable = config('addressing.tables.addressables', 'addressables');

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
        $query->with(['addresses' => function (Builder $q) use ($type): void {
            $pivotTable = config('addressing.tables.addressables', 'addressables');
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
        $now = now();

        return $query
            ->where(function (Builder $q) use ($now): void {
                $pivotTable = config('addressing.tables.addressables', 'addressables');
                $q->whereNull("{$pivotTable}.valid_from")
                    ->orWhere("{$pivotTable}.valid_from", '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $pivotTable = config('addressing.tables.addressables', 'addressables');
                $q->whereNull("{$pivotTable}.valid_until")
                    ->orWhere("{$pivotTable}.valid_until", '>=', $now);
            });
    }

    private function demotePrimaryAddressPivots(string $type): void
    {
        $this->addresses()
            ->newPivotStatement()
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
