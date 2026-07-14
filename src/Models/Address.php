<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @property string $id
 * @property string|null $country_id
 * @property string|null $admin_area_1_id
 * @property string|null $admin_area_2_id
 * @property string|null $admin_area_3_id
 * @property string|null $admin_area_4_id
 * @property string|null $label
 * @property string|null $line1
 * @property string|null $line2
 * @property string|null $line3
 * @property string|null $building_name
 * @property string|null $unit_number
 * @property string|null $floor
 * @property string|null $block
 * @property string|null $street_number
 * @property string|null $street_name
 * @property string|null $neighbourhood
 * @property string|null $village
 * @property string|null $city
 * @property string|null $state_id
 * @property string|null $city_id
 * @property string|null $state
 * @property string|null $postcode
 * @property string|null $country
 * @property string|null $country_code
 * @property string|null $raw_address
 * @property string|null $formatted_address
 * @property array|null $formatted_lines
 * @property array|null $components
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $geohash
 * @property string|null $geo_precision
 * @property string|null $provider
 * @property string|null $provider_place_id
 * @property array|null $provider_payload
 * @property string $validation_status
 * @property CarbonImmutable|null $validated_at
 * @property array|null $metadata
 * @property string|null $google_maps_url
 * @property string|null $waze_url
 * @property array|null $navigation_links
 */
class Address extends Model
{
    use HasUuids;

    protected $fillable = [
        'country_id',
        'admin_area_1_id',
        'admin_area_2_id',
        'admin_area_3_id',
        'admin_area_4_id',
        'state_id',
        'city_id',
        'label',
        'line1',
        'line2',
        'line3',
        'building_name',
        'unit_number',
        'floor',
        'block',
        'street_number',
        'street_name',
        'neighbourhood',
        'village',
        'city',
        'state',
        'postcode',
        'country',
        'country_code',
        'raw_address',
        'formatted_address',
        'formatted_lines',
        'components',
        'latitude',
        'longitude',
        'geohash',
        'geo_precision',
        'provider',
        'provider_place_id',
        'provider_payload',
        'validation_status',
        'validated_at',
        'metadata',
        'google_maps_url',
        'waze_url',
        'navigation_links',
        'lat',
        'lng',
        'google_place_id',
    ];

    public function setAttribute($key, $value): mixed
    {
        return match ($key) {
            'lat' => parent::setAttribute('latitude', $value),
            'lng' => parent::setAttribute('longitude', $value),
            'google_place_id' => parent::setAttribute('provider_place_id', $value),
            'line1',
            'line2',
            'line3',
            'building_name',
            'unit_number',
            'floor',
            'block',
            'street_number',
            'street_name',
            'neighbourhood',
            'village',
            'city',
            'state',
            'postcode',
            'country',
            'country_code' => parent::setAttribute(
                $key,
                is_string($value) ? mb_trim($value) : $value,
            ),
            default => parent::setAttribute($key, $value),
        };
    }

    public function getAttribute($key): mixed
    {
        return match ($key) {
            'lat' => parent::getAttribute('latitude'),
            'lng' => parent::getAttribute('longitude'),
            'google_place_id' => parent::getAttribute('provider_place_id'),
            'country' => $this->relationLoaded('country')
                ? $this->getRelation('country')
                : parent::getAttribute('country'),
            'state' => $this->relationLoaded('state')
                ? $this->getRelation('state')
                : parent::getAttribute('state'),
            'city' => $this->relationLoaded('city')
                ? $this->getRelation('city')
                : parent::getAttribute('city'),
            default => parent::getAttribute($key),
        };
    }

    public function getTable(): string
    {
        return config('addressing.tables.addresses', 'addresses');
    }

    /**
     * @return BelongsTo<AddressCountry, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(AddressCountry::class, 'country_id');
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::stateClass(), 'state_id');
    }

    /**
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::cityClass(), 'city_id');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function adminArea1(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'admin_area_1_id');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function adminArea2(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'admin_area_2_id');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function adminArea3(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'admin_area_3_id');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function adminArea4(): BelongsTo
    {
        return $this->belongsTo(AddressArea::class, 'admin_area_4_id');
    }

    /**
     * @return HasMany<Addressable, $this>
     */
    public function addressableLinks(): HasMany
    {
        return $this->hasMany(Addressable::class, 'address_id');
    }

    /**
     * @return MorphToMany<Model, $this>
     */
    public function addressables(): MorphToMany
    {
        return $this->morphedByMany(
            Model::class,
            'addressable',
            config('addressing.tables.addressables', 'addressables'),
        );
    }

    protected function casts(): array
    {
        return [
            'formatted_lines' => 'array',
            'components' => 'array',
            'provider_payload' => 'array',
            'validated_at' => 'immutable_datetime',
            'metadata' => 'array',
            'navigation_links' => 'array',
        ];
    }
}
