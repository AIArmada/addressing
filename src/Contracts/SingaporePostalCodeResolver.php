<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\ResolveSingaporePostalCodesResultData;

interface SingaporePostalCodeResolver
{
    /** @param list<string> $postcodes */
    public function execute(array $postcodes): ResolveSingaporePostalCodesResultData;
}
