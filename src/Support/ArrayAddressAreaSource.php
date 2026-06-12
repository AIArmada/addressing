<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use AIArmada\Addressing\Data\AddressAreaData;
use Generator;
use Illuminate\Support\LazyCollection;

class ArrayAddressAreaSource implements AddressAreaSource
{
    /** @var array<int, AddressAreaData> */
    private readonly array $items;

    private readonly string $sourceKey;

    public function __construct(string $sourceKey, array $items)
    {
        $this->sourceKey = $sourceKey;
        $this->items = array_values($items);
    }

    public function key(): string
    {
        return $this->sourceKey;
    }

    public function areas(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            foreach ($this->items as $item) {
                yield $item;
            }
        });
    }
}
