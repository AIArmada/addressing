<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Contracts\AddressAssignmentWriter;
use AIArmada\Addressing\Contracts\AddressWriter;
use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use LogicException;

final readonly class SaveAddressAction implements AddressWriter
{
    public function __construct(private AddressAssignmentWriter $assignments) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string|null>|null  $areaAssignments
     * @param  class-string<Address>|null  $modelClass
     * @param  array<string, mixed>  $assignmentMetadata
     */
    public function execute(
        array $data,
        ?array $areaAssignments,
        ?string $recordId = null,
        ?string $modelClass = null,
        array $assignmentMetadata = [],
    ): string {
        $modelClass ??= ModelResolver::addressClass();
        if (! is_a($modelClass, Address::class, true)) {
            throw new LogicException('Configured address model must extend Address.');
        }

        $address = new $modelClass;

        return $address->getConnection()->transaction(function () use ($address, $recordId, $data, $areaAssignments, $assignmentMetadata): string {
            if ($recordId !== null) {
                $address = AddressOwnerGuard::lockAddressForWrite($recordId, $address::class);
            }

            $address->fill($data);

            if (! $address->save()) {
                throw new LogicException('The address could not be saved.');
            }

            if ($areaAssignments !== null && ($recordId !== null || $areaAssignments !== [])) {
                $this->assignments->execute(
                    (string) $address->getKey(),
                    $areaAssignments,
                    $address->state_id,
                    $assignmentMetadata,
                    $address::class,
                );
            }

            return (string) $address->getKey();
        });
    }
}
