<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaName;
use AIArmada\Addressing\Models\ResolutionGap;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MatchGapToAreaAction
{
    public function __construct(
        private readonly CountryAddressProfileResolver $profiles,
    ) {}

    public function execute(ResolutionGap $gap, AddressArea $area, ?string $matchedBy = null): ResolutionGap
    {
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

        if (mb_strtoupper(mb_trim((string) $area->country_code)) !== mb_strtoupper(mb_trim((string) $gap->country_code))) {
            throw ValidationException::withMessages([
                'area' => __('The selected area must belong to the gap country.'),
            ]);
        }

        $definition = $this->profiles->definitionForRole($gap->country_code, (string) $gap->role);

        if ($definition === null) {
            throw ValidationException::withMessages([
                'gap' => __('The gap role is not defined by the country address profile.'),
            ]);
        }

        if ($definition['level']->kind !== 'area') {
            throw ValidationException::withMessages([
                'gap' => __('The gap role is not an assignable area role.'),
            ]);
        }

        if (! $this->areaMatchesDefinition($area, $definition['level'])) {
            throw ValidationException::withMessages([
                'area' => __('The selected area does not match the required hierarchy level.'),
            ]);
        }

        $this->assertNoAmbiguity($gap, $area);

        $matchedBy = $matchedBy !== null ? mb_trim($matchedBy) : null;

        if ($matchedBy !== null && mb_strlen($matchedBy) > 255) {
            throw ValidationException::withMessages([
                'matched_by' => __('The actor identifier must not exceed 255 characters.'),
            ]);
        }

        try {
            return DB::transaction(fn (): ResolutionGap => $this->persistMatch($gap, $area, $matchedBy));
        } catch (UniqueConstraintViolationException) {
            return DB::transaction(fn (): ResolutionGap => $this->persistMatch($gap, $area, $matchedBy));
        }
    }

    private function persistMatch(ResolutionGap $gap, AddressArea $area, ?string $matchedBy): ResolutionGap
    {
        $locked = ResolutionGap::query()->whereKey($gap->getKey())->lockForUpdate()->firstOrFail();

        if ($locked->status !== 'open') {
            throw ValidationException::withMessages([
                'gap' => __('Only open resolution gaps can be matched.'),
            ]);
        }

        if (! $this->targetAlreadyHasValue($locked, $area)) {
            AddressAreaName::query()->create([
                'address_area_id' => $area->getKey(),
                'name' => mb_trim((string) $locked->value),
                'source' => 'manual',
                'name_type' => 'common',
                'is_preferred' => false,
            ]);
        }

        $locked->status = 'matched';
        $locked->matched_area_id = $area->getKey();
        $locked->matched_by = $matchedBy === '' ? null : $matchedBy;
        $locked->matched_at = CarbonImmutable::now();
        $locked->save();

        return $locked->fresh() ?? $locked;
    }

    private function areaMatchesDefinition(AddressArea $area, AddressLevelDefinition $definition): bool
    {
        $types = $definition->areaTypes !== []
            ? $definition->areaTypes
            : ($definition->areaType !== null ? [$definition->areaType] : []);
        $levels = $definition->areaLevels !== []
            ? $definition->areaLevels
            : ($definition->areaLevel !== null ? [$definition->areaLevel] : []);

        return ($types === [] || in_array($area->type, $types, true))
            && ($levels === [] || in_array($area->level, $levels, true));
    }

    private function assertNoAmbiguity(ResolutionGap $gap, AddressArea $area): void
    {
        $normalized = (string) $gap->normalized;
        $countryCode = mb_strtoupper(mb_trim((string) $gap->country_code));

        $primaryConflict = AddressArea::query()
            ->where('country_code', $countryCode)
            ->whereKeyNot($area->getKey())
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->first(['id', 'name']);

        if ($primaryConflict instanceof AddressArea) {
            throw ValidationException::withMessages([
                'area' => __('The value already resolves to a different area (:name).', ['name' => (string) $primaryConflict->name]),
            ]);
        }

        $aliasConflict = AddressArea::query()
            ->where('country_code', $countryCode)
            ->whereKeyNot($area->getKey())
            ->whereHas('names', fn (Builder $names): Builder => $names->whereRaw('LOWER(name) = ?', [$normalized]))
            ->first(['id', 'name']);

        if ($aliasConflict instanceof AddressArea) {
            throw ValidationException::withMessages([
                'area' => __('The value already resolves to a different area (:name).', ['name' => (string) $aliasConflict->name]),
            ]);
        }
    }

    private function targetAlreadyHasValue(ResolutionGap $gap, AddressArea $area): bool
    {
        $normalized = (string) $gap->normalized;

        if (mb_strtolower(mb_trim((string) $area->name)) === $normalized) {
            return true;
        }

        return AddressAreaName::query()
            ->where('address_area_id', $area->getKey())
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->exists();
    }
}
