<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\ImportPostalCodesResultData;

interface PostalCodeImporter
{
    public function execute(PostalCodeSource $source): ImportPostalCodesResultData;
}
