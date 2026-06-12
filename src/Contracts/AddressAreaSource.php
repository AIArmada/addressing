<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\AddressAreaData;
use Illuminate\Support\LazyCollection;

interface AddressAreaSource
{
    public function key(): string;

    /**
     * @return LazyCollection<int, AddressAreaData>
     */
    public function areas(): LazyCollection;
}
