<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\CountryStateSeedData;

interface CountryStateSeeder
{
    public function execute(string $countryId, CountryStateSeedData $data): void;
}
