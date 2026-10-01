<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressAreaRelationship;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\AddressAreaHierarchy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SaveAddressAreaAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes, ?AddressArea $record = null): AddressArea
    {
        $record ??= new AddressArea;

        $countryId = $this->resolveCountryId($attributes, $record);
        $country = AddressCountry::query()->findOrFail($countryId);
        $parent = $this->resolveParent($attributes, $record);

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
            'latitude' => $this->resolveNullableScalar($attributes, 'latitude', $record->latitude),
            'longitude' => $this->resolveNullableScalar($attributes, 'longitude', $record->longitude),
            'source' => $source,
            'source_id' => $sourceId,
            'parent_source_id' => $this->resolveParentSourceId($attributes, $record, $parent),
        ]);

        // Omitted on create, the column default (active) applies; omitted on
        // update, the stored value stands. An explicit boolean always wins.
        if (array_key_exists('is_active', $attributes)) {
            $record->is_active = (bool) $attributes['is_active'];
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

        $previousParentId = $record->exists ? $record->getOriginal('parent_id') : null;
        $previousParentId = $previousParentId !== null ? (string) $previousParentId : null;

        DB::transaction(function () use ($record, $attributes, $parent, $previousSource, $previousParentId): void {
            $record->save();

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
            $ownedLinks = static fn () => AddressAreaRelationship::query()
                ->where('child_address_area_id', $record->getKey())
                ->where('relationship_type', 'contains')
                ->whereIn('source', $sources);

            if ($hierarchyTypeProvided) {
                $hierarchyType = $this->resolveNullableString($attributes, 'hierarchy_type');

                $ownedLinks()->delete();

                if ($hierarchyType !== null && $parent instanceof AddressArea) {
                    AddressAreaRelationship::query()->create([
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

                return;
            }

            $ownedLinks()->update(['parent_address_area_id' => $parent->getKey()]);
        });

        return $record->fresh(['country', 'parent']) ?? $record;
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

        return mb_trim($countryId);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveParent(array $attributes, AddressArea $record): ?AddressArea
    {
        if (! array_key_exists('parent_id', $attributes)) {
            return $record->parent_id !== null
                ? AddressArea::query()->find($record->parent_id)
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

        return AddressArea::query()->findOrFail(mb_trim($parentId));
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
    private function resolveNullableScalar(array $attributes, string $key, mixed $current = null): mixed
    {
        if (! array_key_exists($key, $attributes)) {
            return $current;
        }

        return $attributes[$key];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resolveLevel(array $attributes, AddressArea $record, ?AddressArea $parent): ?int
    {
        if (array_key_exists('level', $attributes)) {
            return $attributes['level'] === null ? null : (int) $attributes['level'];
        }

        if ($record->level !== null) {
            return (int) $record->level;
        }

        if ($parent instanceof AddressArea && $parent->level !== null) {
            return (int) $parent->level + 1;
        }

        return 1;
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
