<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Data\AddressAreaData;
use Illuminate\Support\LazyCollection;

final class SingleAddressAreaSource implements AddressAreaSource
{
    public function __construct(
        private readonly string $key,
        private readonly AddressAreaData $area,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    /**
     * @return LazyCollection<int, AddressAreaData>
     */
    public function areas(): LazyCollection
    {
        return LazyCollection::make([$this->area]);
    }
}
