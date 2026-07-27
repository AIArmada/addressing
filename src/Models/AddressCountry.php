<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Models\Currency;
use AIArmada\CommerceSupport\Models\Timezone;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $iso2
 * @property string $name
 * @property string|null $phone_code
 * @property string|null $iso3
 * @property string|null $numeric_code
 * @property string|null $native
 * @property string|null $capital
 * @property string|null $region
 * @property string|null $subregion
 * @property string|null $tld
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 * @property array|null $translations
 */
class AddressCountry extends Model
{
    use HasUuids;

    protected $fillable = [
        'iso2',
        'name',
        'phone_code',
        'iso3',
        'numeric_code',
        'native',
        'capital',
        'region',
        'subregion',
        'tld',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
        'translations',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.countries', 'countries');
    }

    protected function casts(): array
    {
        return [
            'translations' => 'array',
        ];
    }

    /**
     * @return BelongsToMany<Currency, $this>
     */
    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, config('addressing.tables.country_currency_links', 'country_currency_links'), 'country_id', 'currency_id');
    }

    /**
     * @return BelongsToMany<Timezone, $this>
     */
    public function timezones(): BelongsToMany
    {
        return $this->belongsToMany(Timezone::class, config('addressing.tables.country_timezone_links', 'country_timezone_links'), 'country_id', 'timezone_id');
    }

    /**
     * @return HasMany<State, $this>
     */
    public function states(): HasMany
    {
        return $this->hasMany(ModelResolver::stateClass(), 'country_id');
    }
}
