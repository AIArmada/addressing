<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class AddressAreaPostalCode extends Pivot
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'address_area_id',
        'postal_code_id',
        'relationship_type',
        'is_primary',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.area_postal_codes', 'address_area_postal_codes');
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'address_area_id');
    }

    /** @return BelongsTo<PostalCode, $this> */
    public function postalCode(): BelongsTo
    {
        return $this->belongsTo(PostalCode::class, 'postal_code_id');
    }

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
}
