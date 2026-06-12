<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Contracts\AddressNormalizer;
use AIArmada\Addressing\Data\AddressData;

class NormalizeAddressDataAction implements AddressNormalizer
{
    public function normalize(array $data): AddressData
    {
        return AddressData::from($data);
    }
}
