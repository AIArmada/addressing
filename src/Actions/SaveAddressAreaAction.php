<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Actions;

use AIArmada\Addressing\Models\AddressArea;
use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Support\AddressAreaHierarchy;
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
            'slug' => Str::slug($name),
            'latitude' => $this->resolveNullableScalar($attributes, 'latitude', $record->latitude),
            'longitude' => $this->resolveNullableScalar($attributes, 'longitude', $record->longitude),
            'source' => $source,
            'source_id' => $sourceId,
            'parent_source_id' => $this->resolveParentSourceId($attributes, $record, $parent),
        ]);

        $record->save();

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

        return $parent instanceof AddressArea ? $parent->source_id : $record->parent_source_id;
    }

    private function generatedSourceId(AddressCountry $country, string $type, string $name): string
    {
        return Str::lower((string) $country->iso2)
            . '-' . Str::slug($type)
            . '-' . Str::slug($name)
            . '-' . Str::lower(Str::random(8));
    }
}
