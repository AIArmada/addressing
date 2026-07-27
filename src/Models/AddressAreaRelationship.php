<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressAreaRelationship extends Model
{
    use HasUuids;

    protected $fillable = [
        'parent_address_area_id',
        'child_address_area_id',
        'relationship_type',
        'hierarchy_type',
        'valid_from',
        'valid_until',
        'metadata',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.area_relationships', 'address_area_relationships');
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'parent_address_area_id');
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function child(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'child_address_area_id');
    }

    protected function casts(): array
    {
        return [
            'valid_from' => 'immutable_date',
            'valid_until' => 'immutable_date',
            'metadata' => 'array',
        ];
    }
}
