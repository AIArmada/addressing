<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\PostalCodeSource;
use AIArmada\Addressing\Data\PostalCodeData;
use Generator;
use Illuminate\Support\LazyCollection;

final class ArrayPostalCodeSource implements PostalCodeSource
{
    /** @param list<PostalCodeData> $items */
    public function __construct(
        private readonly string $sourceKey,
        private readonly array $items,
    ) {}

    public function key(): string
    {
        return $this->sourceKey;
    }

    /** @return LazyCollection<int, PostalCodeData> */
    public function postalCodes(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            yield from $this->items;
        });
    }
}
