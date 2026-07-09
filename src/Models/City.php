<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $state_id
 * @property string $name
 * @property string|null $postcode
 * @property string|null $label
 * @property array|null $metadata
 * @property-read State $state
 */
class City extends Model
{
    use HasUuids;

    protected $fillable = [
        'state_id',
        'country_id',
        'name',
        'postcode',
        'label',
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
        return $this->belongsTo(State::class, 'state_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(AddressCountry::class, 'country_id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
