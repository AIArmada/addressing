<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface GeographyReferenceEditor
{
    /** @param array<string, mixed> $attributes */
    public function country(string $id, array $attributes): void;

    /** @param array<string, mixed> $attributes */
    public function state(string $id, array $attributes): void;

    /** @param array<string, mixed> $attributes */
    public function city(string $id, array $attributes): void;
}
