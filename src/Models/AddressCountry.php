<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $iso2
 * @property string|null $iso3
 * @property string|null $numeric_code
 * @property string $entity_type
 * @property bool|null $is_independent
 * @property string $name
 * @property string|null $official_name
 * @property string|null $common_name
 * @property string|null $native
 * @property string|null $emoji
 * @property string|null $emoji_unicode
 * @property string|null $phone_code
 * @property array|null $calling_codes
 * @property string|null $capital
 * @property float|null $capital_latitude
 * @property float|null $capital_longitude
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $region
 * @property string|null $subregion
 * @property array|null $currencies
 * @property string|null $currency
 * @property array|null $language_codes
 * @property array|null $timezones
 * @property array|null $top_level_domains
 * @property array|null $metadata
 */
class AddressCountry extends Model
{
    use HasUuids;

    protected $fillable = [
        'iso2',
        'iso3',
        'numeric_code',
        'entity_type',
        'is_independent',
        'name',
        'official_name',
        'common_name',
        'native',
        'emoji',
        'emoji_unicode',
        'phone_code',
        'calling_codes',
        'capital',
        'capital_latitude',
        'capital_longitude',
        'latitude',
        'longitude',
        'region',
        'subregion',
        'currencies',
        'currency',
        'language_codes',
        'timezones',
        'top_level_domains',
        'metadata',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.countries', 'countries');
    }

    protected function casts(): array
    {
        return [
            'is_independent' => 'boolean',
            'calling_codes' => 'array',
            'currencies' => 'array',
            'language_codes' => 'array',
            'timezones' => 'array',
            'top_level_domains' => 'array',
            'metadata' => 'array',
        ];
    }
}
