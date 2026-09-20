<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $source
 * @property string $country_code
 * @property string $role
 * @property string $value
 * @property string $normalized
 * @property string $reason
 * @property int $hits
 * @property CarbonImmutable|null $first_seen_at
 * @property CarbonImmutable|null $last_seen_at
 * @property array|null $context
 * @property string $status
 * @property string|null $matched_area_id
 * @property string|null $matched_by
 * @property CarbonImmutable|null $matched_at
 * @property-read AddressArea|null $matchedArea
 */
class ResolutionGap extends Model
{
    use HasUuids;

    protected $fillable = [
        'source',
        'country_code',
        'role',
        'value',
        'normalized',
        'reason',
        'hits',
        'first_seen_at',
        'last_seen_at',
        'context',
        'status',
        'matched_area_id',
        'matched_by',
        'matched_at',
    ];

    public function getTable(): string
    {
        return AddressingTableResolver::resolve('resolution_gaps');
    }

    /** @return BelongsTo<AddressArea, $this> */
    public function matchedArea(): BelongsTo
    {
        return $this->belongsTo(ModelResolver::areaClass(), 'matched_area_id');
    }

    protected function casts(): array
    {
        return [
            'hits' => 'integer',
            'first_seen_at' => 'immutable_datetime',
            'last_seen_at' => 'immutable_datetime',
            'matched_at' => 'immutable_datetime',
            'context' => 'array',
        ];
    }
}
