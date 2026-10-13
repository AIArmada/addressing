<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Database\Factories\CityFactory;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\GeographyReferenceCleanup;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
    use HasFactory;
    use HasUuids;

    protected static function booted(): void
    {
        static::deleting(static function (City $city): void {
            GeographyReferenceCleanup::cleanupCityReferences($city);
        });
    }

    public function delete(): ?bool
    {
        return DB::transaction(fn (): ?bool => parent::delete());
    }

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
        return AddressingTableResolver::resolve('cities');
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::stateClass(), 'state_id');
    }

    /**
     * @return BelongsTo<AddressCountry, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::countryClass(), 'country_id');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function newFactory(): CityFactory
    {
        return CityFactory::new();
    }
}
