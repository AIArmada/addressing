<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface PostalCodeWriter
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  class-string|null  $modelClass
     */
    public function execute(array $attributes, ?string $recordId = null, ?string $modelClass = null): string;
}
