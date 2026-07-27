<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAreaName extends Model
{
    use HasUuids;

    protected $fillable = [
        'address_area_id',
        'name',
        'source',
        'name_type',
        'is_preferred',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.area_names', 'address_area_names');
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'address_area_id');
    }

    protected function casts(): array
    {
        return ['is_preferred' => 'boolean'];
    }
}
