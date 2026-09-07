<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Explicit host/provider mapping between an AddressArea node and a canonical City row.
 */
class AddressAreaCityLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'address_area_id',
        'city_id',
        'metadata',
    ];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('area_city_links');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function addressArea(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'address_area_id');
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::cityClass(), 'city_id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
