<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressingTableResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAreaAssignment extends Model
{
    use HasUuids;

    protected $fillable = ['address_id', 'address_area_id', 'role', 'is_primary', 'metadata'];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('address_area_assignments');
    }

    /** @return BelongsTo<Address, $this> */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'address_area_id');
    }

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'metadata' => 'array'];
    }
}
