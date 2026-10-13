<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Models;

use AIArmada\Addressing\Database\Factories\ResolutionGapFactory;
use AIArmada\Addressing\Support\AddressingTableResolver;
use AIArmada\Addressing\Support\ModelResolver;
use AIArmada\CommerceSupport\Traits\HasOwner;
use AIArmada\CommerceSupport\Traits\HasOwnerScopeConfig;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string|null $owner_type
 * @property string|null $owner_id
 * @property string $source
 * @property string $country_code
 * @property string $role
 * @property string $value
 * @property string $normalized
 * @property string $dedupe_hash
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
    use HasFactory;
    use HasOwner;
    use HasOwnerScopeConfig;
    use HasUuids;

    protected static string $ownerScopeConfigKey = 'addressing.features.owner';

    protected static function booted(): void
    {
        static::creating(function (ResolutionGap $gap): void {
            $gap->dedupe_hash = self::dedupeHashFor(
                $gap->getAttribute('owner_type'),
                $gap->getAttribute('owner_id'),
                (string) $gap->getAttribute('source'),
                (string) $gap->getAttribute('country_code'),
                (string) $gap->getAttribute('role'),
                (string) $gap->getAttribute('normalized'),
            );
        });

        static::updating(function (ResolutionGap $gap): void {
            if (! $gap->isDirty(['owner_type', 'owner_id', 'source', 'country_code', 'role', 'normalized'])) {
                return;
            }

            $gap->dedupe_hash = self::dedupeHashFor(
                $gap->getAttribute('owner_type'),
                $gap->getAttribute('owner_id'),
                (string) $gap->getAttribute('source'),
                (string) $gap->getAttribute('country_code'),
                (string) $gap->getAttribute('role'),
                (string) $gap->getAttribute('normalized'),
            );
        });
    }

    public static function dedupeHashFor(
        mixed $ownerType,
        mixed $ownerId,
        string $source,
        string $countryCode,
        string $role,
        string $normalized,
    ): string {
        return hash('sha256', implode("\0", [
            (string) ($ownerType ?? ''),
            (string) ($ownerId ?? ''),
            $source,
            $countryCode,
            $role,
            $normalized,
        ]));
    }

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

    protected static function newFactory(): ResolutionGapFactory
    {
        return ResolutionGapFactory::new();
    }
}
