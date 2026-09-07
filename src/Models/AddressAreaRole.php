<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAreaRole extends Model
{
    use HasUuids;

    protected $fillable = ['address_area_id', 'role', 'source', 'country_code', 'is_primary'];

    public function getTable(): string
    {
        return config('addressing.database.tables.area_roles', 'address_area_roles');
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'address_area_id');
    }

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
}
