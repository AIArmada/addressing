<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\Addressable;
use AIArmada\Addressing\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class UpdateAddressAttachmentAction
{
    /** @param array{type: string, label?: string|null, is_primary: bool} $data */
    public function execute(Addressable $pivot, array $data): Addressable
    {
        return DB::transaction(function () use ($pivot, $data): Addressable {
            $candidate = ModelResolver::addressableClass()::query()->whereKey($pivot->getKey())->firstOrFail();
            $address = $candidate->address()->firstOrFail();
            $addressable = $candidate->addressable()->firstOrFail();

            AddressOwnerGuard::assertAddressIsWritable($address->getKey());
            AddressOwnerGuard::assertAddressableIsWritable($candidate->addressable_type, $candidate->addressable_id);
            $addressable->newQuery()->whereKey($addressable->getKey())->lockForUpdate()->firstOrFail();

            $current = ModelResolver::addressableClass()::query()->whereKey($pivot->getKey())->lockForUpdate()->firstOrFail();

            if ($current->address_id !== $candidate->address_id
                || $current->addressable_type !== $candidate->addressable_type
                || (string) $current->addressable_id !== (string) $candidate->addressable_id) {
                throw new RuntimeException('The address attachment changed while it was being updated.');
            }

            $type = mb_trim($data['type']);

            if ($type === '') {
                throw ValidationException::withMessages(['type' => 'An attachment type is required.']);
            }

            $collision = ModelResolver::addressableClass()::query()
                ->where('addressable_type', $current->addressable_type)
                ->where('addressable_id', $current->addressable_id)
                ->where('address_id', $current->address_id)
                ->where('type', $type)
                ->whereKeyNot($current->getKey())
                ->exists();

            if ($collision) {
                throw ValidationException::withMessages(['type' => 'This address is already attached with that type.']);
            }

            $pivotClass = ModelResolver::addressableClass();
            $query = $pivotClass::query()
                ->where('addressable_type', $current->addressable_type)
                ->where('addressable_id', $current->addressable_id)
                ->where('type', $type)
                ->where('is_primary', true);

            if ($data['is_primary']) {
                $query->whereKeyNot($current->getKey())->update(['is_primary' => false]);
            }

            $current->type = $type;
            if (array_key_exists('label', $data)) {
                $current->label = $data['label'];
            }
            $current->is_primary = $data['is_primary'];

            if (! $current->save()) {
                throw new RuntimeException('The address attachment could not be updated.');
            }

            return $current->fresh() ?? $current;
        });
    }

    public function detach(Addressable $pivot): void
    {
        DB::transaction(function () use ($pivot): void {
            $candidate = ModelResolver::addressableClass()::query()->whereKey($pivot->getKey())->firstOrFail();
            $address = $candidate->address()->firstOrFail();
            $addressable = $candidate->addressable()->firstOrFail();

            AddressOwnerGuard::assertAddressIsWritable($address->getKey());
            AddressOwnerGuard::assertAddressableIsWritable($candidate->addressable_type, $candidate->addressable_id);
            $addressable->newQuery()->whereKey($addressable->getKey())->lockForUpdate()->firstOrFail();

            $current = ModelResolver::addressableClass()::query()->whereKey($pivot->getKey())->lockForUpdate()->firstOrFail();

            if ($current->address_id !== $candidate->address_id
                || $current->addressable_type !== $candidate->addressable_type
                || (string) $current->addressable_id !== (string) $candidate->addressable_id) {
                throw new RuntimeException('The address attachment changed while it was being removed.');
            }

            if (! $current->delete()) {
                throw new RuntimeException('The address attachment could not be removed.');
            }
        });
    }
}
