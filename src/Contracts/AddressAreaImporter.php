<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\ImportAddressAreasResultData;

interface AddressAreaImporter
{
    public function execute(AddressAreaSource $source, bool $dryRun = false, ?string $providerKey = null, bool $reactivate = false, ?callable $progress = null): ImportAddressAreasResultData;
}
