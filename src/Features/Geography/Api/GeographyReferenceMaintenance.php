<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Api;

interface GeographyReferenceMaintenance
{
    public function clearState(string $stateId): void;

    public function clearCity(string $cityId): void;

    public function clearArea(string $areaId): void;

    public function clearCountry(string $countryId): void;
}
