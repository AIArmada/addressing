<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface PostalCodeAdminImporter
{
    /**
     * @param  array<string, mixed>|null  $metadata
     * @param  list<array<string, mixed>>  $coverage
     */
    public function execute(string $countryCode, string $code, bool $isActive, ?array $metadata, array $coverage): string;
}
