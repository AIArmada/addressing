<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes\Actions;

use AIArmada\Addressing\Features\PostalCodes\Api\PostalAreaLookup;
use AIArmada\Addressing\Features\PostalCodes\Models\PostalCode;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Model;

final readonly class FindPostalAreaCandidatesAction implements PostalAreaLookup
{
    public function candidateAreaIds(string $countryCode, string $code, ?string $countryId = null): array
    {
        $areas = ModelResolver::postalCodeClass()::query()
            ->where('country_code', $countryCode)
            ->where('code', $code)
            ->where('is_active', true)
            ->with('areas')
            ->get()
            ->flatMap(static fn (PostalCode $postalCode) => $postalCode->areas)
            ->filter(static fn (Model $area): bool => $area->getAttribute('is_active') !== false)
            ->filter(static fn (Model $area): bool => $countryId === null
                || (string) $area->getAttribute('country_id') === $countryId)
            ->unique(static fn (Model $area): string => (string) $area->getKey())
            ->values();

        $primary = $areas->filter(static function (Model $area): bool {
            $pivot = $area->getRelation('pivot');

            return $pivot !== null && (bool) $pivot->is_primary;
        });

        return ($primary->isNotEmpty() ? $primary : $areas)
            ->map(static fn (Model $area): string => (string) $area->getKey())
            ->values()
            ->all();
    }
}
