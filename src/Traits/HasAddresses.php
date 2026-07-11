<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Traits;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\Addressable;
use AIArmada\CommerceSupport\Support\OwnerContext;
use AIArmada\CommerceSupport\Support\OwnerWriteGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

trait HasAddresses
{
    /**
     * @return MorphToMany<Address, $this>
     */
    public function addresses(): MorphToMany
    {
        $table = $this->addressablesTable();

        return $this->morphToMany(
            Address::class,
            'addressable',
            $table,
        )
            ->using(Addressable::class)
            ->withPivot(['id', 'type', 'label', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps()
            ->orderBy($table . '.is_primary', 'desc')
            ->orderBy($table . '.created_at', 'desc');
    }

    public function primaryAddress(?string $type = null): ?Address
    {
        $table = $this->addressablesTable();

        $query = $this->validAddressQuery(
            $this->addresses()->where($table . '.is_primary', true),
        );

        if ($type !== null) {
            $query->where($table . '.type', $type);
        }

        /** @var Address|null */
        return $query->first();
    }

    /**
     * @return Collection<int, Address>
     */
    public function addressesOfType(string $type): Collection
    {
        $table = $this->addressablesTable();

        /** @var Collection<int, Address> */
        return $this->validAddressQuery(
            $this->addresses()->where($table . '.type', $type),
        )->get();
    }

    /**
     * Attach an address to this model.
     *
     * Attaching the same address multiple times with the same type creates
     * duplicate addressable pivot rows. To modify an existing attachment,
     * update the pivot directly.
     *
     * When $isPrimary is true, the pivot is created as non-primary first,
     * then promoted atomically via setPrimaryAddress(), which demotes any
     * existing primary of the same type before promoting this one.
     */
    public function attachAddress(
        Address $address,
        string $type = 'primary',
        bool $isPrimary = false,
        ?string $label = null,
    ): Addressable {
        if (config('addressing.owner.enabled', false)) {
            OwnerWriteGuard::findOrFailForOwner(
                Address::class,
                $address->getKey(),
                owner: OwnerContext::CURRENT,
                includeGlobal: (bool) config('addressing.owner.include_global', false),
            );
        }

        $pivot = Addressable::query()->create([
            'id' => (string) Str::orderedUuid(),
            'address_id' => $address->id,
            'addressable_type' => $this->getMorphClass(),
            'addressable_id' => $this->getKey(),
            'type' => $type,
            'is_primary' => false,
            'label' => $label,
        ]);

        $this->unsetRelation('addresses');

        if ($isPrimary) {
            return $this->setPrimaryAddress($address, $type);
        }

        return $pivot;
    }

    public function setPrimaryAddress(Address $address, string $type = 'primary'): Addressable
    {
        $table = $this->addressablesTable();

        $pivot = $this->addresses()
            ->where($table . '.address_id', $address->getKey())
            ->first()?->pivot;

        if (! $pivot) {
            throw new RuntimeException("Address [{$address->getKey()}] is not attached to this model.");
        }

        return DB::transaction(function () use ($pivot, $type): Addressable {
            $pivot::query()
                ->whereKey($pivot->getKey())
                ->lockForUpdate()
                ->first();

            $this->addresses()
                ->newPivotStatement()
                ->where('addressable_type', $this->getMorphClass())
                ->where('addressable_id', $this->getKey())
                ->where('type', $type)
                ->where('is_primary', true)
                ->where('id', '!=', $pivot->getKey())
                ->update(['is_primary' => false]);

            $pivot->is_primary = true;
            $pivot->type = $type;
            $pivot->save();

            return $pivot;
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeWithPrimaryAddress(Builder $query, ?string $type = null): void
    {
        $table = $this->addressablesTable();

        $query->with(['addresses' => function (Builder $q) use ($type, $table): void {
            $this->validAddressQuery(
                $q->where($table . '.is_primary', true),
            );

            if ($type !== null) {
                $q->where($table . '.type', $type);
            }
        }]);
    }

    /**
     * @param  Builder<Address>|MorphToMany<Address, $this>  $query
     * @return Builder<Address>|MorphToMany<Address, $this>
     */
    private function validAddressQuery(Builder | MorphToMany $query): Builder | MorphToMany
    {
        $table = $this->addressablesTable();
        $now = now();

        return $query
            ->where(function (Builder $q) use ($now, $table): void {
                $q->whereNull($table . '.valid_from')
                    ->orWhere($table . '.valid_from', '<=', $now);
            })
            ->where(function (Builder $q) use ($now, $table): void {
                $q->whereNull($table . '.valid_until')
                    ->orWhere($table . '.valid_until', '>=', $now);
            });
    }

    private function addressablesTable(): string
    {
        return config('addressing.tables.addressables', 'addressables');
    }
}
