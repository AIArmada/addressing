<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Api;

interface AddressReferenceReader
{
    public function hasAreaReferences(string $areaId): bool;

    public function hasCountryReferences(string $countryId): bool;
}
