<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Data;

final readonly class CountryStateSeedData
{
    /**
     * @param  list<array{code: string, name: string}>  $states
     * @param  list<string>  $retiredCodes
     */
    public function __construct(public array $states, public array $retiredCodes = []) {}
}
