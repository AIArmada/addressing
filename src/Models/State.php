<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $country_id
 * @property string $name
 * @property string|null $country_code
 * @property string|null $code
 * @property float|null $latitude
 * @property float|null $longitude
 * @property-read AddressCountry $country
 */
class State extends Model
{
    use HasUuids;

    protected $fillable = [
        'country_id',
        'name',
        'country_code',
        'code',
        'latitude',
        'longitude',
    ];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('states');
    }

    /**
     * @return BelongsTo<AddressCountry, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(AddressCountry::class, 'country_id');
    }

    /**
     * @return HasMany<City, $this>
     */
    public function cities(): HasMany
    {
        return $this->hasMany(ModelResolver::cityClass(), 'state_id');
    }

    /**
     * @return HasMany<AddressAreaStateLink, $this>
     */
    public function addressAreaLinks(): HasMany
    {
        return $this->hasMany(AddressAreaStateLink::class, 'state_id');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
