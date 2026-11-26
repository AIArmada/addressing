<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Features\Geography\Actions;

use AIArmada\Addressing\Contracts\AddressAreaWriter;
use AIArmada\Addressing\Features\Geography\Models\AddressArea;
use AIArmada\Addressing\Features\Geography\Models\AddressCountry;
use AIArmada\Addressing\Features\Geography\Support\AddressAreaHierarchy;
use AIArmada\Addressing\Features\Geography\Support\AddressAreaStateBridge;
use AIArmada\Addressing\Support\ModelResolver;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;

final readonly class SaveAddressAreaAction implements AddressAreaWriter
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  class-string<AddressArea>|null  $modelClass
     */
    public function handle(array $attributes, ?string $recordId = null, ?string $modelClass = null): string
    {
        $modelClass ??= ModelResolver::areaClass();

        if (! is_a($modelClass, AddressArea::class, true)) {
            throw new LogicException('Configured area model must extend AddressArea.');
        }

        return (new $modelClass)->getConnection()->transaction(function () use ($modelClass, $recordId, $attributes): string {
            $record = $recordId === null ? new $modelClass : $modelClass::query()->lockForUpdate()->findOrFail($recordId);

            $countryId = $this->resolveCountryId($attributes, $record);
            $country = ModelResolver::countryClass()::query()->lockForUpdate()->findOrFail($countryId);
            $parent = $this->resolveParent($attributes, $record, $modelClass);

            if ($parent instanceof AddressArea && (string) $parent->country_id !== $countryId) {
                throw ValidationException::withMessages([
                    'parent_id' => __('The selected parent area must belong to the same country.'),
                ]);
            }

            if ($parent instanceof AddressArea) {
                $parentValidationMessage = AddressAreaHierarchy::validateParentAssignment($record->exists ? $record : null, $parent);

                if ($parentValidationMessage !== null) {
                    throw ValidationException::withMessages([
                        'parent_id' => $parentValidationMessage,
                    ]);
                }
            }

            $name = $this->resolveString($attributes, 'name', $record->name, trim: true);
            $type = $this->resolveString($attributes, 'type', $record->type, trim: true);
            $previousSource = $record->source;
            $source = $this->resolveString($attributes, 'source', $record->source, fallback: 'manual', trim: true);
            $sourceId = $this->resolveString(
                $attributes,
                'source_id',
                $record->source_id,
                fallback: $this->generatedSourceId($country, $type, $name),
                trim: true,
            );

            $record->fill([
                'country_id' => $countryId,
                'parent_id' => $parent?->getKey(),
                'country_code' => Str::upper((string) $country->iso2),
                'type' => $type,
                'level' => $this->resolveLevel($attributes, $record, $parent),
                'name' => $name,
                'native_name' => $this->resolveNullableString($attributes, 'native_name', $record->native_name),
                'code' => $this->resolveNullableString($attributes, 'code', $record->code),
                'slug' => Str::limit(Str::slug($name), 255, ''),
                'latitude' => $this->resolveCoordinate($attributes, 'latitude', $record->latitude, 90),
                'longitude' => $this->resolveCoordinate($attributes, 'longitude', $record->longitude, 180),
                'source' => $source,
                'source_id' => $sourceId,
                'parent_source_id' => $this->resolveParentSourceId($attributes, $record, $parent),
            ]);

            // Omitted on create, the column default (active) applies; omitted on
            // update, the stored value stands. An explicit boolean always wins.
            if (array_key_exists('is_active', $attributes)) {
                if (! is_bool($attributes['is_active'])) {
                    throw ValidationException::withMessages(['is_active' => 'The active status must be a boolean.']);
                }

                $record->is_active = $attributes['is_active'];
            }

            if ($parent instanceof AddressArea) {
                $childLevel = $record->level !== null ? (int) $record->level : null;
                $parentLevelMessage = AddressAreaHierarchy::validateParentCompatibility($parent, $childLevel);

                if ($parentLevelMessage !== null) {
                    throw ValidationException::withMessages([
                        'parent_id' => $parentLevelMessage,
                    ]);
                }
            }

            if ($record->exists && $record->isDirty('level') && $record->level !== null
                && $record->children()->whereNotNull('level')->where('level', '<=', $record->level)->exists()) {
                throw ValidationException::withMessages([
                    'level' => __('The area level must be lower than every direct child level.'),
                ]);
            }

            $previousParentId = $record->exists ? $record->getOriginal('parent_id') : null;
            $previousParentId = $previousParentId !== null ? (string) $previousParentId : null;

            $record->getConnection()->transaction(function () use ($record, $attributes, $parent, $previousSource, $previousParentId): void {
                if (! $record->save()) {
                    throw new LogicException('The address area could not be saved.');
                }

                $hierarchyTypeProvided = array_key_exists('hierarchy_type', $attributes);
                $currentParentId = $record->parent_id !== null ? (string) $record->parent_id : null;

                if (! $hierarchyTypeProvided && $previousParentId === $currentParentId) {
                    return;
                }

                $sources = array_values(array_filter(
                    [$previousSource, $record->source],
                    static fn (?string $source): bool => $source !== null && $source !== '',
                ));

                // Only source-owned containment edges move with the save; other
                // sources and relationship types are left untouched.
                $ownedLinks = static fn () => ModelResolver::areaRelationshipClass()::query()
                    ->where('child_address_area_id', $record->getKey())
                    ->where('relationship_type', 'contains')
                    ->whereIn('source', $sources);

                if ($hierarchyTypeProvided) {
                    $hierarchyType = $this->resolveNullableString($attributes, 'hierarchy_type');

                    $ownedLinks()->delete();
                    AddressAreaStateBridge::forgetAll();

                    if ($hierarchyType !== null && $parent instanceof AddressArea) {
                        ModelResolver::areaRelationshipClass()::query()->create([
                            'parent_address_area_id' => $parent->getKey(),
                            'child_address_area_id' => $record->getKey(),
                            'relationship_type' => 'contains',
                            'hierarchy_type' => $hierarchyType,
                            'source' => $record->source,
                        ]);
                    }

                    return;
                }

                // The parent moved without a new hierarchy type: carry the
                // existing containment edges to the new parent (keeping their
                // hierarchy types) or drop them on detach.
                if (! $parent instanceof AddressArea) {
                    $ownedLinks()->delete();
                    AddressAreaStateBridge::forgetAll();

                    return;
                }

                $ownedLinks()->update(['parent_address_area_id' => $parent->getKey()]);
                AddressAreaStateBridge::forgetAll();
            });

            return (string) $record->getKey();
        }, attempts: 3);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveCountryId(array $attributes, AddressArea $record): string
    {
        $countryId = $attributes['country_id'] ?? $record->country_id;

        if (! is_string($countryId) || mb_trim($countryId) === '') {
            throw ValidationException::withMessages([
                'country_id' => __('The country is required.'),
            ]);
        }

        $countryId = mb_trim($countryId);

        if ($record->exists && (string) $record->country_id !== $countryId) {
            throw ValidationException::withMessages([
                'country_id' => __('The country of an existing address area cannot be changed.'),
            ]);
        }

        return $countryId;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  class-string<AddressArea>  $modelClass
     */
    private function resolveParent(array $attributes, AddressArea $record, string $modelClass): ?AddressArea
    {
        if (! array_key_exists('parent_id', $attributes)) {
            return $record->parent_id !== null
                ? $modelClass::query()->lockForUpdate()->findOrFail($record->parent_id)
                : null;
        }

        $parentId = $attributes['parent_id'];

        if ($parentId === null || $parentId === '') {
            return null;
        }

        if (! is_string($parentId) || mb_trim($parentId) === '') {
            throw ValidationException::withMessages([
                'parent_id' => __('The selected parent area is invalid.'),
            ]);
        }

        return $modelClass::query()->lockForUpdate()->findOrFail(mb_trim($parentId));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveString(
        array $attributes,
        string $key,
        ?string $current = null,
        ?string $fallback = null,
        bool $trim = false,
    ): string {
        $value = $attributes[$key] ?? $current ?? $fallback;

        if (! is_string($value)) {
            throw ValidationException::withMessages([
                $key => [__('The :attribute field must be a string.', ['attribute' => str_replace('_', ' ', $key)])],
            ]);
        }

        $value = $trim ? mb_trim($value) : $value;

        if ($value === '') {
            throw ValidationException::withMessages([
                $key => [__('The :attribute field is required.', ['attribute' => str_replace('_', ' ', $key)])],
            ]);
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveNullableString(array $attributes, string $key, ?string $current = null): ?string
    {
        if (! array_key_exists($key, $attributes)) {
            return $current;
        }

        $value = $attributes[$key];

        if ($value === null) {
            return null;
        }

        if (! is_string($value)) {
            throw ValidationException::withMessages([
                $key => [__('The :attribute field must be a string.', ['attribute' => str_replace('_', ' ', $key)])],
            ]);
        }

        $value = mb_trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveCoordinate(array $attributes, string $key, mixed $current, int $limit): ?float
    {
        $value = array_key_exists($key, $attributes) ? $attributes[$key] : $current;

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || ! is_finite((float) $value) || abs((float) $value) > $limit) {
            throw ValidationException::withMessages([$key => 'The coordinate is outside its valid range.']);
        }

        return (float) $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveLevel(array $attributes, AddressArea $record, ?AddressArea $parent): ?int
    {
        $value = array_key_exists('level', $attributes)
            ? $attributes['level']
            : ($record->level ?? ($parent?->level !== null ? $parent->level + 1 : 1));

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            throw ValidationException::withMessages([
                'level' => __('The area level must be a positive integer no greater than 32767.'),
            ]);
        }

        $numeric = (float) $value;

        if (! is_finite($numeric) || $numeric < 1 || $numeric > 32767 || floor($numeric) !== $numeric) {
            throw ValidationException::withMessages([
                'level' => __('The area level must be a positive integer no greater than 32767.'),
            ]);
        }

        return (int) $numeric;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveParentSourceId(array $attributes, AddressArea $record, ?AddressArea $parent): ?string
    {
        if (array_key_exists('parent_source_id', $attributes)) {
            return $this->resolveNullableString($attributes, 'parent_source_id', $record->parent_source_id);
        }

        if ($parent instanceof AddressArea) {
            return $parent->source_id;
        }

        // An explicit detach clears the stale pointer; an untouched parent
        // keeps the stored value.
        if (array_key_exists('parent_id', $attributes)) {
            return null;
        }

        return $record->parent_source_id;
    }

    private function generatedSourceId(AddressCountry $country, string $type, string $name): string
    {
        return Str::lower((string) $country->iso2)
            . '-' . Str::slug($type)
            . '-' . Str::slug($name)
            . '-' . Str::lower(Str::random(8));
    }
}
