<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string|null $address_id
 * @property string $snapshotable_type
 * @property string $snapshotable_id
 * @property string|null $reason
 * @property string|null $label
 * @property string|null $line1
 * @property string|null $line2
 * @property string|null $line3
 * @property string|null $city
 * @property string|null $district
 * @property string|null $state
 * @property string|null $postcode
 * @property string|null $country
 * @property string|null $country_code
 * @property string|null $formatted_address
 * @property array|null $formatted_lines
 * @property array|null $components
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $provider
 * @property string|null $provider_place_id
 * @property array|null $metadata
 * @property string|null $google_maps_url
 * @property string|null $waze_url
 * @property array|null $navigation_links
 */
class AddressSnapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'address_id',
        'snapshotable_type',
        'snapshotable_id',
        'reason',
        'label',
        'line1',
        'line2',
        'line3',
        'city',
        'district',
        'state',
        'postcode',
        'country',
        'country_code',
        'formatted_address',
        'formatted_lines',
        'components',
        'latitude',
        'longitude',
        'provider',
        'provider_place_id',
        'metadata',
        'google_maps_url',
        'waze_url',
        'navigation_links',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.snapshots', 'address_snapshots');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function snapshotable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'formatted_lines' => 'array',
            'components' => 'array',
            'metadata' => 'array',
            'navigation_links' => 'array',
        ];
    }
}
