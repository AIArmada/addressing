<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressData;

interface AddressFormatter
{
    public function format(AddressData $address): string;
}
