<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaAliasWriter;
use AIArmada\Addressing\Data\AddressLevelDefinition;
use AIArmada\Addressing\Features\Geography\Api\AreaAliasMatcher;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Support\CountryAddressProfileResolver;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final readonly class MatchAreaAliasAction implements AreaAliasMatcher
{
    public function __construct(
        private CountryAddressProfileResolver $profiles,
        private AddressAreaAliasWriter $aliases,
    ) {}

    public function match(string $areaId, string $countryCode, string $role, string $value, string $normalized): void
    {
        ModelResolver::countryClass()::query()->where('iso2', $countryCode)->lockForUpdate()->first();
        $area = ModelResolver::areaClass()::query()->whereKey($areaId)->lockForUpdate()->firstOrFail();

        if (! $area->is_active) {
            throw ValidationException::withMessages(['area' => __('The selected area is inactive.')]);
        }

        if (mb_strtoupper(mb_trim((string) $area->country_code)) !== mb_strtoupper(mb_trim((string) $countryCode))) {
            throw ValidationException::withMessages([
                'area' => __('The selected area must belong to the gap country.'),
            ]);
        }

        $definition = $this->profiles->definitionForRole($countryCode, (string) $role);

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

        $this->assertNoAmbiguity($countryCode, $normalized, $area);

        if (! $this->targetAlreadyHasValue($normalized, $area)) {
            $this->aliases->handle((string) $area->getKey(), [
                'name' => mb_trim($value),
                'name_type' => 'common',
                'is_preferred' => false,
            ]);
        }

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

    private function assertNoAmbiguity(string $countryCode, string $normalized, AddressArea $area): void
    {
        $countryCode = mb_strtoupper(mb_trim((string) $countryCode));

        $primaryConflict = ModelResolver::areaClass()::query()
            ->where('country_code', $countryCode)
            ->whereKeyNot($area->getKey())
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->first(['id', 'name']);

        if ($primaryConflict instanceof AddressArea) {
            throw ValidationException::withMessages([
                'area' => __('The value already resolves to a different area (:name).', ['name' => (string) $primaryConflict->name]),
            ]);
        }

        $aliasConflict = ModelResolver::areaClass()::query()
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

    private function targetAlreadyHasValue(string $normalized, AddressArea $area): bool
    {

        if (mb_strtolower(mb_trim((string) $area->name)) === $normalized) {
            return true;
        }

        return ModelResolver::areaNameClass()::query()
            ->where('address_area_id', $area->getKey())
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->exists();
    }
}
