<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressData;

interface AddressValidator
{
    /** @return array<string, string> */
    public function validate(AddressData $address): array;
}
