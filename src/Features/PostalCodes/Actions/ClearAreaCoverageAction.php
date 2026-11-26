<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\PostalCodes\Actions;

use AIArmada\Addressing\Features\PostalCodes\Api\AreaCoverageMaintenance;
use AIArmada\Addressing\Support\ModelResolver;

final readonly class ClearAreaCoverageAction implements AreaCoverageMaintenance
{
    public function clearArea(string $areaId): void
    {
        ModelResolver::areaPostalCodeClass()::query()->where('address_area_id', $areaId)->delete();
    }

    public function clearPostalCode(string $postalCodeId): void
    {
        ModelResolver::areaPostalCodeClass()::query()->where('postal_code_id', $postalCodeId)->delete();
    }
}
