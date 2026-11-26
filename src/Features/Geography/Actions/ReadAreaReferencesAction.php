<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Features\Geography\Api\AreaReferenceReader;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaName;
use AIArmada\Addressing\Features\Geography\Models\AddressAreaRelationship;
use AIArmada\Addressing\Support\ModelResolver;
use Carbon\CarbonImmutable;

final readonly class ReadAreaReferencesAction implements AreaReferenceReader
{
    public function aliases(array $areaIds, ?string $source = null): array
    {
        if ($areaIds === []) {
            return [];
        }

        return ModelResolver::areaNameClass()::query()
            ->whereIn('address_area_id', $areaIds)
            ->when($source !== null, static fn ($query) => $query->where('source', $source))
            ->get(['address_area_id', 'name', 'name_type', 'is_preferred'])
            ->map(static fn (AddressAreaName $alias): array => [
                'area_id' => (string) $alias->getAttribute('address_area_id'),
                'name' => (string) $alias->getAttribute('name'),
                'name_type' => (string) $alias->getAttribute('name_type'),
                'is_preferred' => (bool) $alias->getAttribute('is_preferred'),
            ])
            ->values()
            ->all();
    }

    public function activeContainmentParents(array $childIds): array
    {
        if ($childIds === []) {
            return [];
        }

        $today = CarbonImmutable::now();

        return ModelResolver::areaRelationshipClass()::query()
            ->whereIn('child_address_area_id', $childIds)
            ->where('relationship_type', 'contains')
            ->whereHas('parent', static fn ($query) => $query->where('is_active', true))
            ->where(static fn ($query) => $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(static fn ($query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today))
            ->get(['parent_address_area_id', 'child_address_area_id', 'hierarchy_type'])
            ->map(static fn (AddressAreaRelationship $edge): array => [
                'parent_id' => (string) $edge->getAttribute('parent_address_area_id'),
                'child_id' => (string) $edge->getAttribute('child_address_area_id'),
                'hierarchy_type' => $edge->getAttribute('hierarchy_type') === null ? null : (string) $edge->getAttribute('hierarchy_type'),
            ])
            ->values()
            ->all();
    }

    public function countryExists(string $countryCode): bool
    {
        return ModelResolver::countryClass()::query()->where('iso2', $countryCode)->exists();
    }

    public function countryCodes(): array
    {
        return ModelResolver::countryClass()::query()->pluck('iso2')
            ->map(static fn ($code): string => mb_strtoupper((string) $code))->values()->all();
    }

    public function areaIdForSource(string $countryCode, string $source, string $sourceId): ?string
    {
        $id = ModelResolver::areaClass()::query()->where('country_code', $countryCode)
            ->where('source', $source)->where('source_id', $sourceId)->value('id');

        return is_string($id) ? $id : null;
    }

    public function areaIdsForSource(string $countryCode, string $source): array
    {
        return ModelResolver::areaClass()::query()->where('country_code', $countryCode)
            ->where('source', $source)->pluck('id', 'source_id')->all();
    }

    public function areaMetadata(array $areaIds): array
    {
        return ModelResolver::areaClass()::query()->whereIn('id', $areaIds)
            ->get(['id', 'source_id', 'name'])
            ->mapWithKeys(static fn ($area): array => [(string) $area->getKey() => [
                'source_id' => (string) $area->source_id,
                'name' => (string) $area->name,
            ]])->all();
    }
}
