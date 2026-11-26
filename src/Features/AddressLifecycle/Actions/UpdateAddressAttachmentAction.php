<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\AddressLifecycle\Actions;

use AIArmada\Addressing\Contracts\AddressAttachmentWriter;
use AIArmada\Addressing\Features\AddressLifecycle\Models\Addressable;
use AIArmada\Addressing\Features\AddressLifecycle\Support\AddressOwnerGuard;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

final readonly class UpdateAddressAttachmentAction implements AddressAttachmentWriter
{
    /** @param array{type: string, label?: string|null, is_primary: bool} $data */
    public function execute(string $pivotId, array $data): string
    {
        return (new (ModelResolver::addressableClass()))->getConnection()->transaction(function () use ($pivotId, $data): string {
            $candidate = $this->writablePivots()->whereKey($pivotId)->firstOrFail();
            $this->lockAddressable($candidate->address_id, $candidate->addressable_type, $candidate->addressable_id);

            $current = $this->writablePivots()->whereKey($pivotId)->lockForUpdate()->firstOrFail();

            if ($current->address_id !== $candidate->address_id
                || $current->addressable_type !== $candidate->addressable_type
                || (string) $current->addressable_id !== (string) $candidate->addressable_id) {
                throw new RuntimeException('The address attachment changed while it was being updated.');
            }

            $type = mb_trim($data['type']);

            if ($type === '') {
                throw ValidationException::withMessages(['type' => 'An attachment type is required.']);
            }

            $collision = $this->writablePivots()
                ->where('addressable_type', $current->addressable_type)
                ->where('addressable_id', $current->addressable_id)
                ->where('address_id', $current->address_id)
                ->where('type', $type)
                ->whereKeyNot($current->getKey())
                ->exists();

            if ($collision) {
                throw ValidationException::withMessages(['type' => 'This address is already attached with that type.']);
            }

            $query = $this->writablePivots()
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

            return (string) $current->getKey();
        });
    }

    public function detach(string $pivotId): void
    {
        (new (ModelResolver::addressableClass()))->getConnection()->transaction(function () use ($pivotId): void {
            $candidate = $this->writablePivots()->whereKey($pivotId)->firstOrFail();
            $this->lockAddressable($candidate->address_id, $candidate->addressable_type, $candidate->addressable_id);

            $current = $this->writablePivots()->whereKey($pivotId)->lockForUpdate()->firstOrFail();

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

    public function detachAll(string $addressableType, string $addressableId): void
    {
        $pivotClass = ModelResolver::addressableClass();

        (new $pivotClass)->getConnection()->transaction(function () use ($addressableType, $addressableId): void {
            AddressOwnerGuard::lockAddressableForWrite($addressableType, $addressableId);

            $pivots = $this->writablePivots()
                ->where('addressable_type', $addressableType)
                ->where('addressable_id', $addressableId)
                ->lockForUpdate()
                ->get();

            foreach ($pivots as $pivot) {
                if (! $pivot->delete()) {
                    throw new RuntimeException('An address attachment could not be removed.');
                }
            }
        });
    }

    public function attach(string $addressId, string $addressableType, string $addressableId, string $type = 'primary', bool $isPrimary = false, ?string $label = null): string
    {
        $pivotClass = ModelResolver::addressableClass();

        return (new $pivotClass)->getConnection()->transaction(function () use ($pivotClass, $addressId, $addressableType, $addressableId, $type, $isPrimary, $label): string {
            $this->lockAddressable($addressId, $addressableType, $addressableId);
            $type = mb_trim($type);

            if ($type === '') {
                throw ValidationException::withMessages(['type' => 'An attachment type is required.']);
            }

            $existing = $this->writablePivots()
                ->where('address_id', $addressId)
                ->where('addressable_type', $addressableType)
                ->where('addressable_id', $addressableId)
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            if ($isPrimary) {
                $this->demotePrimary($addressableType, $addressableId, $type, $existing?->getKey());
            }

            if ($existing instanceof Addressable) {
                $updates = [];

                if ($isPrimary) {
                    $updates['is_primary'] = true;
                }

                if ($label !== null && $existing->label !== $label) {
                    $updates['label'] = $label;
                }

                if ($updates !== []) {
                    $existing->fill($updates);

                    if (! $existing->save()) {
                        throw new RuntimeException('The address attachment could not be updated.');
                    }
                }

                return (string) $existing->getKey();
            }

            $pivot = new $pivotClass;
            $pivot->fill([
                'id' => (string) Str::orderedUuid(),
                'address_id' => $addressId,
                'addressable_type' => $addressableType,
                'addressable_id' => $addressableId,
                'type' => $type,
                'is_primary' => $isPrimary,
                'label' => $label,
            ]);

            if (! $pivot->save()) {
                throw new RuntimeException('The address attachment could not be created.');
            }

            return (string) $pivot->getKey();
        });
    }

    public function setPrimary(string $addressId, string $addressableType, string $addressableId, string $type = 'primary'): string
    {
        $pivotClass = ModelResolver::addressableClass();

        return (new $pivotClass)->getConnection()->transaction(function () use ($addressId, $addressableType, $addressableId, $type): string {
            $this->lockAddressable($addressId, $addressableType, $addressableId);
            $type = mb_trim($type);

            if ($type === '') {
                throw ValidationException::withMessages(['type' => 'An attachment type is required.']);
            }

            $pivot = $this->writablePivots()
                ->where('address_id', $addressId)
                ->where('addressable_type', $addressableType)
                ->where('addressable_id', $addressableId)
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            if (! $pivot instanceof Addressable) {
                throw new InvalidArgumentException('The address must be attached with the requested type before it can be primary.');
            }

            $this->demotePrimary($addressableType, $addressableId, $type, (string) $pivot->getKey());
            $pivot->is_primary = true;

            if (! $pivot->save()) {
                throw new RuntimeException('The primary address attachment could not be updated.');
            }

            return (string) $pivot->getKey();
        });
    }

    private function lockAddressable(string $addressId, string $addressableType, string $addressableId): void
    {
        AddressOwnerGuard::lockAddressableForWrite($addressableType, $addressableId);
        AddressOwnerGuard::lockAddressForWrite($addressId);
    }

    /** @return Builder<Addressable> */
    private function writablePivots(): Builder
    {
        return ModelResolver::addressableClass()::query()->forOwner()
            ->whereHas('address', static fn ($query) => $query->forOwner());
    }

    private function demotePrimary(string $addressableType, string $addressableId, string $type, ?string $exceptId = null): void
    {
        $this->writablePivots()
            ->where('addressable_type', $addressableType)
            ->where('addressable_id', $addressableId)
            ->where('type', $type)
            ->where('is_primary', true)
            ->when($exceptId !== null, static fn ($query) => $query->whereKeyNot($exceptId))
            ->update(['is_primary' => false]);
    }
}
