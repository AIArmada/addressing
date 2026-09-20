<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Contracts\AddressAreaSource;
use Generator;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;

class CompositeAddressAreaSource implements AddressAreaSource
{
    /** @var array<int, AddressAreaSource> */
    private readonly array $sources;

    /**
     * @param  array<int, AddressAreaSource>  $sources
     */
    public function __construct(array $sources)
    {
        if ($sources === []) {
            throw new InvalidArgumentException('Composite address-area sources need at least one source.');
        }

        $this->sources = array_values($sources);
    }

    public function key(): string
    {
        return $this->sources[0]->key();
    }

    public function areas(): LazyCollection
    {
        return LazyCollection::make(function (): Generator {
            foreach ($this->sources as $source) {
                yield from $source->areas();
            }
        })->values();
    }
}
