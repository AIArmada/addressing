<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

use AIArmada\Addressing\Data\PostalCodeData;
use Illuminate\Support\LazyCollection;

interface PostalCodeSource
{
    public function key(): string;

    /** @return LazyCollection<int, PostalCodeData> */
    public function postalCodes(): LazyCollection;
}
