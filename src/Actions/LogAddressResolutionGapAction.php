<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\ResolutionGap;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LogAddressResolutionGapAction
{
    /**
     * @param  array<string, mixed>|null  $context
     */
    public function execute(
        string $source,
        string $countryCode,
        string $role,
        string $value,
        string $reason = 'unmatched',
        ?array $context = null,
    ): ResolutionGap {
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

        if (mb_strlen($countryCode) !== 2) {
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

        try {
            return DB::transaction(fn (): ResolutionGap => $this->upsert($source, $countryCode, $role, $value, $reason, $normalized, $context));
        } catch (UniqueConstraintViolationException) {
            return DB::transaction(fn (): ResolutionGap => $this->upsert($source, $countryCode, $role, $value, $reason, $normalized, $context));
        }
    }

    public static function normalize(string $value): string
    {
        return (string) preg_replace('/\s+/u', ' ', mb_strtolower(mb_trim($value)));
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    private function upsert(
        string $source,
        string $countryCode,
        string $role,
        string $value,
        string $reason,
        string $normalized,
        ?array $context,
    ): ResolutionGap {
        $gap = ResolutionGap::query()
            ->where('source', $source)
            ->where('country_code', $countryCode)
            ->where('role', $role)
            ->where('normalized', $normalized)
            ->first();

        $now = CarbonImmutable::now();

        if (! $gap instanceof ResolutionGap) {
            return ResolutionGap::query()->create([
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
        }

        $gap->increment('hits');
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

        $gap->save();

        return $gap;
    }
}
