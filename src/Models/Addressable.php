<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property string $address_id
 * @property string $addressable_type
 * @property string $addressable_id
 * @property string $type
 * @property string|null $label
 * @property bool $is_primary
 * @property CarbonImmutable|null $valid_from
 * @property CarbonImmutable|null $valid_until
 * @property array|null $metadata
 * @property-read Address $address
 * @property-read Model $addressable
 */
class Addressable extends MorphPivot
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'address_id',
        'addressable_type',
        'addressable_id',
        'type',
        'label',
        'is_primary',
        'valid_from',
        'valid_until',
        'metadata',
    ];

    protected static function booted(): void
    {
        static::creating(function (Addressable $addressable): void {
            $addressable->id ??= (string) Str::orderedUuid();
        });
    }

    public function getTable(): string
    {
        return config('addressing.tables.addressables', 'addressables');
    }

    /**
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'valid_from' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }
}
