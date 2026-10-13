<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Explicit host/provider mapping between an AddressArea node and a canonical State row.
 */
class AddressAreaStateLink extends Model
{
    use HasUuids;

    protected static function booted(): void
    {
        static::saving(static function (AddressAreaStateLink $link): void {
            if ($link->getAttribute('hierarchy_type') === null) {
                $link->setAttribute('hierarchy_type', '');
            }
        });
    }

    protected $fillable = [
        'address_area_id',
        'state_id',
        'hierarchy_type',
        'metadata',
    ];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('area_state_links');
    }

    /**
     * @return BelongsTo<AddressArea, $this>
     */
    public function addressArea(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::areaClass(), 'address_area_id');
    }

    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::stateClass(), 'state_id');
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
