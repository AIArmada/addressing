<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\ResolutionGaps\Actions;

use AIArmada\Addressing\Contracts\ResolutionGapReporter;
use AIArmada\Addressing\Features\ResolutionGaps\Models\ResolutionGap;
use AIArmada\CommerceSupport\Support\OwnerContext;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final readonly class ReportResolutionGapAction implements ResolutionGapReporter
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function report(
        string $source,
        string $countryCode,
        string $role,
        string $value,
        string $reason = 'unmatched',
        ?array $context = null,
    ): string {
        $source = mb_trim($source);
        $countryCode = mb_strtoupper(mb_trim($countryCode));
        $role = mb_trim($role);
        $value = mb_trim($value);
        $reason = mb_strtolower(mb_trim($reason));

        if ($source === '') {
            throw ValidationException::withMessages([
                'source' => __('The gap source is required.'),
            ]);
        }

        if (mb_strlen($source) > 50) {
            throw ValidationException::withMessages([
                'source' => __('The gap source must not exceed 50 characters.'),
            ]);
        }

        if (preg_match('/^[A-Z]{2}$/D', $countryCode) !== 1) {
            throw ValidationException::withMessages([
                'country_code' => __('The gap country code must be a two-letter code.'),
            ]);
        }

        if ($role === '') {
            throw ValidationException::withMessages([
                'role' => __('The gap role is required.'),
            ]);
        }

        if (mb_strlen($role) > 50) {
            throw ValidationException::withMessages([
                'role' => __('The gap role must not exceed 50 characters.'),
            ]);
        }

        if ($value === '') {
            throw ValidationException::withMessages([
                'value' => __('The attempted value is required.'),
            ]);
        }

        if (mb_strlen($value) > 255) {
            throw ValidationException::withMessages([
                'value' => __('The attempted value must not exceed 255 characters.'),
            ]);
        }

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => __('The gap reason is required.'),
            ]);
        }

        if (mb_strlen($reason) > 20) {
            throw ValidationException::withMessages([
                'reason' => __('The gap reason must not exceed 20 characters.'),
            ]);
        }

        $normalized = self::normalize($value);

        if (mb_strlen($normalized) > 255) {
            throw ValidationException::withMessages([
                'value' => __('The attempted value must not exceed 255 characters.'),
            ]);
        }

        $gapClass = ResolutionGap::configuredClass();
        $config = $gapClass::ownerScopeConfig();
        $owner = $config->enabled ? OwnerContext::resolveConfigured($config) : null;
        $effectiveOwner = $config->enabled && $config->autoAssignOnCreate ? $owner : null;
        $attempt = fn (): ResolutionGap => $gapClass::query()->getConnection()->transaction(
            fn (): ResolutionGap => $this->upsert($effectiveOwner, $source, $countryCode, $role, $value, $reason, $normalized, $context),
        );

        try {
            return (string) $this->runWithEffectiveOwner($config->enabled, $effectiveOwner, $attempt)->getKey();
        } catch (UniqueConstraintViolationException) {
            return (string) $this->runWithEffectiveOwner($config->enabled, $effectiveOwner, $attempt)->getKey();
        }
    }

    /**
     * Gaps are telemetry: rows land under the effective owner tuple that the
     * model would persist. When that tuple is global, enter explicit global
     * context so global rows can be created or updated regardless of the
     * caller's ambient owner.
     *
     * @param  callable(): ResolutionGap  $attempt
     */
    private function runWithEffectiveOwner(bool $ownerScopingEnabled, ?Model $effectiveOwner, callable $attempt): ResolutionGap
    {
        if ($ownerScopingEnabled && $effectiveOwner === null) {
            return OwnerContext::withOwner(null, $attempt);
        }

        return $attempt();
    }

    public static function normalize(string $value): string
    {
        return (string) preg_replace('/\s+/u', ' ', mb_strtolower(mb_trim($value)));
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    private function upsert(
        ?Model $owner,
        string $source,
        string $countryCode,
        string $role,
        string $value,
        string $reason,
        string $normalized,
        ?array $context,
    ): ResolutionGap {
        $gapClass = ResolutionGap::configuredClass();
        $dedupeHash = $gapClass::dedupeHashFor(
            $owner?->getMorphClass(),
            $owner?->getKey(),
            $source,
            $countryCode,
            $role,
            $normalized,
        );

        $gap = $gapClass::query()
            ->forOwner($owner, false)
            ->where('dedupe_hash', $dedupeHash)
            ->lockForUpdate()
            ->first();

        $now = CarbonImmutable::now();

        if (! $gap instanceof ResolutionGap) {
            $gap = $gapClass::query()->make([
                'source' => $source,
                'country_code' => $countryCode,
                'role' => $role,
                'value' => $value,
                'normalized' => $normalized,
                'reason' => $reason,
                'hits' => 1,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'context' => $context,
                'status' => 'open',
            ]);

            if (! $gap->save()) {
                throw new RuntimeException('The resolution gap could not be created.');
            }

            return $gap;
        }

        $gap->hits++;
        $gap->last_seen_at = $now;

        if ($context !== null) {
            $gap->context = $context;
        }

        if ($gap->status === 'matched') {
            $gap->status = 'open';
            $gap->matched_area_id = null;
            $gap->matched_by = null;
            $gap->matched_at = null;
        }

        if (! $gap->save()) {
            throw new RuntimeException('The resolution gap could not be updated.');
        }

        return $gap;
    }
}
