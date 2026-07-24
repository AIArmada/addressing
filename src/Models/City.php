<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string|null $state_id
 * @property string $name
 * @property string|null $label
 * @property float|null $latitude
 * @property float|null $longitude
 * @property array|null $metadata
 * @property-read State|null $state
 */
class City extends Model
{
    use HasUuids;

    protected $fillable = [
        'state_id',
        'country_id',
        'name',
        'label',
        'latitude',
        'longitude',
        'metadata',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.cities', 'cities');
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

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'metadata' => 'array',
        ];
    }
}
