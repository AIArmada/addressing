<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Traits\HasOwner;
use AIArmada\CommerceSupport\Traits\HasOwnerScopeConfig;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string|null $country_id
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
    use HasOwner;
    use HasOwnerScopeConfig;
    use HasUuids;

    protected static string $ownerScopeConfigKey = 'addressing.features.owner';

    protected static function booted(): void
    {
        static::saving(function (Address $address): void {
            $normalized = app(AddressNormalizer::class)->normalize($address->attributesToArray());
            $address->forceFill($normalized->toModelAttributes());
        });

        static::deleting(function (Address $address): void {
            $address->areaAssignments()->delete();
            $address->addressableLinks()->delete();
            $address->snapshots()->update(['address_id' => null]);
        });
    }

    protected $fillable = [
        'country_id',
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
    ];

    public function setAttribute($key, $value): mixed
    {
        return match ($key) {
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
            'geohash',
            'geo_precision',
            'provider',
            'provider_place_id',
            'validation_status',
            'google_maps_url',
            'waze_url' => parent::setAttribute(
                $key,
                is_string($value) ? mb_trim($value) : $value,
            ),
            default => parent::setAttribute($key, $value),
        };
    }

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('addresses');
    }

    /**
     * @return BelongsTo<Model, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::countryClass(), 'country_id');
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

    /** @return HasMany<AddressAreaAssignment, $this> */
    public function areaAssignments(): HasMany
    {
        return $this->hasMany(AddressAreaAssignment::class, 'address_id');
    }

    /** @return HasMany<AddressSnapshot, $this> */
    public function snapshots(): HasMany
    {
        return $this->hasMany(AddressSnapshot::class, 'address_id');
    }

    /**
     * @return HasMany<Addressable, $this>
     */
    public function addressableLinks(): HasMany
    {
        return $this->hasMany(Addressable::class, 'address_id');
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
