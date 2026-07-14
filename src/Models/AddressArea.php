<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|null $country_id
 * @property string|null $parent_id
 * @property string $country_code
 * @property string $type
 * @property int|null $level
 * @property string $name
 * @property string|null $native_name
 * @property string|null $code
 * @property string $slug
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string $source
 * @property string $source_id
 * @property string|null $parent_source_id
 * @property array|null $source_payload
 * @property CarbonImmutable|null $synced_at
 * @property array|null $metadata
 * @property-read AddressCountry|null $country
 * @property-read AddressArea|null $parent
 */
class AddressArea extends Model
{
    use HasUuids;

    protected $fillable = [
        'country_id',
        'parent_id',
        'country_code',
        'type',
        'level',
        'name',
        'native_name',
        'code',
        'slug',
        'latitude',
        'longitude',
        'source',
        'source_id',
        'parent_source_id',
        'source_payload',
        'synced_at',
        'metadata',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.areas', 'address_areas');
    }

    /**
     * @return BelongsTo<AddressCountry, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(AddressCountry::class, 'country_id');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<AddressAreaStateLink, $this>
     */
    public function stateLinks(): HasMany
    {
        return $this->hasMany(AddressAreaStateLink::class, 'address_area_id');
    }

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'synced_at' => 'immutable_datetime',
            'source_payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
