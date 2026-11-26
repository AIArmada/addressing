<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\ResolutionGaps\Actions;

use AIArmada\Addressing\Contracts\ResolutionGapCuration;
use AIArmada\Addressing\Features\Geography\Api\AreaAliasMatcher;
use AIArmada\Addressing\Features\ResolutionGaps\Models\ResolutionGap;
use AIArmada\CommerceSupport\Support\OwnerWriteGuard;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final readonly class CurateResolutionGapAction implements ResolutionGapCuration
{
    public function __construct(
        private AreaAliasMatcher $areaMatcher,
    ) {}

    /**
     * Runs inside the caller's owner scope: the gap is re-read with a lock,
     * so matching a tenant gap needs that tenant's context and matching a
     * global gap needs explicit global context.
     */
    public function match(string $gapId, string $areaId, ?string $actor = null): void
    {
        $matchedBy = $actor !== null ? mb_trim($actor) : null;

        if ($matchedBy !== null && mb_strlen($matchedBy) > 255) {
            throw ValidationException::withMessages([
                'matched_by' => __('The actor identifier must not exceed 255 characters.'),
            ]);
        }

        $attempt = function () use ($gapId, $areaId, $matchedBy): void {
            ResolutionGap::configuredClass()::query()->getConnection()->transaction(function () use ($gapId, $areaId, $matchedBy): void {
                $this->persistMatch($gapId, $areaId, $matchedBy);
            });
        };

        try {
            $attempt();
        } catch (UniqueConstraintViolationException) {
            $attempt();
        }
    }

    public function ignore(string $gapId): void
    {
        $gapClass = ResolutionGap::configuredClass();

        $gapClass::query()->getConnection()->transaction(function () use ($gapId, $gapClass): void {
            if ($gapClass::ownerScopeConfig()->enabled) {
                OwnerWriteGuard::findOrFailForOwner($gapClass, $gapId);
            }

            $locked = $gapClass::query()->whereKey($gapId)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'ignored') {
                return;
            }

            $locked->status = 'ignored';

            if (! $locked->save()) {
                throw new RuntimeException('The resolution gap could not be ignored.');
            }
        });
    }

    private function persistMatch(string $gapId, string $areaId, ?string $matchedBy): void
    {
        $gapClass = ResolutionGap::configuredClass();

        if ($gapClass::ownerScopeConfig()->enabled) {
            OwnerWriteGuard::findOrFailForOwner($gapClass, $gapId);
        }

        $gap = $gapClass::query()->whereKey($gapId)->lockForUpdate()->firstOrFail();

        if ($gap->status !== 'open') {
            throw ValidationException::withMessages([
                'gap' => __('Only open resolution gaps can be matched.'),
            ]);
        }

        if ($gap->reason !== 'unmatched') {
            throw ValidationException::withMessages([
                'gap' => $gap->reason === 'ambiguous'
                    ? __('Ambiguous gaps need data cleanup before they can be matched to an area.')
                    : __('Only gaps with an unmatched reason can be matched to an area.'),
            ]);
        }

        if ($gap->role === 'state') {
            throw ValidationException::withMessages([
                'gap' => __('State gaps cannot be matched to an area because states have no alias table. Fix the integration prefix rules or rename the provider state instead.'),
            ]);
        }

        $this->areaMatcher->match($areaId, (string) $gap->country_code, (string) $gap->role, (string) $gap->value, (string) $gap->normalized);

        $gap->status = 'matched';
        $gap->matched_area_id = $areaId;
        $gap->matched_by = $matchedBy === '' ? null : $matchedBy;
        $gap->matched_at = CarbonImmutable::now();

        if (! $gap->save()) {
            throw new RuntimeException('The resolution gap could not be matched.');
        }
    }
}
