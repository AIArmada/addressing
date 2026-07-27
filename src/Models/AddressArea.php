<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|null $parent_id
 * @property string|null $country_id
 * @property string|null $parent_id
 * @property string $country_code
 * @property string $type
 * @property int|null $level
 * @property string $name
 * @property string|null $native_name
 * @property string|null $code
 * @property string $slug
 * @property bool $is_active
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

    protected static function booted(): void
    {
        static::deleting(function (AddressArea $area): void {
            AddressAreaAssignment::query()->where('address_area_id', $area->getKey())->delete();
            AddressAreaCityLink::query()->where('address_area_id', $area->getKey())->delete();
            AddressAreaName::query()->where('address_area_id', $area->getKey())->delete();
            AddressAreaPostalCode::query()->where('address_area_id', $area->getKey())->delete();
            AddressAreaRole::query()->where('address_area_id', $area->getKey())->delete();
            AddressAreaStateLink::query()->where('address_area_id', $area->getKey())->delete();
            AddressAreaRelationship::query()
                ->where('parent_address_area_id', $area->getKey())
                ->orWhere('child_address_area_id', $area->getKey())
                ->delete();
            self::query()->where('parent_id', $area->getKey())->update(['parent_id' => null]);
        });
    }

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
        'is_active',
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
     * @return HasMany<AddressArea, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return HasMany<AddressAreaStateLink, $this>
     */
    public function stateLinks(): HasMany
    {
        return $this->hasMany(AddressAreaStateLink::class, 'address_area_id');
    }

    /**
     * @return HasMany<AddressAreaCityLink, $this>
     */
    public function cityLinks(): HasMany
    {
        return $this->hasMany(AddressAreaCityLink::class, 'address_area_id');
    }

    /** @return HasMany<AddressAreaName, $this> */
    public function names(): HasMany
    {
        return $this->hasMany(AddressAreaName::class, 'address_area_id');
    }

    /** @return HasMany<AddressAreaRole, $this> */
    public function roles(): HasMany
    {
        return $this->hasMany(AddressAreaRole::class, 'address_area_id');
    }

    /** @return BelongsToMany<AddressArea, $this> */
    public function relatedAreas(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            config('addressing.tables.area_relationships', 'address_area_relationships'),
            'parent_address_area_id',
            'child_address_area_id',
        )->withPivot(['relationship_type', 'hierarchy_type', 'source', 'valid_from', 'valid_until', 'metadata']);
    }

    /** @return BelongsToMany<AddressArea, $this> */
    public function ancestors(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            config('addressing.tables.area_relationships', 'address_area_relationships'),
            'child_address_area_id',
            'parent_address_area_id',
        )->withPivot(['relationship_type', 'hierarchy_type', 'source', 'valid_from', 'valid_until', 'metadata']);
    }

    /** @return BelongsToMany<PostalCode, $this, AddressAreaPostalCode, 'pivot'> */
    public function postalCodes(): BelongsToMany
    {
        return $this->belongsToMany(
            PostalCode::class,
            config('addressing.tables.area_postal_codes', 'address_area_postal_codes'),
            'address_area_id',
            'postal_code_id',
        )
            ->using(AddressAreaPostalCode::class)
            ->withPivot(['source', 'source_id', 'relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'synced_at' => 'immutable_datetime',
            'source_payload' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
