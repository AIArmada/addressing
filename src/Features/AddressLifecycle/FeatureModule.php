<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle;

use AIArmada\Addressing\Contracts\AddressAssignmentWriter;
use AIArmada\Addressing\Contracts\AddressAttachmentWriter;
use AIArmada\Addressing\Contracts\AddressSnapshotWriter;
use AIArmada\Addressing\Contracts\AddressWriter;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\CheckAddressReferencesAction;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\ClearAddressGeographyReferencesAction;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\CreateAddressSnapshotAction;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\DeleteAddressReferencesAction;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\SaveAddressAction;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\SyncAddressAreaAssignmentsAction;
use AIArmada\Addressing\Features\AddressLifecycle\Actions\UpdateAddressAttachmentAction;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressDeletionCleanup;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceMaintenance;
use AIArmada\Addressing\Features\AddressLifecycle\Api\AddressReferenceReader;
use AIArmada\Addressing\Features\AddressLifecycle\Models\Address;
use AIArmada\Addressing\Features\AddressLifecycle\Models\Addressable;
use AIArmada\Addressing\Features\AddressLifecycle\Models\AddressAreaAssignment;
use AIArmada\Addressing\Platform\FeatureModule as FeatureModuleContract;
use Illuminate\Contracts\Foundation\Application;

final class FeatureModule implements FeatureModuleContract
{
    public function register(Application $app): void
    {
        $app->instance('addressing.model-default.address', Address::class);
        $app->instance('addressing.model-default.addressable', Addressable::class);
        $app->instance('addressing.model-default.area_assignment', AddressAreaAssignment::class);
        $app->singleton(AddressReferenceReader::class, CheckAddressReferencesAction::class);
        $app->singleton(AddressDeletionCleanup::class, DeleteAddressReferencesAction::class);
        $app->singleton(AddressReferenceMaintenance::class, ClearAddressGeographyReferencesAction::class);
        $app->singleton(AddressWriter::class, SaveAddressAction::class);
        $app->singleton(AddressAssignmentWriter::class, SyncAddressAreaAssignmentsAction::class);
        $app->singleton(AddressSnapshotWriter::class, CreateAddressSnapshotAction::class);
        $app->singleton(AddressAttachmentWriter::class, UpdateAddressAttachmentAction::class);
    }

    public function boot(Application $app): void {}
}
