<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Contracts;

interface AddressAttachmentWriter
{
    /** @param array{type: string, label?: string|null, is_primary: bool} $data */
    public function execute(string $pivotId, array $data): string;

    public function detach(string $pivotId): void;

    public function detachAll(string $addressableType, string $addressableId): void;

    public function attach(string $addressId, string $addressableType, string $addressableId, string $type = 'primary', bool $isPrimary = false, ?string $label = null): string;

    public function setPrimary(string $addressId, string $addressableType, string $addressableId, string $type = 'primary'): string;
}
