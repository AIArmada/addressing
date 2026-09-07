<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $country_id
 * @property string|null $state_id
 * @property string $name
 * @property string|null $country_code
 * @property string|null $state_code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property-read State|null $state
 */
class City extends Model
{
    use HasUuids;

    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'country_code',
        'state_code',
        'latitude',
        'longitude',
    ];

    public function getTable(): string
    {
        return config('addressing.database.tables.cities', 'cities');
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::stateClass(), 'state_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(AddressCountry::class, 'country_id');
    }

    /**
     * @return HasMany<AddressAreaCityLink, $this>
     */
    public function addressAreaLinks(): HasMany
    {
        return $this->hasMany(AddressAreaCityLink::class, 'city_id');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
