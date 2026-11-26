<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Models;

use AIArmada\Addressing\Database\Factories\StateFactory;
use AIArmada\Addressing\Features\Geography\Api\GeographyReferenceMaintenance;
use AIArmada\Addressing\Persistence\TransactionalDeletionModel;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
class State extends TransactionalDeletionModel
{
    use HasFactory;
    use HasUuids;

    protected static function booted(): void
    {
        static::deleting(static function (State $state): void {
            app(GeographyReferenceMaintenance::class)->clearState((string) $state->getKey());
        });
    }

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
        return $this->belongsTo(ModelResolver::countryClass(), 'country_id');
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
        return $this->hasMany(ModelResolver::areaStateLinkClass(), 'state_id');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function newFactory(): StateFactory
    {
        return StateFactory::new();
    }
}
