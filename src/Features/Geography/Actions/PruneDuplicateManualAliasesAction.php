<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Features\Geography\Api\ResolutionAliasMaintenance;
use AIArmada\Addressing\Support\ModelResolver;

final readonly class PruneDuplicateManualAliasesAction implements ResolutionAliasMaintenance
{
    public function pruneDuplicates(string $countryCode): int
    {
        $aliasClass = ModelResolver::areaNameClass();
        $model = new $aliasClass;
        $table = $model->getTable();

        return $model->getConnection()->transaction(static function () use ($aliasClass, $table, $countryCode): int {
            return $aliasClass::query()
                ->where('source', 'manual')
                ->whereHas('area', static fn ($query) => $query->where('country_code', $countryCode))
                ->whereExists(static function ($query) use ($table): void {
                    $query->selectRaw('1')
                        ->from($table . ' as provider_alias')
                        ->whereColumn('provider_alias.address_area_id', $table . '.address_area_id')
                        ->whereColumn('provider_alias.name', $table . '.name')
                        ->where('provider_alias.source', '!=', 'manual');
                })
                ->delete();
        });
    }
}
