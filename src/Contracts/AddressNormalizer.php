<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressData;

interface AddressNormalizer
{
    public function normalize(array $data): AddressData;
}
