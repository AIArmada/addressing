<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $country_id
 * @property string $name
 * @property string|null $code
 * @property string|null $label
 * @property array|null $metadata
 * @property-read AddressCountry $country
 */
class State extends Model
{
    use HasUuids;

    protected $fillable = [
        'country_id',
        'name',
        'code',
        'label',
        'metadata',
    ];

    public function getTable(): string
    {
        return config('addressing.tables.states', 'states');
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
        return $this->hasMany(City::class, 'state_id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
