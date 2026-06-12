<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Traits;

use AIArmada\Addressing\Models\Address;
use AIArmada\Addressing\Models\Addressable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait HasAddresses
{
    /**
     * @return MorphToMany<Address, $this>
     */
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(
            Address::class,
            'addressable',
            config('addressing.tables.addressables', 'addressables'),
        )
            ->using(Addressable::class)
            ->withPivot(['id', 'type', 'label', 'is_primary', 'valid_from', 'valid_until'])
            ->withTimestamps()
            ->orderBy('addressables.is_primary', 'desc')
            ->orderBy('addressables.created_at', 'desc');
    }

    public function primaryAddress(?string $type = null): ?Address
    {
        $query = $this->addresses()->where('addressables.is_primary', true);

        if ($type !== null) {
            $query->where('addressables.type', $type);
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
        return $this->addresses()->where('addressables.type', $type)->get();
    }

    public function attachAddress(
        Address $address,
        string $type = 'primary',
        bool $isPrimary = false,
        ?string $label = null,
    ): Addressable {
        $this->addresses()->attach($address->id, [
            'id' => (string) Str::orderedUuid(),
            'type' => $type,
            'is_primary' => $isPrimary,
            'label' => $label,
        ]);

        /** @var Addressable $pivot */
        $pivot = $this->addresses()->find($address->id)?->pivot;

        return $pivot;
    }

    public function setPrimaryAddress(Address $address, string $type = 'primary'): Addressable
    {
        $this->addresses()
            ->newPivotStatement()
            ->where('addressable_type', $this->getMorphClass())
            ->where('addressable_id', $this->getKey())
            ->where('type', $type)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        /** @var Addressable $pivot */
        $pivot = $this->addresses()->find($address->id)?->pivot;
        $pivot->is_primary = true;
        $pivot->type = $type;
        $pivot->save();

        return $pivot;
    }
}
